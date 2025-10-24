<?php
// functions.php
// ================================
// Database Connection
// ================================
require_once __DIR__ . '/db.php'; // Make sure $pdo is defined here

// ================================
// SESSION HANDLING
// ================================
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ================================
// 1. AUTHENTICATION FUNCTIONS
// ================================

// Register user
function register_user($fullname, $email, $password) {
    global $pdo;

    if (empty($fullname)) return ['success'=>false,'message'=>'Full name required.'];
    if (empty($email)) return ['success'=>false,'message'=>'Email required.'];
    if (empty($password)) return ['success'=>false,'message'=>'Password required.'];

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email=?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            return ['success'=>false,'message'=>'Email already registered.'];
        }

        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (fullname,email,password) VALUES (?,?,?)");
        $stmt->execute([$fullname,$email,$hashed_password]);

        return ['success'=>true,'message'=>'Account created successfully.'];

    } catch (PDOException $e) {
        error_log("Register error: ".$e->getMessage());
        return ['success'=>false,'message'=>'Database error occurred.'];
    }
}

// Login user
function login_user($email, $password) {
    global $pdo;

    if (empty($email) || empty($password)) {
        return ['success'=>false,'message'=>'Email and password required.'];
    }

    try {
        $stmt = $pdo->prepare("SELECT id, fullname, password FROM users WHERE email=?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success'=>false,'message'=>'Invalid email or password.'];
        }

        // Successful login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['fullname'];
        $_SESSION['user_email'] = $email;
        $_SESSION['loggedin'] = true;

        return ['success'=>true,'message'=>'Login successful.','redirect'=>'dashboard.php'];

    } catch (PDOException $e) {
        error_log("Login error: ".$e->getMessage());
        return ['success'=>false,'message'=>'Database error occurred.'];
    }
}

// Logout user
function logout_user() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && !empty($_SESSION['user_id']);
}

// Get current user ID
function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// ================================
// 2. USER FUNCTIONS
// ================================
function get_user($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ================================
// 3. ACCOUNT FUNCTIONS
// ================================
function get_accounts($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM accounts WHERE user_id=?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_account_balance($account_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(CASE WHEN type='income' THEN amount ELSE -amount END),0) AS balance
        FROM transactions
        WHERE account_id=?
    ");
    $stmt->execute([$account_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['balance'] ?? 0;
}

// ================================
// 4. CATEGORY FUNCTIONS
// ================================
function get_categories($user_id, $type=null) {
    global $pdo;
    $sql = "SELECT * FROM categories WHERE user_id=?";
    $params = [$user_id];
    if ($type) {
        $sql .= " AND type=?";
        $params[] = $type;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ================================
// 5. TRANSACTION FUNCTIONS
// ================================
function get_transactions($user_id, $limit=10) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT t.*, c.name AS category_name, a.account_name
        FROM transactions t
        LEFT JOIN categories c ON t.category_id=c.id
        LEFT JOIN accounts a ON t.account_id=a.id
        WHERE t.user_id=?
        ORDER BY t.transaction_date DESC
        LIMIT ?
    ");
    $stmt->execute([$user_id,$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_monthly_stats($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN type='income' THEN amount ELSE 0 END),0) AS total_income,
            COALESCE(SUM(CASE WHEN type='expense' THEN amount ELSE 0 END),0) AS total_expenses,
            COALESCE(SUM(CASE WHEN type='income' THEN amount ELSE -amount END),0) AS total_balance
        FROM transactions
        WHERE user_id=? AND MONTH(transaction_date)=MONTH(CURRENT_DATE())
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ================================
// 6. BUDGET FUNCTIONS
// ================================
function get_budgets($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM budgets WHERE user_id=?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ================================
// 7. FINANCIAL GOALS FUNCTIONS
// ================================
function get_financial_goals($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM financial_goals WHERE user_id=?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

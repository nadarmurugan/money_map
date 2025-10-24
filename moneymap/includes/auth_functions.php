<?php
// includes/auth_functions.php

// --- CRITICAL: Start the session if it hasn't been already ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// NOTE: The path 'db.php' is relative to this file's location inside the 'includes' directory.
require_once 'db.php'; // Access the $pdo connection


// =======================================================
// === AUTHENTICATION FUNCTIONS (EXISTING) ================
// =======================================================

/**
 * Registers a new user into the database.
 * ... (function body omitted for brevity, assuming existing logic is correct)
 */
function register_user($fullname, $email, $password) {
    global $pdo; // Use the connection from db.php

    // 1. Basic input validation check
    if (empty($fullname)) {
        return ['success' => false, 'message' => 'Full name is required.', 'field' => 'fullname'];
    }
    if (empty($email)) {
        return ['success' => false, 'message' => 'Email is required.', 'field' => 'email'];
    }
    if (empty($password)) {
        return ['success' => false, 'message' => 'Password is required.', 'field' => 'password'];
    }

    // 2. Hash the password securely
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    if ($hashed_password === false) {
        return ['success' => false, 'message' => 'A severe internal error occurred during password processing.'];
    }

    // 3. Check if email already exists
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'This email is already registered.', 'field' => 'email'];
        }
    } catch (PDOException $e) {
        error_log("Email existence check error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error during email check.'];
    }

    // 4. Insert the new user
    try {
        $sql = "INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$fullname, $email, $hashed_password]);

        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'Account created successfully.'];
        } else {
            return ['success' => false, 'message' => 'Failed to create account.'];
        }
    } catch (PDOException $e) {
        error_log("User insertion error: " . $e->getMessage());

        if ($e->getCode() === '23000') { 
             return ['success' => false, 'message' => 'A user with this email already exists.', 'field' => 'email'];
        }
        return ['success' => false, 'message' => 'A database error occurred during registration.'];
    }
}


/**
 * Authenticates a user for login.
 * ... (function body omitted for brevity, assuming existing logic is correct)
 */
function login_user($email, $password) {
    global $pdo;

    // 1. Input validation (optional, but good practice)
    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Email and password are required.'];
    }

    // 2. Fetch user from database by email
    try {
        $stmt = $pdo->prepare("SELECT id, fullname, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Use a generic message to prevent exposing whether the email exists
            return ['success' => false, 'message' => 'Invalid email or password.', 'field' => 'email'];
        }
    } catch (PDOException $e) {
        error_log("Login database fetch error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error during login.'];
    }

    // 3. Verify the password
    if (password_verify($password, $user['password'])) {
        
        // --- Successful Login ---
        // 4. Start session and set user data (CRUCIAL for maintaining login state)
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $email; 
        $_SESSION['user_name'] = $user['fullname']; // Storing fullname for dashboard personalization
        $_SESSION['loggedin'] = true;

        // 5. Return success response
        return [
            'success' => true,
            'message' => 'Login successful! Redirecting to dashboard.',
            'redirect' => 'dashboard.php' // Used by the front-end JS for the modal redirect
        ];
    } else {
        // Password verification failed
        return ['success' => false, 'message' => 'Invalid email or password.', 'field' => 'password'];
    }
}

/**
 * Checks if a user is currently logged in based on session status.
 * ... (function body omitted for brevity, assuming existing logic is correct)
 */
function is_logged_in() {
    // Check for the critical session variables set during login
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}


/**
 * Retrieves the ID of the currently logged-in user.
 * ... (function body omitted for brevity, assuming existing logic is correct)
 */
function get_user_id() {
    if (is_logged_in()) {
        return (int)$_SESSION['user_id'];
    }
    return null;
}


/**
 * Logs out the current user by destroying the session and clearing cookies.
 * ... (function body omitted for brevity, assuming existing logic is correct)
 */
function logout_user() {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();
}


// =======================================================
// === DATA FETCHING FUNCTIONS (NEWLY ADDED) ==============
// =======================================================

/**
 * Fetches the current user's financial dashboard data from the database.
 * NOTE: Assumes existence of tables: users, user_financial_accounts, user_savings_goals.
 * * @param PDO $pdo The database connection object.
 * @param int $user_id The ID of the logged-in user (from the users table).
 * @return array|false An associative array of user metrics or false on failure.
 */
function fetch_user_dashboard_data(PDO $pdo, $user_id) {
    global $pdo;

    // SQL: Select metrics by joining user, accounts, and goals tables
    $sql_metrics = "SELECT 
                        u.id AS user_id, u.fullname AS name, u.email,
                        a.total_balance, a.monthly_income, a.monthly_expenses,
                        g.savings_progress
                    FROM 
                        users u
                    LEFT JOIN 
                        user_financial_accounts a ON u.id = a.user_id
                    LEFT JOIN
                        user_savings_goals g ON u.id = g.user_id
                    WHERE 
                        u.id = :user_id";

    try {
        $stmt = $pdo->prepare($sql_metrics);
        // Using PDO::PARAM_INT is the safest way to bind ID
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT); 
        $stmt->execute();
        
        return $stmt->fetch(); // Returns false if no user is found
        
    } catch (PDOException $e) {
        error_log("Database Error in fetch_user_dashboard_data for UserID $user_id: " . $e->getMessage());
        return false;
    }
}

/**
 * Fetches the most recent transactions for the current user.
 * * @param PDO $pdo The database connection object.
 * @param int $user_id The ID of the logged-in user.
 * @param int $limit The maximum number of transactions to return.
 * @return array An array of transaction records.
 */
function fetch_recent_transactions(PDO $pdo, $user_id, $limit = 5) {
    global $pdo;

    // SQL: Select transactions ordered by date descending
    $sql_transactions = "SELECT 
                            description, amount, transaction_date
                         FROM 
                            transactions
                         WHERE 
                            user_id = :user_id
                         ORDER BY 
                            transaction_date DESC, id DESC
                         LIMIT :limit"; // Assuming 'id' is the primary key for secondary ordering

    try {
        $stmt = $pdo->prepare($sql_transactions);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        // Bind limit as an integer
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT); 
        $stmt->execute();
        
        return $stmt->fetchAll(); // Returns an empty array if no transactions are found
        
    } catch (PDOException $e) {
        error_log("Database Error in fetch_recent_transactions for UserID $user_id: " . $e->getMessage());
        return [];
    }
}

// Ensure the functions are now present in your includes/auth_functions.php
?>
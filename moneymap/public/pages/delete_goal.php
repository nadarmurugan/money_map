<?php
// ===================================
// delete_goal.php (Goal Deletion Script)
// ===================================

// --- SESSION CHECK ---
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$goal_id = $_GET['id'] ?? null;

// --- DATABASE CONFIG & CONNECTION ---
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'money_map');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function db_connect() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=".DB_CHARSET;
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
        try { $pdo = new PDO($dsn, DB_USER, DB_PASS, $options); }
        catch(PDOException $e){ die("We're currently experiencing technical difficulties. 😟"); }
    }
    return $pdo;
}
$pdo = db_connect();

// --- DELETION LOGIC ---
if (!$goal_id || !is_numeric($goal_id)) {
    $_SESSION['message'] = "Invalid goal ID for deletion.";
    $_SESSION['message_type'] = 'error';
    header('Location: manage_goals.php');
    exit();
}

try {
    // First, fetch the name for the confirmation message
    $stmt = $pdo->prepare("SELECT goal_name FROM goals WHERE id=? AND user_id=?");
    $stmt->execute([$goal_id, $user_id]);
    $goal_name = $stmt->fetchColumn();

    if ($goal_name) {
        // Proceed with deletion
        $sql = "DELETE FROM goals WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$goal_id, $user_id]);

        $_SESSION['message'] = "Goal '{$goal_name}' has been successfully deleted.";
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = "Goal not found or access denied.";
        $_SESSION['message_type'] = 'error';
    }

} catch (PDOException $e) {
    $_SESSION['message'] = "Error deleting goal: " . $e->getMessage();
    $_SESSION['message_type'] = 'error';
    error_log("Goal deletion error: " . $e->getMessage());
}

// Redirect back to the goal management page
header('Location: manage_goals.php');
exit();
?>

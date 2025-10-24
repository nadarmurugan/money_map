<?php
// ===================================
// do_logout.php (Handles actual session termination)
// ===================================

// 1. Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in before proceeding
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// 2. Include authentication functions (if needed)
// Adjust the path according to your folder structure
require_once __DIR__ . '/../includes/auth_functions.php'; 

// 3. Perform logout via auth function (if you have one)
if (function_exists('logout_user')) {
    logout_user(); // Clears $_SESSION and destroys the session
}

// 4. Clear $_SESSION variables
$_SESSION = array();

// 5. Extra: Clear session cookie for security
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 6. Destroy the session completely
session_destroy();

// 7. Redirect to index.php (landing page)
header("Location: index.php");
exit;
?>
<?php
// admin_logout.php

session_start();

// Check if the admin is actually logged in before logging them out (optional but good practice)
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session cookie to ensure a clean slate
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Finally, destroy the session
    session_destroy();
}

// Always redirect to the admin login page
header("Location: admin_login.php");
exit();
?>
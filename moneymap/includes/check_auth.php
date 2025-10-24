<?php
// includes/check_auth.php

// 1. Ensure the session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Include authentication functions (which contains is_logged_in())
// The path logic ensures it works correctly when called from a file in the root directory.
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/auth_functions.php';
}

// 3. Check if the user is logged in
if (!is_logged_in()) {
    // User is NOT logged in. Redirect them to the login page.
    // Use absolute path for redirect to avoid issues
    $login_url = '/login.php'; // Adjust this path based on your file structure
    header("Location: $login_url"); 
    exit;
}

// If the script reaches this point, the user is authenticated.
// Fetch and store user data for easy access on the protected page
$current_user_id = get_user_id();
$current_user_name = $_SESSION['user_name'] ?? 'User';
$current_user_email = $_SESSION['user_email'] ?? 'No Email';

// Ensure we have a valid user ID
if (empty($current_user_id)) {
    // If for some reason we don't have a user ID, log out the user
    header("Location: /logout.php");
    exit;
}
?>
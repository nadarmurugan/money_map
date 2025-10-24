<?php
// includes/db.php

// Set headers for JSON response in case of a connection failure
header('Content-Type: application/json');

// --- Database Configuration ---
// DB_HOST is set to 127.0.0.1 to force TCP connection, essential when Apache uses a non-standard port (like 8080).
// The database service (MySQL/MariaDB) usually runs on its default port 3306, even if Apache is on 8080.
define('DB_HOST', '127.0.0.1'); 
define('DB_USER', 'root');   
define('DB_PASS', '');       
define('DB_NAME', 'money_map'); 
define('DB_PORT', '3306'); // Standard MySQL Port - **Do not change this unless your MySQL/MariaDB logs show a different port.**
// ------------------------------

try {
    // We explicitly include the port number in the DSN string
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // --- Error Handling Block ---
    error_log("Database Connection Error: " . $e->getMessage());

    http_response_code(500);
    
    // Provide a detailed error message to help the developer (not the user)
    die(json_encode([
        "success" => false, 
        "message" => "Database connection failed. Check MySQL/MariaDB server status and the port (" . DB_PORT . ")."
    ]));
}

// If successful, the $pdo variable is the usable database connection object.
?>
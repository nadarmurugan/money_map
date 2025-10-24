<?php
// api/auth.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST');

// Note: This path assumes api is a sibling folder to includes
require_once '../includes/auth_functions.php';

$response = [
    'success' => false,
    'message' => 'An unexpected server error occurred.' 
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        http_response_code(400); 
        $response['message'] = 'Invalid JSON data received.';
        echo json_encode($response);
        exit;
    }
    
    // --- Determine Action (Login vs. Signup) ---
    // Infer action based on presence of 'fullname' (a field unique to signup).
    $action = isset($data['fullname']) ? 'signup' : 'login';

    if ($action === 'login') {
        // --- LOGIN Logic ---
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        
        // 1. Basic Validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Please enter a valid email address.';
            $response['field'] = 'email';
        } else if (empty($password)) {
            $response['message'] = 'Please enter your password.';
            $response['field'] = 'password';
        } else {
            // 2. Call Login Database Function (Assumes login_user exists in auth_functions.php)
            $result = login_user($email, $password); 
            $response = $result; 
        }

    } else if ($action === 'signup') {
        // --- SIGNUP/REGISTRATION Logic ---
        $required_fields = ['fullname', 'email', 'password', 'confirm_password', 'terms'];
        $missing_fields = array_filter($required_fields, fn($field) => !isset($data[$field]));

        if (!empty($missing_fields)) {
            http_response_code(400); 
            $response['message'] = 'Missing required data for signup.';
        } else {
            $fullname = trim($data['fullname']);
            $email = trim($data['email']);
            $password = $data['password'];
            $confirm_password = $data['confirm_password'];
            $terms = $data['terms'];
            
            // Re-run critical client-side validation on the server for security
            if ($password !== $confirm_password) {
                $response['message'] = 'Passwords do not match.';
                $response['field'] = 'confirm-password'; 
            } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                 $response['message'] = 'Please enter a valid email address.';
                 $response['field'] = 'email';
            } else if (empty($fullname) || empty($email) || empty($password)) {
                 $response['message'] = 'All fields are required.';
            } else {
                $result = register_user($fullname, $email, $password);
                $response = $result; 
            }
        }
    } else {
        http_response_code(400);
        $response['message'] = 'Unknown action specified.';
    }
} else {
    http_response_code(405); 
    $response['message'] = 'Method not allowed.';
}

echo json_encode($response);
exit;
?>

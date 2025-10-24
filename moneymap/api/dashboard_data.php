<?php
// api/dashboard_data.php

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET');

// Note: This path uses '../' to go up from 'api/' to the root, then down into 'includes/'.
require_once '../includes/check_auth.php'; // Provides authentication check and $current_user_id
require_once '../includes/auth_functions.php'; // Provides data fetching functions and access to $pdo via db.php

// The $pdo variable is available here because auth_functions.php includes db.php.

$response = [
    'success' => false,
    'message' => 'An unexpected error occurred.' 
];

// If check_auth.php didn't redirect, the user is authenticated, 
// and $current_user_id is set.
if (isset($current_user_id) && $current_user_id > 0) {
    try {
        // Fetch the user's main metrics
        $metrics = fetch_user_dashboard_data($pdo, $current_user_id); // The function is in auth_functions.php
        
        // Fetch recent transactions (limit to 5)
        $transactions = fetch_recent_transactions($pdo, $current_user_id, 5); // The function is in auth_functions.php

        // Handle case where user exists but has no financial account records
        $metrics_output = ($metrics === false) ? [] : $metrics;
        
        $response = [
            'success' => true,
            'metrics' => $metrics_output,
            'transactions' => $transactions
        ];

    } catch (Exception $e) {
        error_log("Dashboard Data Fetch API Error: " . $e->getMessage());
        http_response_code(500);
        $response['message'] = 'Server error fetching financial data.';
    }
    
} else {
    // Failsafe for unauthenticated API access
    http_response_code(401);
    $response['message'] = 'Authentication required.';
}

echo json_encode($response);
exit;
?>
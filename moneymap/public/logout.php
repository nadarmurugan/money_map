<?php
// ===================================
// logout.php (Confirmation Page with Modal)
// ===================================

// 1. Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is even logged in. If not, just redirect.
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// We do NOT perform the logout here yet. We display the modal.
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Logout</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

<div id="logout-modal" class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full flex items-center justify-center z-50">
    
    <div class="relative bg-white rounded-lg shadow-xl max-w-sm w-full mx-4 p-6">
        
        <div class="flex items-center space-x-3 pb-4 border-b">
            <svg class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.3 16.938c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="text-xl font-semibold text-gray-900">Confirm Logout</h3>
        </div>

        <div class="py-4">
            <p class="text-gray-500">Are you sure you want to log out? You will need to sign in again to access your financial data.</p>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t">
            
            <a href="dashboard.php" class="py-2 px-4 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition duration-150">
                Cancel
            </a>

            <a href="do_logout.php" class="py-2 px-4 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition duration-150">
                Yes, Log Me Out
            </a>
        </div>
    </div>
</div>

</body>
</html>
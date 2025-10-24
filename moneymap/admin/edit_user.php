<?php
// ===================================
// edit_user.php (MoneyMap Admin Panel - Edit User Page)
// ===================================

// --- SESSION CHECK & ADMIN AUTH ---
session_start();

// Check if the specific admin session variable is set
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect unauthenticated users back to the login page
    header('Location: admin_login.php');
    exit();
}

// Hardcode admin name
$admin_name = "Admin User"; 

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
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false, 
        ];
        try { 
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options); 
        }
        catch(PDOException $e){ 
            error_log("Database connection failed: " . $e->getMessage());
            die("We're currently experiencing technical difficulties. Please try again later. 😟"); 
        }
    }
    return $pdo;
}
$pdo = db_connect();

// --- INITIAL DATA FETCH (User to Edit) ---
$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$user_data = null;

if ($user_id) {
    $stmt = $pdo->prepare("SELECT id, fullname, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch();
}

if (!$user_data) {
    // Redirect if user ID is invalid or not found
    $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Invalid user ID or user not found.'];
    header('Location: admin_dashboard.php');
    exit();
}

// Initialize form variables with existing data
$errors = [];
$form_data = [
    'id' => $user_data['id'],
    'fullname' => $user_data['fullname'],
    'email' => $user_data['email'],
    'password' => '' // Password field is kept empty for security
];
$original_email = $user_data['email'];

// --- FORM SUBMISSION HANDLER (Update Logic) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and collect form data
    $form_data['fullname'] = trim($_POST['fullname'] ?? '');
    $form_data['email'] = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // New password (optional)

    // 1. Validation
    if (empty($form_data['fullname'])) {
        $errors['fullname'] = 'Full Name is required.';
    }
    if (empty($form_data['email'])) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    }

    // Password validation (only if provided)
    if (!empty($password) && strlen($password) < 8) {
        $errors['password'] = 'New password must be at least 8 characters long.';
    }

    // 2. Check for duplicate email (if email changed)
    if (!isset($errors['email']) && $form_data['email'] !== $original_email) {
        $stmt = $pdo->prepare("SELECT COUNT(id) FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$form_data['email'], $user_data['id']]);
        if ($stmt->fetchColumn() > 0) {
            $errors['email'] = 'This email is already registered to another user.';
        }
    }

    // 3. Database Update if no errors
    if (empty($errors)) {
        $update_fields = [];
        $update_params = [];
        
        // Always update fullname and email
        $update_fields[] = 'fullname = ?';
        $update_params[] = $form_data['fullname'];
        $update_fields[] = 'email = ?';
        $update_params[] = $form_data['email'];

        // Update password only if a new one is provided
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $update_fields[] = 'password = ?'; // Use 'password' as per your schema
            $update_params[] = $password_hash;
        }

        $update_params[] = $user_data['id']; // ID for WHERE clause

        $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($update_params);
            
            // Set success message and redirect back to dashboard
            $_SESSION['admin_message'] = [
                'type' => 'success', 
                'text' => 'User ID ' . $user_data['id'] . ' updated successfully!'
            ];
            header('Location: admin_dashboard.php');
            exit();

        } catch (PDOException $e) {
            error_log("User update failed: " . $e->getMessage());
            $errors['db_error'] = 'A database error occurred while updating the user.';
        }
    }
    // If there were errors, the page reloads with form_data and errors
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User #<?php echo $user_data['id']; ?> | MoneyMap Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/4a98a3b83d.js" crossorigin="anonymous"></script> 
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: {
                        primary: '#10B981', // Emerald Green
                        secondary: '#059669', // Darker Emerald
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar {
            background-color: #10B981; /* primary color */
        }
        .form-input {
            @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary transition duration-150;
        }
        .error-message {
            @apply mt-1 text-sm text-red-600;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">

<div class="flex h-screen">
    <div class="sidebar w-64 p-5 flex flex-col text-white shadow-xl">
        <h2 class="text-3xl font-extrabold mb-8 text-center">MoneyMap 👑</h2>
        <p class="text-center mb-6 text-sm">Welcome, <?php echo htmlspecialchars($admin_name); ?></p>
        
        <nav class="flex-grow">
            <a href="admin_dashboard.php#overview" class="block py-3 px-4 rounded-lg transition hover:bg-secondary mb-2">
                Dashboard / Overview
            </a>
            <a href="admin_dashboard.php#user-management" class="block py-3 px-4 rounded-lg bg-secondary font-medium mb-2 transition hover:bg-green-700">
                User Management
            </a>
            <a href="admin_dashboard.php#export-data" class="block py-3 px-4 rounded-lg transition hover:bg-secondary mb-2">
                Export Data
            </a>
        </nav>

        <div class="mt-auto pt-4 border-t border-green-500">
            <a href="dashboard.php" class="block py-2 text-sm transition hover:text-gray-200">
                🌐 Go to User View
            </a>
            <a href="admin_dashboard.php?action=logout" class="block py-2 text-sm text-red-300 transition hover:text-red-100">
                ➡️ Logout
            </a>
        </div>
    </div>
    
    <div class="flex-1 overflow-y-auto p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit User: #<?php echo htmlspecialchars($user_data['id']); ?></h1>

        <?php if (isset($errors['db_error'])): ?>
            <div class="p-4 mb-4 text-sm bg-red-100 text-red-800 rounded-lg" role="alert">
                <?= htmlspecialchars($errors['db_error']); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-xl shadow-md max-w-lg mx-auto">
            <form method="POST" action="edit_user.php?id=<?php echo htmlspecialchars($user_data['id']); ?>">
                
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_data['id']); ?>">
                
                <div class="mb-5">
                    <label for="fullname" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" 
                           id="fullname" 
                           name="fullname" 
                           value="<?= htmlspecialchars($form_data['fullname']); ?>"
                           class="form-input <?= isset($errors['fullname']) ? 'border-red-500' : ''; ?>"
                           required>
                    <?php if (isset($errors['fullname'])): ?>
                        <p class="error-message"><?= htmlspecialchars($errors['fullname']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="mb-5">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?= htmlspecialchars($form_data['email']); ?>"
                           class="form-input <?= isset($errors['email']) ? 'border-red-500' : ''; ?>"
                           required>
                    <?php if (isset($errors['email'])): ?>
                        <p class="error-message"><?= htmlspecialchars($errors['email']); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="mb-5">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password (Leave blank to keep current password)</label>
                    <div class="relative">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input pr-10 <?= isset($errors['password']) ? 'border-red-500' : ''; ?>"
                               placeholder="******">
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                             <i id="eyeIcon" class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>
                    
                    <p class="mt-1 text-xs text-gray-500">Only fill this field to change the password. Must be at least 8 characters long.</p>
                    <?php if (isset($errors['password'])): ?>
                        <p class="error-message"><?= htmlspecialchars($errors['password']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="flex justify-between items-center mt-6">
                    <a href="admin_dashboard.php#user-management" class="py-2 px-4 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition font-medium">
                        Cancel / Back
                    </a>
                    <button type="submit" class="py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition shadow-md">
                        Save Changes ✅
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // toggle the eye icon
            if (type === 'text') {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        });
    });
</script>

</body>
</html>
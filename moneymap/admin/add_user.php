<?php
// ===================================
// add_user.php (MoneyMap Admin Panel - Add User Page, SUPER RESPONSIVE)
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

// --- DATABASE CONFIG & CONNECTION (Copied from admin_dashboard.php) ---
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

// --- VARIABLES FOR FORM AND MESSAGES ---
$errors = [];
$form_data = [
    'fullname' => '',
    'email' => '',
    'password' => ''
];

// --- FORM SUBMISSION HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and collect form data
    $form_data['fullname'] = trim($_POST['fullname'] ?? '');
    $form_data['email'] = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1. Validation
    if (empty($form_data['fullname'])) {
        $errors['fullname'] = 'Full Name is required.';
    }
    if (empty($form_data['email'])) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    }
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    }

    // 2. Check if email already exists
    if (!isset($errors['email'])) {
        $stmt = $pdo->prepare("SELECT COUNT(id) FROM users WHERE email = ?");
        $stmt->execute([$form_data['email']]);
        if ($stmt->fetchColumn() > 0) {
            $errors['email'] = 'This email is already registered.';
        }
    }

    // 3. Database Insertion if no errors
    if (empty($errors)) {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (fullname, email, password, created_at) VALUES (?, ?, ?, NOW())";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$form_data['fullname'], $form_data['email'], $password_hash]);
            
            // Set success message and redirect back to dashboard
            $_SESSION['admin_message'] = [
                'type' => 'success', 
                'text' => 'New user "' . htmlspecialchars($form_data['fullname']) . '" created successfully!'
            ];
            header('Location: admin_dashboard.php#user-management');
            exit();

        } catch (PDOException $e) {
            error_log("User creation failed: " . $e->getMessage());
            // Set error message
            $errors['db_error'] = 'A database error occurred while creating the user.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User | MoneyMap Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
            /* Full height sidebar, fixed on large screens, absolute on small */
            transition: transform 0.3s ease-in-out;
            background-color: #10B981; /* primary color */
            z-index: 40; /* Above main content */
        }
        /* Mobile-first: Sidebar starts off-screen */
        .sidebar-toggle:checked ~ .flex .sidebar {
            transform: translateX(0);
        }
        .sidebar-toggle:not(:checked) ~ .flex .sidebar {
            transform: translateX(-100%);
        }
        /* Tablet/Desktop: Sidebar is permanently visible */
        @media (min-width: 768px) {
            .sidebar-toggle:not(:checked) ~ .flex .sidebar {
                transform: translateX(0);
            }
        }
        /* Overlay for mobile when menu is open */
        .sidebar-toggle:checked ~ .flex .overlay {
            display: block;
        }

        /* Reusable Form Styles */
        .form-input {
             @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary transition duration-150;
        }
        .error-message {
             @apply mt-1 text-sm text-red-600;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">

<input type="checkbox" id="sidebar-toggle" class="hidden peer sidebar-toggle">

<div class="flex h-screen">

    <label for="sidebar-toggle" class="overlay fixed inset-0 bg-black bg-opacity-50 z-30 hidden md:hidden"></label>

    <div class="sidebar w-64 p-5 flex flex-col text-white shadow-2xl fixed inset-y-0 left-0 md:relative md:translate-x-0">
        <h2 class="text-3xl font-extrabold mb-8 text-center">MoneyMap 👑</h2>
        <p class="text-center mb-6 text-sm text-green-100">Welcome, <?php echo htmlspecialchars($admin_name); ?></p>
        
        <nav class="flex-grow">
            <label for="sidebar-toggle" class="absolute top-4 right-4 text-white text-xl md:hidden cursor-pointer hover:text-gray-200">
                <i class="fas fa-times"></i>
            </label>

            <a href="admin_dashboard.php#overview" class="block py-3 px-4 rounded-lg transition hover:bg-secondary mb-2">
                <i class="fas fa-chart-line mr-2"></i> Dashboard / Overview
            </a>
            <a href="admin_dashboard.php#user-management" class="block py-3 px-4 rounded-lg bg-secondary font-medium mb-2 transition hover:bg-green-700">
                <i class="fas fa-users-cog mr-2"></i> User Management
            </a>
            <a href="admin_dashboard.php#export-data" class="block py-3 px-4 rounded-lg transition hover:bg-secondary mb-2">
                <i class="fas fa-file-export mr-2"></i> Export Data
            </a>
        </nav>

        <div class="mt-auto pt-4 border-t border-green-700">
            <a href="../public/index.php" 
                class="block py-2 px-3 rounded-lg text-sm text-green-200 transition duration-150 ease-in-out 
                            hover:bg-green-700 hover:text-white group"
            >
                <span class=" group-hover:text-white transition duration-150">🌐</span> 
                Go to User View
            </a>
            <a href="admin_logout.php" 
                class="block py-2 px-3 rounded-lg text-sm text-white transition duration-150 ease-in-out 
                            bg-red-700 hover:bg-red-800 mt-2 cursor-pointer group text-center"
            >
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </div>
    </div>
    
    <div class="flex-1 overflow-y-auto">
        
        <header class="bg-white p-4 shadow-md sticky top-0 z-20 md:hidden flex justify-between items-center">
             <h1 class="text-xl font-bold text-gray-800">Add User</h1>
             <label for="sidebar-toggle" class="text-gray-700 text-2xl cursor-pointer">
                 <i class="fas fa-bars"></i>
             </label>
        </header>

        <div class="p-4 sm:p-6 lg:p-8 md:ml-0">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-6 hidden md:block">Add New User</h1>

            <?php if (isset($errors['db_error'])): ?>
                <div class="p-4 mb-4 text-sm bg-red-100 text-red-800 rounded-lg border border-red-300" role="alert">
                    <?= htmlspecialchars($errors['db_error']); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 sm:p-8 rounded-xl shadow-xl max-w-lg lg:max-w-xl mx-auto border-t-4 border-primary">
                <form method="POST" action="add_user.php">
                    
                    <div class="mb-5">
                        <label for="fullname" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" 
                               id="fullname" 
                               name="fullname" 
                               value="<?= htmlspecialchars($form_data['fullname']); ?>"
                               class="form-input <?= isset($errors['fullname']) ? 'border-red-500' : ''; ?>"
                               placeholder="Enter full name"
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
                               placeholder="user@example.com"
                               required>
                        <?php if (isset($errors['email'])): ?>
                            <p class="error-message"><?= htmlspecialchars($errors['email']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-input pr-10 <?= isset($errors['password']) ? 'border-red-500' : ''; ?>"
                                   placeholder="Minimum 8 characters"
                                   required>
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 focus:outline-none">
                                 <i id="eyeIcon" class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                        
                        <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters long.</p>
                        <?php if (isset($errors['password'])): ?>
                            <p class="error-message"><?= htmlspecialchars($errors['password']); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                        <a href="admin_dashboard.php#user-management" class="py-2 px-4 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition font-medium text-sm sm:text-base flex items-center">
                             <i class="fas fa-arrow-left mr-2 hidden sm:inline"></i> Cancel
                        </a>
                        <button type="submit" class="py-2 px-4 bg-primary hover:bg-secondary text-white font-medium rounded-lg transition shadow-md text-sm sm:text-base flex items-center">
                            <i class="fas fa-user-plus mr-2"></i> Create User
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="text-center mt-8 text-sm text-gray-500">
                <p>&copy; <?php echo date('Y'); ?> MoneyMap Admin Panel.</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const sidebarToggle = document.getElementById('sidebar-toggle');

        // --- Password Toggle Functionality ---
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

        // --- Handle Sidebar closing on link click for mobile (Optional UX improvement) ---
        const navLinks = document.querySelectorAll('.sidebar a');
        if(window.innerWidth < 768) {
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    sidebarToggle.checked = false;
                });
            });
        }
    });
</script>

</body>
</html>
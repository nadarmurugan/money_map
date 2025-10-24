<?php
session_start();

// --- 1. REDIRECT ALREADY LOGGED-IN ADMINS ---
// If the admin is already logged in, redirect them immediately to the dashboard.
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_dashboard.php');
    exit();
}

// ✅ Predefined Admin Credentials
$admin_username = "admin";
$admin_password = "123"; // Plain text (for demo). Ideally, hash in production!

$login_error = '';

// --- 2. HANDLE LOGIN FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Check credentials
    if ($username === $admin_username && $password === $admin_password) {
        // Successful login
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_dashboard.php");
        exit;
    } else {
        // Failed login
        $login_error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | MoneyMap</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: {
                        primary: '#10B981'
                    }
                }
            }
        }
    </script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100 font-sans">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold text-gray-800 mb-4 text-center">Admin Login</h1>
        <?php if($login_error): ?>
            <div class="text-red-500 text-sm mb-3 p-2 bg-red-100 rounded text-center"><?= htmlspecialchars($login_error) ?></div>
        <?php endif; ?>
        <form action="" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" id="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div class="relative">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary pr-10">
                <i id="togglePassword" class="fa-solid fa-eye absolute right-3 top-1/2 transform translate-y-2 cursor-pointer text-gray-400"></i>
            </div>
            <button type="submit" class="w-full py-2 px-4 bg-primary hover:bg-green-700 text-white font-medium rounded-lg transition">Login</button>
        </form>
  <a href="../public/index.php" class="text-xs text-primary-600 font-medium hover:text-primary-700 text-center block mt-4 color-red">
                            back to moneymap 
                        </a>
    </div>



    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.classList.toggle('fa-eye');
            togglePassword.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
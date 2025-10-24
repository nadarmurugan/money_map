<?php
// ===================================
// edit_goal.php (Goal Edit Page)
// ===================================

// --- SESSION CHECK ---
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$goal_id = $_GET['id'] ?? null;

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
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
        try { $pdo = new PDO($dsn, DB_USER, DB_PASS, $options); }
        catch(PDOException $e){ die("We're currently experiencing technical difficulties. 😟"); }
    }
    return $pdo;
}
$pdo = db_connect();

// --- HELPERS ---
function format_currency($amount, $sign = '₹') {
    return $sign . number_format($amount, 2);
}

// --- FETCH GOAL DETAILS ---
if (!$goal_id || !is_numeric($goal_id)) {
    $_SESSION['message'] = "Invalid goal ID.";
    $_SESSION['message_type'] = 'error';
    header('Location: manage_goals.php');
    exit();
}

$stmt = $pdo->prepare("SELECT id, goal_name, target_amount, saved_amount, target_date, status FROM goals WHERE id=? AND user_id=?");
$stmt->execute([$goal_id, $user_id]);
$goal = $stmt->fetch();

if (!$goal) {
    $_SESSION['message'] = "Goal not found or access denied.";
    $_SESSION['message_type'] = 'error';
    header('Location: manage_goals.php');
    exit();
}

// --- EDIT LOGIC ---
$message = null;
$message_type = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_goal_name = trim($_POST['goal_name']);
    $new_target_amount = floatval($_POST['target_amount']);
    $new_target_date = !empty($_POST['target_date']) ? $_POST['target_date'] : null;

    if ($new_target_amount <= 0 || $new_target_amount < $goal['saved_amount']) {
        $message = "Invalid target amount. Target must be positive and cannot be less than the current saved amount (" . format_currency($goal['saved_amount']) . ").";
        $message_type = 'error';
    } elseif (empty($new_goal_name)) {
        $message = "Goal name cannot be empty.";
        $message_type = 'error';
    } else {
        try {
            // Check if goal is achieved after target update
            $new_status = ($goal['saved_amount'] >= $new_target_amount) ? 'achieved' : 'active';
            
            $sql = "UPDATE goals SET goal_name = ?, target_amount = ?, target_date = ?, status = ? WHERE id = ? AND user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$new_goal_name, $new_target_amount, $new_target_date, $new_status, $goal_id, $user_id]);

            $_SESSION['message'] = "Goal '{$new_goal_name}' updated successfully!";
            $_SESSION['message_type'] = 'success';
            header('Location: manage_goals.php');
            exit();

        } catch (PDOException $e) {
            $message = "Error updating goal: " . $e->getMessage();
            $message_type = 'error';
            error_log("Goal update error: " . $e->getMessage());
        }
    }
}

// --- FETCH USER INFO (for sidebar/header) ---
$stmt = $pdo->prepare("SELECT COALESCE(fullname, 'MoneyMapper User') as fullname FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch();
$first_name = htmlspecialchars(explode(' ', $user_info['fullname'] ?? 'MoneyMapper')[0]);

// Ensure $goal is up-to-date with fetched data or post data on error
$goal_name_val = htmlspecialchars($_POST['goal_name'] ?? $goal['goal_name']);
$target_amount_val = htmlspecialchars($_POST['target_amount'] ?? $goal['target_amount']);
$target_date_val = htmlspecialchars($_POST['target_date'] ?? $goal['target_date']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Goal | <?= htmlspecialchars($goal['goal_name']) ?></title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    theme: {
        extend: {
            fontFamily: { sans: ['Poppins','sans-serif'] }, 
            colors: { 
                primary:{50:'#ECFDF5',100:'#D1FAE5',200:'#A7F3D0',300:'#6EE7B7',400:'#34D399',500:'#10B981',600:'#059669',700:'#047857',800:'#065F46',900:'#064E3B'},
                'red': { 50: '#FEF2F2', 600: '#DC2626' }, 
                'blue': { 50: '#EFF6FF', 500: '#3B82F6', 600: '#2563EB' }, 
            },
            keyframes: { 'slide-in': { '0%': { opacity: 0, transform: 'translateY(10px)' }, '100%': { opacity: 1, transform: 'translateY(0)' } } },
            animation: { 'slide-in': 'slide-in 0.6s ease-out' }
        }
    }
}
</script>
<style>
body{font-family:'Poppins',sans-serif; background:#f4f7f9;} 
.dashboard-card{ background:rgba(255,255,255,1); border-radius:1rem; border:1px solid theme('colors.gray.200'); box-shadow:0 8px 20px rgba(0,0,0,0.08); animation: slide-in 0.6s backwards; }
.main-content { margin-left: 280px; }
@media (max-width: 1024px) { .main-content { margin-left: 0; padding-top: 5rem; } }
</style>
</head>
<body class="min-h-screen">

<!-- Simplified Header for Goal Page - keeps branding and menu -->
<header class="fixed top-0 left-0 right-0 bg-white border-b border-gray-200 shadow-sm z-40 p-4">
    <div class="flex items-center justify-between max-w-7xl mx-auto">
        <a href="manage_goals.php" class="text-xl font-bold text-gray-900">
            <i class="fa-solid fa-bullseye-arrow text-primary-600 mr-1"></i> Editing: <?= htmlspecialchars($goal['goal_name']) ?>
        </a>
        <a href="manage_goals.php" class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300 transition duration-300 font-medium text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Cancel
        </a>
    </div>
</header>

<main id="main-content" class="main-content py-24 px-4 lg:px-8 max-w-3xl mx-auto space-y-10">
    <h1 class="text-4xl font-extrabold text-gray-900 mb-2">✏️ Edit Goal: <?= htmlspecialchars($goal['goal_name']) ?></h1>
    <p class="text-lg text-gray-600">Update the details of your savings goal.</p>
    
    <!-- Status Message Display -->
    <?php if ($message): ?>
        <div class="p-4 rounded-lg <?= $message_type === 'success' ? 'bg-primary-100 text-primary-800 border border-primary-400' : 'bg-red-100 text-red-800 border border-red-400' ?>" 
             role="alert">
            <p class="font-medium"><?= htmlspecialchars($message) ?></p>
        </div>
    <?php endif; ?>
    
    <div class="dashboard-card p-8 space-y-6">
        <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded-lg">
            <p class="text-sm text-blue-800">Current Saved Amount: <span class="font-bold"><?= format_currency($goal['saved_amount']) ?></span>. New Target Amount must be equal to or greater than this value.</p>
        </div>
        <form action="edit_goal.php?id=<?= $goal_id ?>" method="POST" class="space-y-4">
            <div>
                <label for="goal_name" class="block text-sm font-medium text-gray-700 mb-1">Goal Name</label>
                <input type="text" id="goal_name" name="goal_name" required value="<?= $goal_name_val ?>"
                       class="w-full border border-gray-300 rounded-lg p-3 focus:ring-primary-500 focus:border-primary-500 transition duration-150 shadow-sm">
            </div>
            <div>
                <label for="target_amount" class="block text-sm font-medium text-gray-700 mb-1">Target Amount (₹)</label>
                <input type="number" id="target_amount" name="target_amount" required min="<?= $goal['saved_amount'] > 0 ? $goal['saved_amount'] : '1' ?>" step="0.01" value="<?= $target_amount_val ?>"
                       class="w-full border border-gray-300 rounded-lg p-3 focus:ring-primary-500 focus:border-primary-500 transition duration-150 shadow-sm">
            </div>
            <div>
                <label for="target_date" class="block text-sm font-medium text-gray-700 mb-1">Target Completion Date (Optional)</label>
                <input type="date" id="target_date" name="target_date" value="<?= $target_date_val ?>"
                       class="w-full border border-gray-300 rounded-lg p-3 focus:ring-primary-500 focus:border-primary-500 transition duration-150 shadow-sm">
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white p-3 rounded-lg hover:bg-blue-600 transition duration-300 font-medium text-lg shadow-md hover:shadow-xl">
                <i class="fa-solid fa-save mr-2"></i> Apply Changes
            </button>
        </form>
    </div>
</main>
</body>
</html>

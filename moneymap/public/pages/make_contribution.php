<?php
// ===================================
// make_contribution.php (Goal Contribution Page)
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

$stmt = $pdo->prepare("SELECT goal_name, target_amount, saved_amount, status FROM goals WHERE id=? AND user_id=?");
$stmt->execute([$goal_id, $user_id]);
$goal = $stmt->fetch();

if (!$goal) {
    $_SESSION['message'] = "Goal not found or access denied.";
    $_SESSION['message_type'] = 'error';
    header('Location: manage_goals.php');
    exit();
}
if ($goal['status'] === 'achieved') {
    $_SESSION['message'] = "This goal is already achieved! No further contributions needed.";
    $_SESSION['message_type'] = 'success';
    header('Location: manage_goals.php');
    exit();
}

$remaining_target = $goal['target_amount'] - $goal['saved_amount'];

// --- CONTRIBUTION LOGIC ---
$message = null;
$message_type = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contribution_amount = floatval($_POST['contribution_amount'] ?? 0);

    if ($contribution_amount <= 0) {
        $message = "Please enter a valid contribution amount greater than zero.";
        $message_type = 'error';
    } else {
        try {
            $new_saved_amount = $goal['saved_amount'] + $contribution_amount;
            $new_status = ($new_saved_amount >= $goal['target_amount']) ? 'achieved' : 'active';
            
            $sql = "UPDATE goals SET saved_amount = ?, status = ? WHERE id = ? AND user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$new_saved_amount, $new_status, $goal_id, $user_id]);

            $status_msg = ($new_status === 'achieved') ? " The goal is now ACHIEVED! 🎉" : "";

            $_SESSION['message'] = "Successfully contributed " . format_currency($contribution_amount) . " to '{$goal['goal_name']}'.{$status_msg}";
            $_SESSION['message_type'] = 'success';
            header('Location: manage_goals.php');
            exit();

        } catch (PDOException $e) {
            $message = "Error recording contribution: " . $e->getMessage();
            $message_type = 'error';
            error_log("Contribution error: " . $e->getMessage());
        }
    }
}

// --- MESSAGE DISPLAY (from session) ---
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// --- FETCH USER INFO (for sidebar/header) ---
$stmt = $pdo->prepare("SELECT COALESCE(fullname, 'MoneyMapper User') as fullname FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$first_name = htmlspecialchars(explode(' ', $user['fullname'] ?? 'MoneyMapper')[0]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contribute | <?= htmlspecialchars($goal['goal_name']) ?></title>

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
            <i class="fa-solid fa-bullseye-arrow text-primary-600 mr-1"></i> Goal: <?= htmlspecialchars($goal['goal_name']) ?>
        </a>
        <a href="manage_goals.php" class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300 transition duration-300 font-medium text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to Goals
        </a>
    </div>
</header>

<main id="main-content" class="main-content py-24 px-4 lg:px-8 max-w-5xl mx-auto space-y-10">
    <h1 class="text-4xl font-extrabold text-gray-900 mb-2">💰 Make Contribution</h1>
    <p class="text-lg text-gray-600">Add funds to your **<?= htmlspecialchars($goal['goal_name']) ?>** goal.</p>
    
    <!-- Status Message Display -->
    <?php if ($message): ?>
        <div class="p-4 rounded-lg <?= $message_type === 'success' ? 'bg-primary-100 text-primary-800 border border-primary-400' : 'bg-red-100 text-red-800 border border-red-400' ?>" 
             role="alert">
            <p class="font-medium"><?= htmlspecialchars($message) ?></p>
        </div>
    <?php endif; ?>
    
    <div class="dashboard-card p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Goal Overview Panel -->
        <div class="space-y-4 border-r pr-8 border-gray-200">
            <h3 class="text-2xl font-semibold text-gray-800">Goal Progress</h3>
            <p class="text-sm text-gray-500">
                Target: <span class="font-bold text-gray-800"><?= format_currency($goal['target_amount']) ?></span>
            </p>
            <p class="text-sm text-gray-500">
                Saved: <span class="font-bold text-primary-600"><?= format_currency($goal['saved_amount']) ?></span>
            </p>
            <p class="text-lg font-extrabold <?= $remaining_target > 0 ? 'text-blue-500' : 'text-primary-600' ?>">
                Remaining: <?= format_currency($remaining_target) ?>
            </p>

            <!-- Progress Bar -->
            <?php $progress_percent = ($goal['target_amount'] > 0) ? min(100, round(($goal['saved_amount'] / $goal['target_amount']) * 100)) : 0; ?>
            <div class="w-full bg-gray-200 rounded-full h-3 mt-4">
                <div class="h-3 rounded-full bg-primary-500 transition-all duration-500 ease-out" style="width: <?= $progress_percent ?>%;"></div>
            </div>
            <p class="text-sm text-primary-700 font-bold mt-2"><?= $progress_percent ?>% Complete</p>
        </div>

        <!-- Contribution Form -->
        <div class="space-y-6">
            <h3 class="text-2xl font-semibold text-gray-800">New Contribution</h3>
            <form action="make_contribution.php?id=<?= $goal_id ?>" method="POST" class="space-y-4">
                <p class="text-gray-600 text-sm">Note: This action records a contribution. You must manage the actual money transfer in your real bank accounts.</p>
                <div>
                    <label for="contribution_amount" class="block text-base font-medium text-gray-700 mb-1">Contribution Amount (₹)</label>
                    <input type="number" id="contribution_amount" name="contribution_amount" required min="0.01" step="0.01"
                           max="<?= $remaining_target > 0 ? $remaining_target : '9999999.00' ?>"
                           placeholder="<?= format_currency(min(1000, $remaining_target), '') ?>"
                           class="w-full border-2 border-primary-300 rounded-lg p-3 text-lg focus:ring-primary-500 focus:border-primary-500 transition duration-150 shadow-inner">
                </div>
                
                <button type="submit" class="w-full bg-primary-600 text-white p-3 rounded-lg hover:bg-primary-700 transition duration-300 font-medium text-lg shadow-md hover:shadow-xl">
                    <i class="fa-solid fa-hand-holding-dollar mr-2"></i> Record Contribution
                </button>
            </form>
        </div>
    </div>
</main>
</body>
</html>

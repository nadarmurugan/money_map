<?php
// ===================================
// dashboard.php (Professional Final Enhanced Version - Teal/Green Theme)
// ===================================

// --- SESSION CHECK ---
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// --- DATABASE CONFIG ---
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'money_map');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// --- DB CONNECTION ---
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

// --- FETCH USER INFO ---
$stmt = $pdo->prepare("SELECT COALESCE(fullname, 'MoneyMapper User') as fullname FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_name = htmlspecialchars($user['fullname'] ?? 'MoneyMapper'); 
$first_name = htmlspecialchars(explode(' ', $user_name)[0]);

// --- DASHBOARD DATA ---
function fetch_total($pdo,$user_id,$type){
    $stmt=$pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id=? AND type=?");
    $stmt->execute([$user_id,$type]);
    return $stmt->fetchColumn();
}
$total_income = (float)fetch_total($pdo,$user_id,'income');
$total_expense = (float)fetch_total($pdo,$user_id,'expense');
$balance = $total_income - $total_expense;

// --- GOALS DATA ---
function fetch_user_goals($pdo, $user_id) {
    // Fetch all active goals
    $stmt = $pdo->prepare("SELECT id, goal_name, target_amount, saved_amount, target_date FROM goals WHERE user_id=? AND status='active' ORDER BY target_date ASC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}
$active_goals = fetch_user_goals($pdo, $user_id);

// Calculate total saved amount across all active goals
$total_saved_across_goals = array_reduce($active_goals, function($sum, $goal) {
    return $sum + (float)$goal['saved_amount'];
}, 0.00);

// --- CATEGORY-WISE DATA ---
$stmt=$pdo->prepare("SELECT COALESCE(category, 'Uncategorized') as category, SUM(amount) as total FROM transactions WHERE user_id=? AND type='expense' GROUP BY COALESCE(category, 'Uncategorized') ORDER BY total DESC");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll();

// --- RECENT TRANSACTIONS ---
$stmt=$pdo->prepare("SELECT COALESCE(date, created_at) AS txn_date, category, description, amount, type FROM transactions WHERE user_id=? ORDER BY txn_date DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent = $stmt->fetchAll();

// --- NOTE MANAGEMENT (UPDATED LOGIC) ---
$note_message = [];

// 1. Handle POST request for SAVE or DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['note_action'])) {
    
    // 1.1 Handle Note Deletion
    if ($_POST['note_action'] === 'delete_note' && isset($_POST['note_id'])) {
        $note_id = (int)$_POST['note_id'];
        try {
            // Ensure the user owns the note before deleting
            $stmt = $pdo->prepare("DELETE FROM user_notes WHERE id=? AND user_id=?");
            $stmt->execute([$note_id, $user_id]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['note_message'] = ['type' => 'success', 'text' => 'Note deleted successfully!'];
            } else {
                $_SESSION['note_message'] = ['type' => 'error', 'text' => 'Note not found or you do not have permission to delete it.'];
            }
        } catch (PDOException $e) {
            error_log("Note deletion failed: " . $e->getMessage());
            $_SESSION['note_message'] = ['type' => 'error', 'text' => 'Error deleting note. Please try again.'];
        }
    }
    
    // 1.2 Handle Note Saving
    if ($_POST['note_action'] === 'save_note') {
        $note_content = trim($_POST['note_content']);
        if (!empty($note_content)) {
            try {
                // NOTE: Assumes a table named 'user_notes' with columns: id, user_id, note_content, created_at
                $stmt = $pdo->prepare("INSERT INTO user_notes (user_id, note_content) VALUES (?, ?)");
                $stmt->execute([$user_id, $note_content]);
                $_SESSION['note_message'] = ['type' => 'success', 'text' => 'Note saved successfully!'];
            } catch (PDOException $e) {
                error_log("Note save failed: " . $e->getMessage());
                $_SESSION['note_message'] = ['type' => 'error', 'text' => 'Error saving note. Please try again.'];
            }
        } else {
            $_SESSION['note_message'] = ['type' => 'error', 'text' => 'Note cannot be empty.'];
        }
    }
    
    // Redirect to self to prevent form resubmission (Post/Redirect/Get pattern)
    header('Location: dashboard.php');
    exit();
}

// Check for and display session message (from the redirect)
if (isset($_SESSION['note_message'])) {
    $note_message = $_SESSION['note_message'];
    unset($_SESSION['note_message']);
}

// 2. Fetch all existing notes (NO LIMIT)
$user_notes = [];
try {
    // Fetches all notes associated with the user, including the ID for delete/edit actions
    $stmt = $pdo->prepare("SELECT id, note_content, created_at FROM user_notes WHERE user_id=? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $user_notes = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Note fetch failed: " . $e->getMessage());
    // Error will be displayed if $note_message was set during an operation
}

// --- CURRENCY FORMATTER ---
function format_currency($amount, $sign = '₹') {
    return $sign . number_format($amount, 2);
}

// --- EXTENDED CURRENCY LIST (20 major currencies for demonstration) ---
$all_currencies = [
    'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥', 
    'INR' => '₹', 'AUD' => 'A$', 'CAD' => 'C$', 'CHF' => 'CHF', 
    'CNY' => '¥', 'SEK' => 'kr', 'NZD' => 'NZ$', 'SGD' => 'S$',
    'HKD' => 'HK$', 'NOK' => 'kr', 'KRW' => '₩', 'BRL' => 'R$',
    'ZAR' => 'R', 'RUB' => '₽', 'MXN' => 'Mex$', 'AED' => 'د.إ',
];
$all_currencies_json = json_encode($all_currencies);

// --- SIMULATED CROSS RATES (Rates to 1 USD) ---
$rates_to_usd = [
    'USD' => 1.00, 'EUR' => 1.08, 'GBP' => 1.25, 'JPY' => 0.0067, 
    'INR' => 0.012, 'AUD' => 0.65, 'CAD' => 0.73, 'CHF' => 1.10, 
    'CNY' => 0.14, 'SEK' => 0.091, 'NZD' => 0.60, 'SGD' => 0.74,
    'HKD' => 0.13, 'NOK' => 0.093, 'KRW' => 0.00073, 'BRL' => 0.20,
    'ZAR' => 0.053, 'RUB' => 0.011, 'MXN' => 0.059, 'AED' => 0.27,
];
$rates_to_usd_json = json_encode($rates_to_usd);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | MoneyMap Pro 📈</title>
<meta name="description" content="Professional and responsive personal financial dashboard with scroll animations.">

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
                // Requested Teal/Green Palette
                primary:{50:'#ECFDF5',100:'#D1FAE5',200:'#A7F3D0',300:'#6EE7B7',400:'#34D399',500:'#10B981',600:'#059669',700:'#047857',800:'#065F46',900:'#064E3B'},
                'red': { 50: '#FEF2F2', 100: '#FEE2E2', 600: '#DC2626' }, 
                'blue': { 50: '#EFF6FF', 500: '#3B82F6', 600: '#2563EB' }, 
                'yellow': { 500: '#EAB308' },
                'orange': { 500: '#F97316' }
            },
            keyframes: {
                'pulse-sm': { '0%, 100%': { opacity: 1 }, '50%': { opacity: .7 } },
            },
            animation: {
                'pulse-sm': 'pulse-sm 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            }
        }
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<style>
/* Global Styles */
body{font-family:'Poppins',sans-serif; background:#f4f7f9;} 
.dashboard-card{
    background:rgba(255,255,255,1);
    border-radius:1rem; 
    border:1px solid theme('colors.gray.200'); 
    box-shadow:0 8px 20px rgba(0,0,0,0.08); 
}
/* Stat Card Enhancement */
.stats-card{
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-left-width: 5px; 
    cursor: pointer;
}
.stats-card:hover{
    transform: translateY(-4px);
    box-shadow: 0 12px 25px rgba(0,0,0,0.1); 
}

/* Sidebar Specifics */
.sidebar {
    width: 280px;
    transition: transform 0.3s ease-in-out, width 0.3s ease; 
    box-shadow: 4px 0 15px rgba(0,0,0,0.08); 
    z-index: 50;
}
.nav-item {
    transition: all 0.2s ease;
    padding: 0.75rem 1.25rem;
    border-radius: 0.6rem;
}
.nav-item:hover {
    background-color: theme('colors.primary.100');
}

/* Transaction Table */
.table th{background-color:theme('colors.primary.700');color:white;font-weight:600;}
.table td, .table th{padding:0.75rem 1rem;text-align:left;} 
.table tbody tr:hover {
    background-color: theme('colors.primary.50');
}

/* Currency Converter Focus Style */
.input-focus:focus {
    box-shadow: 0 0 0 3px theme('colors.primary.200'); /* Teal focus ring */
    border-color: theme('colors.primary.600');
}

/* Responsive Overrides (Crucial for Mobile) */
@media (max-width: 1023px) {
    .sidebar {
        position: fixed; 
        height: 100vh;
        transform: translateX(-100%);
    }
    .sidebar.open {
        transform: translateX(0);
    }
    .main-content {
        padding-top: 6rem; /* UPDATED: Changed from 5rem to 6rem */
        margin-left: 0 !important;
    }
}
@media (min-width: 1024px) {
    .main-content {
        margin-left: 280px;
        transition: margin-left 0.3s ease; 
    }
}

/* Custom Class for Scroll Animation Base State (Initial hidden state) */
.scroll-animate {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
/* Class to trigger animation (will be added by JS) */
.scroll-animate.animated {
    opacity: 1;
    transform: translateY(0);
}
</style>
</head>
<body class="min-h-screen">

<aside id="sidebar" class="sidebar fixed top-0 left-0 bg-white lg:block h-full border-r border-gray-200 p-6">
    <div class="flex flex-col h-full">
        <a href="dashboard.php" class="inline-flex items-center text-3xl font-extrabold text-gray-900 p-2 mb-10">
            <i class="fa-solid fa-map-location-dot text-primary-600 mr-2"></i>Money<span class="text-gray-900">Map</span>
        </a>

        <nav class="flex-grow space-y-2">
            <a href="dashboard.php" class="nav-item bg-primary-600 text-white flex items-center space-x-4 text-black active" style="background: linear-gradient(90deg, theme('colors.primary.600') 0%, theme('colors.primary.500') 100%);">
                <i class="fa-solid fa-house w-6 text-xl"></i>
                <span class="text-lg">Dashboard</span>
            </a>
          <a href="../public/pages/manage_goals.php" class="nav-item flex items-center space-x-4 text-gray-700 hover:text-primary-800">
    <i class="fa-solid fa-piggy-bank w-6 text-xl"></i>
    <span class="text-lg">Savings Goals</span>
</a>

            <a href="../public/pages/add-transaction.php" class="nav-item flex items-center space-x-4 text-gray-700 hover:text-primary-800">
                <i class="fa-solid fa-plus-circle w-6 text-xl"></i>
                <span class="text-lg">New Transaction</span>
            </a>
            <a href="../public/pages/reports.php"  class="nav-item flex items-center space-x-4 text-gray-700 hover:text-primary-800">
                <i class="fa-solid fa-chart-column w-6 text-xl"></i>
                <span class="text-lg">Analytics & Reports</span>
            </a>

        </nav>

        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50 mt-auto hover:bg-white transition duration-300">
            <div class="flex items-center justify-between space-x-3">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center text-white font-semibold text-lg">
                        <?= strtoupper(substr($first_name,0,1)) ?>
                    </div>
                   <a href="pages/profile.php" class="flex-shrink-0 group block">
    <div class="min-w-0">
        <p class="text-sm font-semibold text-gray-900 truncate"><?= $user_name ?></p>
        <p class="text-xs text-primary-700 font-medium truncate">Your Profile</p>
    </div>
</a>
                </div>
                <a href="logout.php" class="p-2 text-gray-500 hover:text-red-600 transition-colors" title="Logout">
                    <i class="fa-solid fa-right-from-bracket text-lg"></i>
                </a>
            </div>
        </div>
    </div>
</aside>

<header class="lg:hidden fixed top-0 left-0 right-0 bg-white border-b border-gray-200 shadow-md z-40">
    <div class="flex items-center justify-between p-4">
        <button id="menu-btn" class="text-gray-600 hover:text-primary-600 p-2" aria-label="Open menu">
            <i class="fa-solid fa-bars text-xl"></i>
        </button>
        <a href="dashboard.php" class="text-xl font-bold text-gray-900">
            <i class="fa-solid fa-map-location-dot text-primary-600 mr-1"></i>Money<span class="text-gray-900">Map</span>
        </a>
        <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center text-white font-semibold text-sm animate-pulse-sm">
            <?= strtoupper(substr($first_name,0,1)) ?>
        </div>
    </div>
</header>


<main id="main-content" class="main-content py-8 lg:py-12 px-4 lg:px-8 space-y-10">

<!-- FIX: Changed base text size to text-3xl and scaled up to md:text-4xl for better mobile fit. -->
<h1 class="pt-[6rem] sm:pt-[6rem] md:pt-[2rem] text-2xl sm:text-3xl md:text-4xl font-extrabold text-gray-900 mb-2 scroll-animate" 
    data-scroll-animate 
    style="transition-delay: 0s;">
    👋 Welcome Back, <?= $first_name ?>!
</h1>
<p class="text-lg text-gray-600 mb-8 sm:text-base scroll-animate" data-scroll-animate style="transition-delay: 0.1s;">Your financial overview at a glance. Let's map your success! 🎯</p>

<!-- Stat Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="stats-card p-6 dashboard-card border-l-primary-500 scroll-animate" data-scroll-animate style="transition-delay: 0.2s;">
        <h3 class="flex items-center text-gray-700 text-base font-medium mb-1"><i class="fa-solid fa-circle-up text-primary-500 mr-2"></i> Total Income</h3>
        <p class="text-3xl lg:text-4xl font-extrabold text-primary-600 mt-2"><?= format_currency($total_income) ?></p>
        <p class="text-sm text-gray-500 mt-1">Overall earnings tracked</p>
    </div>
    <div class="stats-card p-6 dashboard-card border-l-red-600 scroll-animate" data-scroll-animate style="transition-delay: 0.3s;">
        <h3 class="flex items-center text-gray-700 text-base font-medium mb-1"><i class="fa-solid fa-circle-down text-red-600 mr-2"></i> Total Expense</h3>
        <p class="text-3xl lg:text-4xl font-extrabold text-red-600 mt-2"><?= format_currency($total_expense) ?></p>
        <p class="text-sm text-gray-500 mt-1">Total money spent so far</p>
    </div>
    <div class="stats-card p-6 dashboard-card border-l-blue-500 scroll-animate" data-scroll-animate style="transition-delay: 0.4s;">
        <h3 class="flex items-center text-gray-700 text-base font-medium mb-1"><i class="fa-solid fa-wallet text-blue-500 mr-2"></i> Current Balance</h3>
        <p class="text-3xl lg:text-4xl font-extrabold text-blue-500 mt-2"><?= format_currency($balance) ?></p>
        <p class="text-sm text-gray-500 mt-1">Net funds remaining</p>
    </div>
    <div class="stats-card p-6 dashboard-card border-l-yellow-500 scroll-animate" data-scroll-animate style="transition-delay: 0.5s;">
        <h3 class="flex items-center text-gray-700 text-base font-medium mb-1"><i class="fa-solid fa-piggy-bank text-yellow-500 mr-2"></i> Total Savings Progress</h3>
        <p class="text-3xl lg:text-4xl font-extrabold text-orange-500 mt-2"><?= format_currency($total_saved_across_goals) ?></p>
        <p class="text-sm text-gray-500 mt-1">Saved towards active goals</p>
    </div>
</div>

<!-- Active Savings Goals -->
<div class="dashboard-card p-6 scroll-animate" data-scroll-animate style="transition-delay: 0.6s;">
    <div class="flex justify-between items-center mb-6 flex-wrap gap-3">
        <h3 class="text-xl font-semibold text-gray-800"><i class="fa-solid fa-bullseye-arrow mr-2 text-primary-600"></i> Active Savings Goals</h3>
        <a href="../public/pages/manage_goals.php" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition duration-300 font-medium text-sm shadow-md hover:shadow-lg">
            <i class="fa-solid fa-plus-circle mr-1"></i> New Goal
        </a>
    </div>

    <div class="space-y-4">
        <?php if (count($active_goals) > 0): ?>
            <?php foreach($active_goals as $goal):
                $progress_percent = ($goal['target_amount'] > 0) ? min(100, round(((float)$goal['saved_amount'] / (float)$goal['target_amount']) * 100)) : 0;
            ?>
            <div class="p-4 border border-gray-200 rounded-lg hover:bg-primary-50 transition duration-300">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2">
                    <h4 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($goal['goal_name']) ?></h4>
                    <p class="text-sm font-medium text-primary-600 mt-1 sm:mt-0">
                        <?= format_currency($goal['saved_amount']) ?> / <?= format_currency($goal['target_amount']) ?>
                    </p>
                </div>
                <!-- Progress Bar -->
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="h-3 rounded-full bg-primary-600 transition-all duration-500 ease-out shadow-md" style="width: <?= $progress_percent ?>%;"></div>
                </div>
                <div class="flex justify-between items-center mt-2 text-xs text-gray-500">
                    <span class="font-bold text-primary-700"><?= $progress_percent ?>% Complete</span>
                    <?php if ($goal['target_date']): ?>
                        <span>Target Date: <?= date('M j, Y', strtotime($goal['target_date'])) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-gray-500 p-6 border border-dashed rounded-lg">Create a goal to start saving for your dreams! ✈️</p>
        <?php endif; ?>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    <div class="dashboard-card p-6 scroll-animate" data-scroll-animate style="transition-delay: 0.7s;">
        <h3 class="text-xl font-semibold text-gray-800 mb-4"><i class="fa-solid fa-chart-simple mr-2 text-primary-600"></i> Income Allocation (Expense vs Remaining)</h3>
        <!-- Container optimized for responsive chart resizing -->
        <div class="relative w-full h-80 sm:h-96"> 
            <canvas id="financeChart1"></canvas>
        </div>
    </div>

    <div class="dashboard-card p-6 scroll-animate" data-scroll-animate style="transition-delay: 0.8s;">
        <h3 class="text-xl font-semibold text-gray-800 mb-4"><i class="fa-solid fa-tags mr-2 text-primary-600"></i> Expenses by Category</h3>
        <div class="relative w-full h-80 sm:h-96"> 
            <canvas id="financeChart2"></canvas>
        </div>
    </div>
</div>

<!-- Transactions & Converter Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="dashboard-card p-6 lg:col-span-2 scroll-animate" data-scroll-animate style="transition-delay: 0.9s;">
        <div class="flex justify-between items-center mb-6 flex-wrap gap-3">
            <h3 class="text-xl font-semibold text-gray-800"><i class="fa-solid fa-list-ul mr-2 text-primary-600"></i> Recent Transactions</h3>
            <a href="../public/pages/add-transaction.php" class="bg-primary-600 text-white py-2 px-4 rounded-lg hover:bg-primary-700 transition duration-300 font-medium text-sm shadow-md hover:shadow-lg">
                <i class="fa-solid fa-plus mr-1"></i> Add New
            </a>
        </div>
        <?php if(count($recent) > 0): ?>
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="table w-full border-collapse text-sm">
                <thead>
                    <tr><th class="rounded-tl-lg">Date</th><th>Category</th><th class="hidden sm:table-cell">Description</th><th>Amount</th><th class="rounded-tr-lg">Type</th></tr>
                </thead>
                <tbody>
                    <?php foreach($recent as $txn): ?>
                    <tr class="border-t border-gray-100">
                        <td><?= date('M j, Y', strtotime($txn['txn_date'])) ?></td>
                        <td class="font-medium"><?= htmlspecialchars($txn['category']) ?></td>
                        <td class="text-gray-500 hidden sm:table-cell truncate max-w-xs"><?= htmlspecialchars($txn['description']) ?></td>
                        <td class="font-semibold"><?= format_currency($txn['amount']) ?></td>
                        <td class="<?= $txn['type']=='income'?'text-primary-600':'text-red-600' ?> font-medium"><?= ucfirst($txn['type']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 flex justify-end">
            <a href="../public/pages/view_transactions.php" class="text-primary-600 hover:text-primary-700 transition duration-300 font-medium text-sm">
                View All Transactions <i class="fa-solid fa-arrow-right ml-1"></i>
            </a>
        </div>
        <?php else: ?>
        <p class="text-center text-gray-500 p-8 border border-dashed rounded-lg">🚀 No transactions yet. Click 'Add New' to get started!</p>
        <?php endif; ?>
    </div>

    

    <div class="dashboard-card p-6 flex flex-col scroll-animate" data-scroll-animate style="transition-delay: 1.0s;">
        <h3 class="text-xl font-semibold text-gray-800 mb-4"><i class="fa-solid fa-dollar-sign mr-2 text-primary-600"></i> Currency Converter 💱</h3>
        <form id="currencyForm" class="space-y-4 flex-grow">
            <div class="flex flex-col sm:flex-row gap-2 items-center"> 
                <select id="fromCurrency" class="w-full border border-gray-300 rounded-lg p-3 bg-white shadow-sm focus:ring-primary-500 focus:border-primary-500 transition duration-150 input-focus">
                    <?php foreach ($all_currencies as $code => $symbol): ?>
                        <option value="<?= $code ?>" <?= $code === 'INR' ? 'selected' : '' ?>><?= $code ?> (<?= $symbol ?>)</option>
                    <?php endforeach; ?>
                </select>
                
                <button type="button" id="swapBtn" class="p-2 text-gray-500 hover:text-primary-600 transition duration-150" title="Swap Currencies">
                    <i class="fa-solid fa-sync-alt text-lg"></i>
                </button>
                
                <select id="toCurrency" class="w-full border border-gray-300 rounded-lg p-3 bg-white shadow-sm focus:ring-primary-500 focus:border-primary-500 transition duration-150 input-focus">
                    <?php foreach ($all_currencies as $code => $symbol): ?>
                        <option value="<?= $code ?>" <?= $code === 'USD' ? 'selected' : '' ?>><?= $code ?> (<?= $symbol ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="number" id="amount" placeholder="Amount to Convert" class="w-full border border-gray-300 rounded-lg p-3 shadow-inner text-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 input-focus" min="0" step="any" />
            <button type="button" id="convertBtn" class="w-full bg-blue-500 text-white p-3 rounded-lg hover:bg-blue-600 transition duration-300 font-medium text-lg shadow-md hover:shadow-lg">
                Calculate Conversion
            </button>
            <p id="convertedResult" class="text-center mt-4 text-xl font-extrabold text-gray-900 min-h-[1.5em] flex flex-col items-center justify-center p-2 rounded-lg bg-blue-50 text-base sm:text-xl"></p>
        </form>
    </div>

</div>

<!-- Expense Journal / Notes Section: Always 1 col -->
<div class="grid grid-cols-1 gap-6 mb-12">
    <div class="dashboard-card p-6 scroll-animate" data-scroll-animate style="transition-delay: 1.1s;">
        <h3 class="text-xl font-semibold text-gray-800 mb-4"><i class="fa-solid fa-pencil-alt mr-2 text-primary-600"></i> Expense Journal / Notes</h3>
        
        <?php if (!empty($note_message)): ?>
            <div class="p-3 mb-4 rounded-lg <?= $note_message['type'] === 'success' ? 'bg-primary-100 text-primary-800' : 'bg-red-100 text-red-800' ?> font-medium">
                <?= htmlspecialchars($note_message['text']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="dashboard.php" class="flex flex-col">
            <input type="hidden" name="note_action" value="save_note">
            <textarea name="note_content" rows="3" placeholder="Bought groceries for family gathering... Remember to budget for next month's trip!" 
                             class="w-full border border-gray-300 rounded-lg shadow-inner p-3 resize-none text-base focus:ring-primary-500 focus:border-primary-500 mb-3 transition duration-150 input-focus"></textarea>
            <button type="submit" class="self-start bg-primary-600 text-white py-2 px-6 rounded-lg hover:bg-primary-700 transition duration-300 font-medium shadow-md hover:shadow-lg">
                <i class="fa-solid fa-check mr-1"></i> Save Note
            </button>
        </form>

        <h4 class="text-lg font-semibold text-gray-700 mt-6 mb-3 border-t pt-3"><i class="fa-solid fa-book-open-reader mr-1"></i> Your Journal Notes</h4>
        
        <?php if (!empty($user_notes)): ?>
            <div class="space-y-4">
                <?php foreach ($user_notes as $note): ?>
                    <div class="p-4 border border-gray-200 rounded-lg bg-gray-50 text-gray-800 shadow-sm hover:border-primary-300 transition duration-300">
                        <p class="text-base italic mb-2 leading-relaxed whitespace-pre-wrap font-medium">"<?= nl2br(htmlspecialchars($note['note_content'])) ?>"</p>
                        <div class="flex flex-wrap justify-between items-center border-t pt-2 mt-2">
                            <p class="text-xs text-gray-500 mb-1 sm:mb-0">Saved on: <?= date('M j, Y H:i A', strtotime($note['created_at'])) ?></p>
                            <div class="flex space-x-2">
                                <a href="edit_note.php?note_id=<?= $note['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium transition duration-150 p-1 rounded-md">
                                    <i class="fa-solid fa-edit mr-1"></i> Edit
                                </a>
                                
                                <!-- DELETE FORM -->
                                <form method="POST" action="dashboard.php" onsubmit="return confirm('Are you sure you want to delete this note? This action cannot be undone.');">
                                    <input type="hidden" name="note_action" value="delete_note">
                                    <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium transition duration-150 p-1 rounded-md">
                                        <i class="fa-solid fa-trash-alt mr-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-gray-500 p-6 border border-dashed rounded-lg">📝 No journal entries found. Start a note above!</p>
        <?php endif; ?>
    </div>
</div>

</main>


<script>
// ===================
// CHART.JS CONFIGURATION (Optimized for Responsiveness)
// ===================
Chart.register(ChartDataLabels);

// Utility function to generate a refined color palette
function getColorPalette(count) {
  const refinedPalette = [
        '#4F46E5', // Primary Blue (Indigo)
        '#F59E0B', // Accent Gold (Amber)
        '#EC4899', // Vibrant Pink
        '#EF4444', // Error Red
        '#3B82F6', // Trust Blue
        '#10B981', // Success Green
        '#9333EA', // Deep Purple
        '#0891B2', // Teal/Cyan
        '#D946EF', // Magenta
        '#6B7280', // Neutral Gray
    ];
    let palette = [];
    for (let i = 0; i < count; i++) {
        palette.push(refinedPalette[i % refinedPalette.length]);
    }
    return palette;
}

// Global Chart Options for Doughnut (for Chart 2)
const commonDoughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '70%', 
    plugins: {
        legend: {
            position: 'right',
            labels: {
                boxWidth: 15,
                padding: 18,
                font: { size: 13, family: 'Poppins' }
            }
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    let label = context.label || '';
                    if (label) { label += ': '; }
                    let value = context.parsed;
                    let sum = context.dataset.data.reduce((a, b) => a + b, 0);
                    let percentage = ((value * 100) / sum).toFixed(1) + '%';
                    return label + '₹' + value.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' (' + percentage + ')';
                }
            }
        },
        datalabels: {
            color: '#fff',
            font: { weight: 'bold', size: 12, family: 'Poppins' },
            formatter: (value, context) => {
                let sum = context.dataset.data.reduce((a, b) => a + b, 0);
                let percentage = Math.round((value * 100) / sum);
                if (percentage < 5) return ''; 
                return percentage + '%';
            }
        }
    }
};

// --- Chart 1: Income Allocation (Expense vs Remaining Balance) - STACKED BAR CHART ---
const ctx1 = document.getElementById('financeChart1')?.getContext('2d');
const totalIncome = <?= $total_income ?>;
const totalExpense = <?= $total_expense ?>;
const remainingBalance = <?= $balance ?>;

if (totalIncome > 0 && ctx1) {
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: ['Total Allocation'], 
            datasets: [
                {
                    label: 'Total Expense',
                    data: [totalExpense],
                    backgroundColor: 'rgba(255, 0, 0, 0.85)', 
                    hoverBackgroundColor: '#DC2626',
                    borderRadius: 5,
                    borderColor: '#000000', 
                    borderWidth: 1,         
                },
                {
                    label: 'Remaining Balance',
                    data: [remainingBalance],
                    backgroundColor: 'rgba(0, 255, 174, 0.85)', 
                    hoverBackgroundColor: '#059669',
                    borderRadius: 5,
                    borderColor: '#000000', 
                    borderWidth: 1,         
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y', // Horizontal Bar Chart
            scales: {
                x: {
                    stacked: true,
                    title: { display: true, text: 'Amount (₹)', font: { size: 14 } },
                    grid: { display: true, drawBorder: false, color: 'rgba(0, 0, 0, 0.05)' },
                    min: 0, 
                    max: totalIncome, 
                },
                y: {
                    stacked: true,
                    grid: { display: false, drawBorder: false },
                    barThickness: 'flex', 
                    maxBarThickness: 70,
                }
            },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { 
                    callbacks: {
                        label: (context) => {
                            let value = context.parsed.x;
                            return context.dataset.label + ': ₹' + value.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    }
                },
                title: {
                    display: true,
                    text: `Total Income: ₹<?= number_format($total_income, 2) ?>`,
                    font: { size: 16, weight: 'bold' },
                    padding: 10
                },
                datalabels: {
                    anchor: 'center',
                    align: 'center',
                    color: '#fff',
                    fontsize: 13,
                    textStrokeColor: '#000000', 
                    textStrokeWidth: 4,         
                    font: { weight: 'bold', size: 16 },
                    formatter: (value) => value > 0 ? '₹' + value.toLocaleString('en-IN', { maximumFractionDigits: 0 }) : '',
                }
            }
        }
    });
} else if (document.getElementById('financeChart1')) {
    document.getElementById('financeChart1').parentElement.innerHTML = '<div class="h-full w-full flex flex-col items-center justify-center p-8"><i class="fa-solid fa-face-sad-cry text-4xl text-gray-300 mb-3"></i><p class="text-center text-gray-500 border border-dashed rounded-lg p-4">📊 No income data yet to allocate. Please add your first Income transaction!</p></div>';
}

// --- Chart 2: Category-wise Expenses - DOUGHNUT CHART ---
const categoriesData = [
    <?php foreach($categories as $c){ echo "{ label: '".htmlspecialchars($c['category'])."', total: ".$c['total']." },"; } ?>
];
const categoryLabels = categoriesData.map(c => c.label);
const categoryTotals = categoriesData.map(c => c.total);
const categoryColors = getColorPalette(categoryLabels.length);

if (categoryTotals.length > 0) {
    const ctx2 = document.getElementById('financeChart2').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryTotals,
                backgroundColor: categoryColors,
                borderWidth: 2,
                borderColor: '#FFFFFF', 
                hoverOffset: 15,
            }]
        },
        options: commonDoughnutOptions
    });
} else if (document.getElementById('financeChart2')) {
    document.getElementById('financeChart2').parentElement.innerHTML = '<div class="h-full w-full flex flex-col items-center justify-center p-8"><i class="fa-solid fa-mug-hot text-4xl text-gray-300 mb-3"></i><p class="text-center text-gray-500 border border-dashed rounded-lg p-4">📊 No expense categories to display yet. Time to start tracking your spending!</p></div>';
}


// =======================================================
// CURRENCY CONVERTER DYNAMIC (API FETCH PATTERN) LOGIC
// =======================================================

const ALL_CURRENCIES = <?= $all_currencies_json ?>;
const RATES_TO_USD = <?= $rates_to_usd_json ?>;

// --- DYNAMIC MOCK RATE CALCULATOR ---
const fetchExchangeRate = (from, to) => {
    return new Promise((resolve, reject) => {
        setTimeout(() => {
            const rateFrom = RATES_TO_USD[from];
            const rateTo = RATES_TO_USD[to];
            
            if (rateFrom === undefined || rateTo === undefined) {
                reject(new Error("Selected currency code is missing from the mock rates list."));
                return;
            }
            
            if (from === to) {
                resolve(1);
            } else if (rateTo !== 0) {
                // Rate for FROM to TO = Rate(FROM to USD) / Rate(TO to USD) 
                const calculatedRate = rateFrom / rateTo; 
                resolve({ success: true, base: from, rates: { [to]: calculatedRate } });
            } else {
                reject(new Error("Cannot calculate rate (Target rate is zero)."));
            }
        }, 500); // Simulated 500ms network delay
    });
};

const getCurrencySign = (currency) => {
    return ALL_CURRENCIES[currency] || '';
};

const convertCurrency = async () => {
    const fromSelect = document.getElementById('fromCurrency');
    const toSelect = document.getElementById('toCurrency');
    const amountInput = document.getElementById('amount');
    const convertBtn = document.getElementById('convertBtn');
    const resultDisplay = document.getElementById('convertedResult');
    
    let from = fromSelect.value;
    let to = toSelect.value;
    let amt = parseFloat(amountInput.value);
    
    convertBtn.disabled = true;

    if (isNaN(amt) || amt <= 0) {
        resultDisplay.innerHTML = "Please enter a <span class='text-red-600 font-extrabold'>valid amount.</span>";
        convertBtn.disabled = false;
        return;
    }
    
    // Display loading state
    resultDisplay.innerHTML = `
        <i class="fa-solid fa-spinner fa-spin text-lg text-primary-500 mr-2"></i> 
        <span class='text-sm font-medium text-gray-600'>Fetching real-time rates...</span>
    `;

    try {
        const apiResponse = await fetchExchangeRate(from, to);
        
        let rate;
        if (typeof apiResponse === 'number') {
            rate = apiResponse;
        } else if (apiResponse.success && apiResponse.rates[to]) {
            rate = apiResponse.rates[to];
        } else {
            throw new Error("Failed to extract rate from simulated API response.");
        }

        let result = (amt * rate).toFixed(2);
        let resultSign = getCurrencySign(to);

        resultDisplay.innerHTML = `
            <span class="text-lg sm:text-xl font-extrabold text-blue-500">${amt.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${from}</span> 
            <i class="fa-solid fa-arrow-right text-base sm:text-lg text-primary-500 mx-2"></i> 
            <span class="text-lg sm:text-xl font-extrabold text-primary-600">${resultSign}${parseFloat(result).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${to}</span>
        `;
    } catch (error) {
        console.error("Conversion Error:", error);
        resultDisplay.innerHTML = `<span class='text-red-600 font-extrabold text-sm sm:text-base'>Error:</span> Failed to get real-time rates.`;
    } finally {
        convertBtn.disabled = false;
    }
};

document.getElementById('convertBtn').addEventListener('click', convertCurrency);
document.getElementById('amount').addEventListener('input', convertCurrency);
document.getElementById('fromCurrency').addEventListener('change', convertCurrency);
document.getElementById('toCurrency').addEventListener('change', convertCurrency);

// Swap button functionality
document.getElementById('swapBtn').addEventListener('click', () => {
    const fromSelect = document.getElementById('fromCurrency');
    const toSelect = document.getElementById('toCurrency');
    const temp = fromSelect.value;
    fromSelect.value = toSelect.value;
    toSelect.value = temp;
    convertCurrency(); // Re-calculate after swap
});

// Initial calculation on load if amount field has a value
if (document.getElementById('amount').value) {
    convertCurrency();
}


// =======================================================
// RESPONSIVENESS AND SCROLL ANIMATION LOGIC
// =======================================================

// --- Sidebar Toggle Logic ---
const menuBtn = document.getElementById('menu-btn');
const sidebar = document.getElementById('sidebar');

if (menuBtn && sidebar) {
    menuBtn.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        document.body.classList.toggle('overflow-hidden'); 
    });

    document.addEventListener('click', (e) => {
        if (window.innerWidth < 1024 && sidebar.classList.contains('open') && !sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
            sidebar.classList.remove('open');
            document.body.classList.remove('overflow-hidden');
        }
    });
}

// --- Scroll Animation Implementation (Intersection Observer) ---
const setupScrollAnimations = () => {
    // Set up the observer options
    const options = {
        root: null, // viewport
        rootMargin: '0px 0px -10% 0px', // Trigger when element enters the bottom 90% of the viewport
        threshold: 0.05 // Trigger when 5% of the item is visible
    };

    // Create a new Intersection Observer
    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Add the animation class when intersecting
                entry.target.classList.add('animated');
                // Stop observing the element after it has animated once
                observer.unobserve(entry.target);
            }
        });
    }, options);

    // Attach the observer to all elements with the custom attribute
    document.querySelectorAll('[data-scroll-animate]').forEach(element => {
        // Ensure the base class is applied (initial opacity/transform)
        element.classList.add('scroll-animate');
        observer.observe(element);
    });
};

// We run this after the page has fully loaded
window.addEventListener('load', setupScrollAnimations);
</script>

</body>
</html>

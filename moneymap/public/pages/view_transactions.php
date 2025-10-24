<?php
// ===================================
// view_transactions.php (Corrected and Enhanced)
// ===================================

// --- SESSION CHECK ---
session_start();
if (!isset($_SESSION['user_id'])) {
    // Corrected path assumed to be one level up from pages/
    header('Location: ../login.php'); 
    exit();
}
$user_id = $_SESSION['user_id'];

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
            die("Connection failed. Please check the logs. 😟"); 
        }
    }
    return $pdo;
}
$pdo = db_connect();

// --- CURRENCY FORMATTER ---
function format_currency($amount, $sign = '₹') {
    return $sign . number_format($amount, 2);
}

// --- MESSAGE HANDLING ---
$message = [];
if (isset($_SESSION['transaction_message'])) {
    $message = $_SESSION['transaction_message'];
    unset($_SESSION['transaction_message']);
}


// --- TRANSACTION DELETION LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_txn') {
    if (isset($_POST['txn_id'])) {
        $txn_id = (int)$_POST['txn_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM transactions WHERE id=? AND user_id=?");
            $stmt->execute([$txn_id, $user_id]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['transaction_message'] = ['type' => 'success', 'text' => 'Transaction deleted successfully!'];
            } else {
                $_SESSION['transaction_message'] = ['type' => 'error', 'text' => 'Transaction not found or you do not have permission to delete it.'];
            }
        } catch (PDOException $e) {
            error_log("Transaction deletion failed: " . $e->getMessage());
            $_SESSION['transaction_message'] = ['type' => 'error', 'text' => 'Error deleting transaction.'];
        }
    } else {
         $_SESSION['transaction_message'] = ['type' => 'error', 'text' => 'Invalid transaction ID.'];
    }
    
    // Post/Redirect/Get pattern
    header('Location: view_transactions.php');
    exit();
}


// --- FETCH ALL TRANSACTIONS ---
$stmt = $pdo->prepare("SELECT id, COALESCE(date, created_at) AS txn_date, category, description, amount, type FROM transactions WHERE user_id=? ORDER BY txn_date DESC");
$stmt->execute([$user_id]);
$all_transactions = $stmt->fetchAll();

// Get the user's first name for a friendly greeting
$stmt = $pdo->prepare("SELECT COALESCE(fullname, 'User') as fullname FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_name = htmlspecialchars($user['fullname'] ?? 'MoneyMapper User');
$first_name = htmlspecialchars(explode(' ', $user_name)[0]);


// --- HTML START ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Transactions | MoneyMap</title>
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
                // Primary color definition based on provided script
                primary:{50:'#ECFDF5',100:'#D1FAE5',200:'#A7F3D0',300:'#6EE7B7',400:'#34D399',500:'#10B981',600:'#059669',700:'#047857',800:'#065F46',900:'#054E3B'},
                'red': { 50: '#FEE2E2', 100: '#FECDCD', 600: '#DC2626' },
            },
            // Removed unnecessary keyframes/animations for transaction view
        }
    }
}
</script>
<style>
/* Global & Card Styles */
body{font-family:'Poppins',sans-serif; background:#f4f7f9; overflow-x: hidden;} 

/* Sidebar Styles */
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
.nav-item:hover { background-color: theme('colors.primary.100'); }
/* Active link styling matches the provided snippet's logic */
.nav-item.active { 
    background: linear-gradient(90deg, theme('colors.primary.600') 0%, theme('colors.primary.500') 100%); 
    box-shadow: 0 4px 10px rgba(5, 150, 105, 0.4); 
    color: white !important; /* Ensure text remains white */
}
.nav-item.active .fa-solid {
    color: white; /* Ensure icon is white in active state */
}

/* Responsive Overrides (Crucial for Mobile) */
/* The overall layout is now flex-based for Laptops/Desktops and hidden sidebar for Mobile */
@media (max-width: 1023px) {
    .sidebar { 
        position: fixed; 
        height: 100vh; 
        transform: translateX(-100%); /* Start off-screen */
    }
    .sidebar.open { 
        transform: translateX(0); /* Move on-screen */
    }
    .main-wrapper { 
        margin-left: 0 !important; 
        padding-top: 6rem; /* Add padding for fixed mobile header if one was present */
    }
}
@media (min-width: 1024px) {
    .main-wrapper { 
        margin-left: 280px; 
        transition: margin-left 0.3s ease; 
    }
}
</style>
</head>
<body class="min-h-screen">

<div id="sidebar-overlay" class="fixed inset-0 bg-black opacity-50 z-20 hidden lg:hidden"></div>

<aside id="sidebar" class="sidebar fixed top-0 left-0 bg-white lg:block h-full border-r border-gray-200 p-6">
    <div class="flex flex-col h-full">
        <a href="../dashboard.php" class="inline-flex items-center text-3xl font-extrabold text-gray-900 p-2 mb-10">
            <i class="fa-solid fa-map-location-dot text-primary-600 mr-2"></i>Money<span class="text-gray-900">Map</span>
        </a>
        
        <button id="close-sidebar" class="absolute top-6 right-4 text-gray-700 hover:text-red-600 lg:hidden">
            <i class="fa-solid fa-times text-2xl"></i>
        </button>

        <nav class="flex-grow space-y-2">
            <a href="../dashboard.php" class="nav-item flex items-center space-x-4 text-gray-700 hover:text-primary-800">
                <i class="fa-solid fa-house w-6 text-xl text-primary-600"></i>
                <span class="text-lg">Dashboard</span>
            </a>
            <a href="../pages/manage_goals.php" class="nav-item flex items-center space-x-4 text-gray-700 hover:text-primary-800">
                <i class="fa-solid fa-piggy-bank w-6 text-xl text-primary-600"></i>
                <span class="text-lg">Savings Goals</span>
            </a>
            <a href="../pages/add-transaction.php" class="nav-item flex items-center space-x-4 text-gray-700 hover:text-primary-800">
                <i class="fa-solid fa-plus-circle w-6 text-xl text-primary-600"></i>
                <span class="text-lg">New Transaction</span>
            </a>
            <a href="reports.php" class="nav-item flex items-center space-x-4 text-gray-700 hover:text-primary-800">
                <i class="fa-solid fa-chart-column w-6 text-xl text-primary-600"></i>
                <span class="text-lg">Analytics & Reports</span>
            </a>
        </nav>

        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50 mt-auto hover:bg-white transition duration-300">
            <div class="flex items-center justify-between space-x-3">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center text-white font-semibold text-lg">
                        <?= strtoupper(substr($first_name,0,1)) ?>
                    </div>
                   <a href="profile.php" class="flex-shrink-0 group block">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate"><?= $user_name ?></p>
                            <p class="text-xs text-primary-700 font-medium truncate">Your Profile</p>
                        </div>
                    </a>
                </div>
                <a href="../logout.php" class="p-2 text-gray-500 hover:text-red-600 transition-colors" title="Logout">
                    <i class="fa-solid fa-right-from-bracket text-lg"></i>
                </a>
            </div>
        </div>
    </div>
</aside>

<div class="main-wrapper min-h-screen p-4 lg:p-10">

    <main class="flex-grow">
    
        <header class="flex justify-between items-center mb-6 lg:hidden">
            <h1 class="text-xl font-bold text-gray-800">All Transactions</h1>
            <button id="open-sidebar" class="text-primary-600 hover:text-primary-700 p-2 rounded">
                <i class="fa-solid fa-bars text-2xl"></i>
            </button>
        </header>

        <div class="flex justify-between items-center mb-8 hidden lg:flex">
            <h1 class="text-3xl font-bold text-gray-800">All Transactions 📚</h1>
            <a href="../dashboard.php" class="text-primary-600 hover:text-primary-700 font-medium">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>
        
        <?php if (!empty($message)): ?>
        <div class="p-4 mb-6 rounded-lg 
            <?= $message['type'] === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700' ?>" 
            role="alert">
            <p class="font-medium"><?= htmlspecialchars($message['text']) ?></p>
        </div>
        <?php endif; ?>

        <?php if (count($all_transactions) > 0): ?>
        <div class="overflow-x-auto rounded-xl shadow-lg border border-gray-200 bg-white">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase tracking-wider">
                        <th class="py-3 px-4 text-left rounded-tl-xl">ID</th>
                        <th class="py-3 px-4 text-left">Date</th>
                        <th class="py-3 px-4 text-left">Category</th>
                        <th class="py-3 px-4 text-left hidden sm:table-cell">Description</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                        <th class="py-3 px-4 text-center">Type</th>
                        <th class="py-3 px-4 text-center rounded-tr-xl">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($all_transactions as $txn): ?>
                    <tr class="border-t border-gray-100 hover:bg-primary-50 transition duration-150">
                        <td class="py-3 px-4 text-gray-500"><?= $txn['id'] ?></td>
                        <td class="py-3 px-4"><?= date('M j, Y', strtotime($txn['txn_date'])) ?></td>
                        <td class="py-3 px-4 font-medium"><?= htmlspecialchars($txn['category']) ?></td>
                        <td class="py-3 px-4 text-gray-500 hidden sm:table-cell truncate max-w-xs"><?= htmlspecialchars($txn['description']) ?></td>
                        <td class="py-3 px-4 text-right font-semibold"><?= format_currency($txn['amount']) ?></td>
                        <td class="py-3 px-4 text-center <?= $txn['type']=='income'?'text-primary-600':'text-red-600' ?> font-medium">
                            <?= ucfirst($txn['type']) ?>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <form method="POST" action="view_transactions.php" 
                                  onsubmit="return confirm('Are you sure you want to delete this transaction (ID: <?= $txn['id'] ?>)? This action cannot be undone.');"
                                  class="inline-block">
                                <input type="hidden" name="action" value="delete_txn">
                                <input type="hidden" name="txn_id" value="<?= $txn['id'] ?>">
                                <button type="submit" 
                                        class="text-red-500 hover:text-red-700 transition duration-150 p-1 rounded-full hover:bg-red-100" 
                                        title="Delete Transaction">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center text-gray-600 p-12 border border-dashed border-gray-300 rounded-xl bg-white shadow-sm">
            <i class="fa-solid fa-circle-info text-4xl text-primary-400 mb-4"></i>
            <p class="text-lg font-semibold">It looks like you haven't recorded any transactions yet, <?= $first_name ?>!</p>
            <p class="text-gray-500 mt-2">Go back to the dashboard and click 'Add New' to start mapping your money.</p>
            <a href="../dashboard.php" class="mt-4 inline-block bg-primary-600 text-white py-2 px-4 rounded-lg hover:bg-primary-700 transition duration-300 font-medium shadow-md">
                <i class="fa-solid fa-plus mr-1"></i> Add New Transaction
            </a>
        </div>
        <?php endif; ?>
    </main>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const openBtn = document.getElementById('open-sidebar');
    const closeBtn = document.getElementById('close-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const body = document.body; // Reference to the body element

    function toggleSidebar(show) {
        if (show) {
            sidebar.classList.add('open');
            overlay.classList.remove('hidden');
            body.style.overflow = 'hidden'; // Prevent scrolling background
        } else {
            sidebar.classList.remove('open');
            overlay.classList.add('hidden');
            body.style.overflow = ''; // Restore scrolling
        }
    }

    // Event Listeners for mobile toggle
    if (openBtn) {
        openBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleSidebar(true);
        });
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', () => toggleSidebar(false));
    }
    if (overlay) {
        overlay.addEventListener('click', () => toggleSidebar(false));
    }
    
    // Auto-close sidebar if screen size is resized above mobile threshold
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            // Restore default scroll behavior and ensure sidebar is visible on desktop
            body.style.overflow = '';
            sidebar.classList.remove('open');
            overlay.classList.add('hidden');
        }
    });
</script>
</body>
</html>
<?php
// ===================================
// add-transaction.php (Transaction Input Form)
// ===================================

// --- SESSION CHECK ---
session_start();
if (!isset($_SESSION['user_id'])) {
    // Adjusted path: Redirects up one directory to the root for login.php
    header('Location: ../login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// --- DATABASE CONFIG & CONNECTION (Copied from dashboard.php for consistency) ---
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
        try { $pdo = new PDO($dsn, DB_USER, DB_PASS, $options); }
        catch(PDOException $e){ 
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. 😟"); 
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

// --- Default Categories for the Select Field ---
$default_categories = [
    'Expense' => ['Groceries', 'Rent/Mortgage', 'Utilities', 'Transport', 'Entertainment', 'Dining Out', 'Shopping'],
    'Income' => ['Salary', 'Freelance', 'Investment', 'Gift', 'Other Income']
];

$message = '';

// --- TRANSACTION SUBMISSION HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitize and Validate Input
    $type = $_POST['type'] ?? '';
    $amount = filter_var($_POST['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = $_POST['date'] ?? date('Y-m-d');

    if (empty($type) || ($type !== 'income' && $type !== 'expense')) {
        $message = ['type' => 'error', 'text' => 'Invalid transaction type selected.'];
    } elseif ($amount === false || $amount <= 0) {
        $message = ['type' => 'error', 'text' => 'Amount must be a positive number.'];
    } elseif (empty($category)) {
        $message = ['type' => 'error', 'text' => 'Category cannot be empty.'];
    } else {
        try {
            // 2. Prepare SQL Statement for Secure Insertion
            $sql = "INSERT INTO transactions (user_id, type, amount, category, description, date) 
                     VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            
            // 3. Execute with Bound Parameters
            $stmt->execute([
                $user_id,
                $type,
                $amount,
                $category,
                $description,
                $date
            ]);

            $message = ['type' => 'success', 'text' => "Transaction added successfully! Redirecting to Dashboard... 🚀"];
            
            // Adjusted path: Redirects up one directory to the root for dashboard.php
            header('Refresh: 2; URL=../dashboard.php');

        } catch (PDOException $e) {
            error_log("Transaction insertion failed: " . $e->getMessage());
            $message = ['type' => 'error', 'text' => "Database error: Could not save transaction."];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Transaction | MoneyMap Pro</title>

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
                primary:{50:'#ECFDF5',100:'#D1FAE5',200:'#A7F3D0',300:'#6EE7B7',400:'#34D399',500:'#10B981',600:'#059669',700:'#047857',800:'#065F46',900:'#054E3B'},
                'red': { 50: '#FEE2E2', 100: '#FECDCD', 600: '#DC2626' },
                'green': { 50: '#D1FAE5', 100: '#A7F3D0', 600: '#059669' },
            },
            keyframes: {
                'shake': { '0%, 100%': { transform: 'translateX(0)' }, '10%, 30%, 50%, 70%, 90%': { transform: 'translateX(-5px)' }, '20%, 40%, 60%, 80%': { transform: 'translateX(5px)' } }
            },
            animation: {
                'shake': 'shake 0.5s ease-in-out',
            }
        }
    }
}
</script>
<style>
/* Global & Card Styles */
body{font-family:'Poppins',sans-serif; background:#f4f7f9; overflow-x: hidden;} 
.transaction-card{
    background:white;
    border-radius:1.25rem;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
    /* Added scroll-animate properties for consistency */
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.transaction-card.animated {
    opacity: 1;
    transform: translateY(0);
}
.input-field {
    @apply w-full p-3 border border-gray-300 rounded-lg shadow-inner transition duration-150 focus:border-primary-500 focus:ring-primary-500;
}

/* Sidebar Styles (Copied for consistency) */
.sidebar { width: 280px; transition: transform 0.3s ease-in-out, width 0.3s ease; box-shadow: 4px 0 15px rgba(0,0,0,0.08); z-index: 50; }
.nav-item { transition: all 0.2s ease; padding: 0.75rem 1.25rem; border-radius: 0.6rem; }
.nav-item:hover { background-color: theme('colors.primary.100'); }
.nav-item.active { box-shadow: 0 4px 10px rgba(5, 150, 105, 0.4); }

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
        padding-top: 6rem; /* MOBILE PADDING FIX: 6rem */
        margin-left: 0 !important; 
    }
}
@media (min-width: 1024px) {
    .main-content { 
        margin-left: 280px; 
        transition: margin-left 0.3s ease; 
    }
}
</style>
</head>
<body class="min-h-screen">

<!-- Sidebar (ADDED) -->
<aside id="sidebar" class="sidebar fixed top-0 left-0 bg-white lg:block h-full border-r border-gray-200 p-6">
    <div class="flex flex-col h-full">
        <a href="../dashboard.php" class="inline-flex items-center text-3xl font-extrabold text-gray-900 p-2 mb-10">
            <i class="fa-solid fa-map-location-dot text-primary-600 mr-2"></i>Money<span class="text-gray-900">Map</span>
        </a>

        <nav class="flex-grow space-y-2">
            <!-- Dashboard -->
            <a href="../dashboard.php" class="nav-item flex items-center space-x-4 text-gray-700 hover:text-primary-800">
                <i class="fa-solid fa-house w-6 text-xl"></i>
                <span class="text-lg">Dashboard</span>
            </a>
            <!-- Savings Goals -->
            <a href="../pages/manage_goals.php" class="nav-item flex items-center space-x-4 text-gray-700 hover:text-primary-800">
    <i class="fa-solid fa-piggy-bank w-6 text-xl"></i>
                <span class="text-lg">Savings Goals</span>
            </a>
            <!-- New Transaction (ACTIVE) -->
            <a href="add-transaction.php" class="nav-item active bg-primary-600 text-white flex items-center space-x-4 text-black" style="background: linear-gradient(90deg, theme('colors.primary.600') 0%, theme('colors.primary.500') 100%);">
                <i class="fa-solid fa-plus-circle w-6 text-xl"></i>
                <span class="text-lg">New Transaction</span>
            </a>
            <a href="../pages/reports.php" class="nav-item flex items-center space-x-4 text-gray-700 hover:text-primary-800">
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

<!-- Header (Mobile Menu - ADDED) -->
<header class="lg:hidden fixed top-0 left-0 right-0 bg-white border-b border-gray-200 shadow-md z-40">
    <div class="flex items-center justify-between p-4">
        <button id="menu-btn" class="text-gray-600 hover:text-primary-600 p-2" aria-label="Open menu">
            <i class="fa-solid fa-bars text-xl"></i>
        </button>
        <a href="../dashboard.php" class="text-xl font-bold text-gray-900">
            <i class="fa-solid fa-map-location-dot text-primary-600 mr-1"></i>Money<span class="text-gray-900">Map</span>
        </a>
        <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center text-white font-semibold text-sm animate-pulse-sm">
            <?= strtoupper(substr($first_name,0,1)) ?>
        </div>
    </div>
</header>


<main id="main-content" class="pt-[6rem] sm:pt-[6rem] md:pt-[8rem] main-content py-8 lg:py-12 px-4 lg:px-8 space-y-10">

    <div class="w-full max-w-xl mx-auto">
        <div class="transaction-card w-full p-8 md:p-10 animated">
            
            <div class="text-center mb-8">
                <a href="../dashboard.php" class="inline-flex items-center text-3xl font-extrabold text-gray-800">
                    <i class="fa-solid fa-map-location-dot text-primary-600 mr-2"></i>Money<span class="text-primary-600">Map</span>
                </a>
                <h2 class="text-2xl font-semibold text-gray-800 mt-4">Add New Transaction 💰</h2>
                <p class="text-gray-500">Record your latest income or expense details.</p>
            </div>

            <?php if (!empty($message)): ?>
                <div id="alert-message" class="p-4 rounded-lg text-sm mb-6 font-medium <?= $message['type'] === 'success' ? 'bg-green-50 text-green-600 border border-green-300' : 'bg-red-50 text-red-600 border border-red-300 animate-shake' ?>">
                    <i class="fa-solid <?= $message['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> mr-2"></i>
                    <?= htmlspecialchars($message['text']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type</label>
                    <div class="flex space-x-4">
                        <label class="flex-1">
                            <input type="radio" name="type" value="income" required class="hidden peer" onchange="updateCategories('Income')" checked>
                            <div class="p-4 border-2 border-primary-300 rounded-xl cursor-pointer peer-checked:bg-primary-500 peer-checked:text-white peer-checked:border-primary-700 transition duration-200 text-center font-semibold text-primary-700 hover:bg-primary-50">
                                <i class="fa-solid fa-arrow-up-circle mr-1"></i> Income
                            </div>
                        </label>
                        <label class="flex-1">
                            <input type="radio" name="type" value="expense" required class="hidden peer" onchange="updateCategories('Expense')">
                            <div class="p-4 border-2 border-red-300 rounded-xl cursor-pointer peer-checked:bg-red-600 peer-checked:text-white peer-checked:border-red-800 transition duration-200 text-center font-semibold text-red-600 hover:bg-red-50">
                                <i class="fa-solid fa-arrow-down-circle mr-1"></i> Expense
                            </div>
                        </label>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount (₹)</label>
                        <input type="number" id="amount" name="amount" step="0.01" min="0.01" required placeholder="1500.00" class="input-field">
                    </div>
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <input type="date" id="date" name="date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required class="input-field">
                    </div>
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select id="category" name="category" required class="input-field appearance-none">
                        <option value="" disabled selected>Select a Category</option>
                        </select>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description / Note</label>
                    <textarea id="description" name="description" rows="2" placeholder="Brief note about the transaction" class="input-field resize-none"></textarea>
                </div>

                <button type="submit" class="w-full bg-primary-600 text-white p-3 rounded-xl hover:bg-primary-700 transition duration-300 font-bold text-lg shadow-md hover:shadow-lg">
                    <i class="fa-solid fa-plus-circle mr-2"></i> Save Transaction
                </button>

            </form>
            
            <div class="mt-6 text-center text-sm">
                <a href="../dashboard.php" class="text-gray-500 hover:text-primary-600 transition duration-200">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</main>

<script>
    // --- Category Dropdown Logic ---
    const categoriesMap = {
        'Income': <?= json_encode($default_categories['Income']) ?>,
        'Expense': <?= json_encode($default_categories['Expense']) ?>
    };
    const categorySelect = document.getElementById('category');

    /**
     * Updates the category dropdown options based on the selected transaction type.
     * @param {string} type - 'Income' or 'Expense'
     */
    function updateCategories(type) {
        const categories = categoriesMap[type] || [];
        categorySelect.innerHTML = '<option value="" disabled selected>Select a Category</option>';

        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category;
            option.textContent = category;
            categorySelect.appendChild(option);
        });
    }

    // --- Initialization & Sidebar/Animation Logic ---
    const menuBtn = document.getElementById('menu-btn');
    const sidebar = document.getElementById('sidebar');
    const transactionCard = document.querySelector('.transaction-card');
    
    // Initialize the categories on page load (defaults to Income)
    document.addEventListener('DOMContentLoaded', () => {
        const checkedType = document.querySelector('input[name="type"]:checked');
        if (checkedType) {
            updateCategories(checkedType.value === 'income' ? 'Income' : 'Expense');
        } else {
            // Default to Income categories if none is checked
            updateCategories('Income'); 
        }

        // Apply scroll animation class immediately since the card is visible on load
        if (transactionCard) {
             // Use setTimeout to ensure the transition takes effect after initial rendering
            setTimeout(() => {
                transactionCard.classList.add('animated');
            }, 50); 
        }
    });

    // --- Sidebar Toggle Logic ---
    if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            // Prevent body scroll when sidebar is open on mobile
            document.body.classList.toggle('overflow-hidden'); 
        });

        document.addEventListener('click', (e) => {
            if (window.innerWidth < 1024 && sidebar.classList.contains('open') && !sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                sidebar.classList.remove('open');
                document.body.classList.remove('overflow-hidden');
            }
        });
    }
</script>

</body>
</html>

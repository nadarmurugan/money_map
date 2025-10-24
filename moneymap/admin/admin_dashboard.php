<?php
// ===================================
// admin_dashboard.php (MoneyMap Admin Panel - Tailwind Version - Responsive/FIXED)
// ===================================

// --- SESSION CHECK & ADMIN AUTH ---
session_start();

// Check if the specific admin session variable is set
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect unauthenticated users back to the login page
    header('Location: admin_login.php');
    exit();
}

// Hardcode admin name since login doesn't fetch from DB/user_id
$admin_name = "Admin User"; 

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

// --- ADMIN DASHBOARD DATA FETCHING (Actual Data) ---

// 1. Total Users Registered
$total_users = $pdo->query("SELECT COUNT(id) FROM users")->fetchColumn();

// 2. Total Transactions (Income vs Expenses) - PLATFORM WIDE
function fetch_total_platform($pdo, $type) {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type=?");
    $stmt->execute([$type]);
    return (float)$stmt->fetchColumn();
}
$total_platform_income = fetch_total_platform($pdo, 'income');
$total_platform_expense = fetch_total_platform($pdo, 'expense');
$total_transactions = $pdo->query("SELECT COUNT(id) FROM transactions")->fetchColumn();

// 3. Total Active Goals
$total_active_goals = $pdo->query("SELECT COUNT(id) FROM goals WHERE status='active'")->fetchColumn();

// 4. Chart Data
$chart_data = [
    'labels' => ['Total Platform Income', 'Total Platform Expenses'],
    'data' => [$total_platform_income, $total_platform_expense],
    'colors' => ['#10B981', '#EF4444'] // Primary (green) for income, Red for expense
];

// 5. User List for Management
$all_users = $pdo->query("SELECT id, fullname, email, created_at FROM users ORDER BY created_at DESC")->fetchAll();

// --- USER MANAGEMENT HANDLERS (Basic Delete Logic) ---

// Example of a DELETE user handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user' && isset($_POST['user_id'])) {
    $user_to_delete = (int)$_POST['user_id'];

    try {
        $pdo->beginTransaction();
        // Delete related records first (Assumes foreign key ON DELETE CASCADE is NOT set)
        $pdo->prepare("DELETE FROM transactions WHERE user_id=?")->execute([$user_to_delete]);
        $pdo->prepare("DELETE FROM goals WHERE user_id=?")->execute([$user_to_delete]);
        // Note: Assumes 'user_notes' table exists
        // $pdo->prepare("DELETE FROM user_notes WHERE user_id=?")->execute([$user_to_delete]);
        
        // Finally, delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
        $stmt->execute([$user_to_delete]);
        $pdo->commit();
        $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'User ID ' . $user_to_delete . ' and related data deleted successfully.'];
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("User deletion failed: " . $e->getMessage());
        $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Database error during user deletion.'];
    }
    header('Location: admin_dashboard.php');
    exit();
}


// Display session messages
$admin_message = [];
if (isset($_SESSION['admin_message'])) {
    $admin_message = $_SESSION['admin_message'];
    unset($_SESSION['admin_message']);
}


// --- CURRENCY FORMATTER ---
function format_currency($amount, $sign = '₹') {
    return $sign . number_format($amount, 2);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyMap Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"> 
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
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
        /* FIX: Use z-index higher than modal backdrop (50) to ensure the sidebar can be on top */
        .sidebar {
            background-color: #10B981; /* primary color */
            transition: transform 0.3s ease-in-out;
            /* Start position for mobile: off-screen */
            transform: translateX(-100%); 
        }

        /* FIX: Mobile menu logic - Toggles the transform property */
        #mobile-menu-toggle:checked ~ .flex > .sidebar {
            transform: translateX(0);
        }
        
        /* FIX: Backdrop logic - Controls the overlay and its pointer events */
        #mobile-menu-toggle:checked ~ .flex > .mobile-backdrop {
            opacity: 0.5;
            pointer-events: auto;
        }

        /* Desktop/Tablet view: always show sidebar and adjust main content margin */
        @media (min-width: 768px) {
            .sidebar {
                transform: translateX(0);
                position: static;
                /* Reset fixed properties for desktop flow */
                flex-shrink: 0; 
            }
        }
        
        .stat-card {
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* FIX: Modal Styling for Logout */
        .logout-modal-backdrop {
            opacity: 0;
            visibility: hidden; /* FIX: Hides the element entirely when closed */
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
        }
        #logout-modal-toggle:checked ~ .logout-modal-backdrop {
            opacity: 1;
            visibility: visible;
        }
        .logout-modal-content {
            transform: scale(0.9);
            transition: transform 0.3s ease-in-out;
        }
        #logout-modal-toggle:checked ~ .logout-modal-backdrop .logout-modal-content {
            transform: scale(1);
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">


<input type="checkbox" id="mobile-menu-toggle" class="hidden peer">

<input type="checkbox" id="logout-modal-toggle" class="hidden peer">


<div class="fixed inset-0 bg-black bg-opacity-50 z-50 logout-modal-backdrop">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="absolute inset-0" onclick="document.getElementById('logout-modal-toggle').checked = false;"></div>
        
        <div class="bg-white p-6 rounded-xl shadow-2xl w-full max-w-sm logout-modal-content z-50" onclick="event.stopPropagation();">
            <h3 class="text-xl font-bold text-gray-800 border-b pb-3 mb-4 flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i> Confirm Logout
            </h3>
            <p class="text-gray-600 mb-6">Are you sure you want to log out of the Admin Dashboard?</p>
            <div class="flex justify-end space-x-3">
                <label for="logout-modal-toggle"
                    class="py-2 px-4 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 cursor-pointer transition">
                    Cancel
                </label>
                <a href="admin_logout.php"
                    class="py-2 px-4 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition">
                    Yes, Logout
                </a>
            </div>
        </div>
    </div>
</div>
<div class="flex h-screen">
    
    <div class="sidebar w-64 p-5 flex flex-col text-white shadow-2xl z-40 fixed inset-y-0 left-0 md:relative md:flex-shrink-0">
        <label for="mobile-menu-toggle" class="md:hidden self-end text-3xl cursor-pointer hover:text-gray-200 mb-6">
            <i class="fas fa-times"></i>
        </label>
        
        <h2 class="text-3xl font-extrabold mb-8 text-center border-b border-green-700 pb-4">MoneyMap 👑</h2>
        <p class="text-center mb-6 text-sm text-green-100">Welcome, <?php echo htmlspecialchars($admin_name); ?></p>
        
        <nav class="flex-grow space-y-2">
            <label for="mobile-menu-toggle" class="block md:block">
                <a href="#overview" class="nav-link block py-3 px-4 rounded-xl bg-secondary font-semibold transition duration-200 hover:bg-green-700">
                    <i class="fas fa-chart-line mr-2"></i> Dashboard / Overview
                </a>
            </label>
            <label for="mobile-menu-toggle" class="block md:block">
                <a href="#user-management" class="nav-link block py-3 px-4 rounded-xl transition duration-200 hover:bg-secondary">
                    <i class="fas fa-users mr-2"></i> User Management
                </a>
            </label>
            <label for="mobile-menu-toggle" class="block md:block">
                <a href="#export-data" class="nav-link block py-3 px-4 rounded-xl transition duration-200 hover:bg-secondary">
                    <i class="fas fa-file-export mr-2"></i> Export Data
                </a>
            </label>
        </nav>

        <div class="mt-auto pt-4 border-t border-green-700 space-y-3">
            <a href="../public/index.php" 
               class="block py-2 px-3 rounded-lg text-sm text-gray-200 transition duration-150 ease-in-out hover:bg-green-700 hover:text-white group flex items-center"
            >
                <i class="fas fa-globe mr-2 text-green-400 group-hover:text-white transition duration-150"></i> 
                Go to User View
            </a>
            
            <label for="logout-modal-toggle" 
                class="block py-3 px-3 rounded-lg text-base text-white font-semibold transition duration-150 ease-in-out bg-red-600 hover:bg-red-700 mt-2 cursor-pointer text-center"
            >
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </label>
        </div>
    </div>
    <label for="mobile-menu-toggle" class="mobile-backdrop md:hidden fixed inset-0 bg-black opacity-0 transition-opacity z-30 pointer-events-none"></label>


    <div class="flex-1 overflow-y-auto content-area p-4 md:p-8">
        
        <div class="flex justify-between items-center mb-8 md:hidden">
            <h1 class="text-2xl font-bold text-gray-800">Admin Dashboard</h1>
            <label for="mobile-menu-toggle" class="text-gray-700 text-3xl cursor-pointer p-2">
                <i class="fas fa-bars"></i>
            </label>
        </div>
        <h1 class="hidden md:block text-3xl font-bold text-gray-800 mb-8 border-b pb-3">Admin Dashboard</h1>


        <?php if (!empty($admin_message)): ?>
            <div class="p-4 mb-6 text-sm rounded-xl border
                <?php echo ($admin_message['type'] === 'success') ? 'bg-green-50 text-green-800 border-green-200' : 'bg-red-50 text-red-800 border-red-200'; ?>" 
                role="alert">
                <i class="mr-2 fas <?php echo ($admin_message['type'] === 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?= htmlspecialchars($admin_message['text']); ?>
            </div>
        <?php endif; ?>


        <a id="overview" class="anchor-link pt-16 -mt-16 block"></a>
        
        <h2 class="text-2xl font-semibold text-gray-700 mb-5">Platform Overview</h2>
        
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-10">
            
            <div class="stat-card bg-white p-5 md:p-6 rounded-xl border-l-4 border-primary shadow-lg">
                <h5 class="text-xs md:text-sm font-medium text-gray-500 uppercase truncate">Total Users</h5>
                <div class="text-2xl md:text-4xl font-extrabold text-gray-900 mt-2"><?php echo number_format($total_users); ?></div>
            </div>

            <div class="stat-card bg-white p-5 md:p-6 rounded-xl border-l-4 border-primary shadow-lg">
                <h5 class="text-xs md:text-sm font-medium text-gray-500 uppercase truncate">Total Transactions</h5>
                <div class="text-2xl md:text-4xl font-extrabold text-gray-900 mt-2"><?php echo number_format($total_transactions); ?></div>
            </div>

            <div class="stat-card bg-white p-5 md:p-6 rounded-xl border-l-4 border-primary shadow-lg">
                <h5 class="text-xs md:text-sm font-medium text-gray-500 uppercase truncate">Active Goals</h5>
                <div class="text-2xl md:text-4xl font-extrabold text-gray-900 mt-2"><?php echo number_format($total_active_goals); ?></div>
            </div>

            <div class="stat-card bg-white p-5 md:p-6 rounded-xl border-l-4 border-primary shadow-lg">
                <h5 class="text-xs md:text-sm font-medium text-gray-500 uppercase truncate">Platform Net Balance</h5>
                <?php 
                $platform_balance = $total_platform_income - $total_platform_expense;
                $color_class = $platform_balance >= 0 ? 'text-green-600' : 'text-red-600';
                ?>
                <div class="text-2xl md:text-4xl font-extrabold mt-2 <?php echo $color_class; ?>"><?php echo format_currency($platform_balance); ?></div>
            </div>
        </div>

        <div class="bg-white p-4 md:p-6 rounded-xl shadow-lg mb-10 border border-gray-200">
            <h4 class="text-lg md:text-xl font-bold text-gray-800 mb-6 border-b pb-3">Platform-Wide Financial Summary</h4>
            <div class="h-80 w-full"> <canvas id="incomeExpenseChart"></canvas>
            </div>
        </div>
        
        
        <hr class="my-10 border-gray-300">

        <a id="user-management" class="anchor-link pt-16 -mt-16 block"></a>
        <h2 class="text-2xl font-semibold text-gray-700 mb-5">User Management</h2>
        
        <div class="flex justify-start md:justify-end mb-4">
            <a href="add_user.php" class="w-full md:w-auto text-center py-2.5 px-6 bg-primary hover:bg-secondary text-white font-medium rounded-xl transition duration-200 shadow-lg">
                <i class="fas fa-plus mr-1"></i> Add New User
            </a>
        </div>
        
        <div class="bg-white p-4 md:p-6 rounded-xl shadow-lg border border-gray-200 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">ID</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Full Name</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Email</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Registered On</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Role</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($all_users)): ?>
                        <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($all_users as $user): ?>
                            <tr>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900"><?php echo htmlspecialchars($user['id']); ?></td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($user['fullname'] ?? 'N/A'); ?></td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        User
                                    </span>
                                </td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" title="Edit User" class="text-indigo-600 hover:text-indigo-900 transition">
                                        <i class="fas fa-edit"></i> <span class="hidden lg:inline">Edit</span>
                                    </a>
                                    
                                    <form method="POST" action="admin_dashboard.php" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete user ID <?php echo $user['id']; ?>? This action cannot be undone.');">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" title="Delete User" class="text-red-600 hover:text-red-800 transition">
                                            <i class="fas fa-trash-alt"></i> <span class="hidden lg:inline">Delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>


        <hr class="my-10 border-gray-300">
        
        <a id="export-data" class="anchor-link pt-16 -mt-16 block"></a>
        <h2 class="text-2xl font-semibold text-gray-700 mb-5">Data Export</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
                <h5 class="text-xl font-bold text-gray-800 mb-3"><i class="fas fa-users mr-2 text-primary"></i> Export User Data</h5>
                <p class="text-sm text-gray-500 mb-5">Export a list of all users and their registration details.</p>
                <form method="POST" action="export.php" class="flex flex-col sm:flex-row sm:space-x-4 space-y-4 sm:space-y-0">
                    <input type="hidden" name="data_type" value="users">
                    <button type="submit" name="format" value="csv" class="flex-1 py-2.5 px-4 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-md">
                        Export CSV <i class="fas fa-download ml-1"></i>
                    </button>
                    <button type="button" class="flex-1 py-2.5 px-4 bg-gray-400 text-white font-medium rounded-lg cursor-not-allowed opacity-80" disabled>
                        Export PDF (Pro) 📄
                    </button>
                </form>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
                <h5 class="text-xl font-bold text-gray-800 mb-3"><i class="fas fa-exchange-alt mr-2 text-primary"></i> Export All Transactions</h5>
                <p class="text-sm text-gray-500 mb-5">Download all platform-wide income and expense records.</p>
                <form method="POST" action="export.php" class="flex flex-col sm:flex-row sm:space-x-4 space-y-4 sm:space-y-0">
                    <input type="hidden" name="data_type" value="transactions">
                    <button type="submit" name="format" value="csv" class="flex-1 py-2.5 px-4 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-md">
                        Export CSV <i class="fas fa-download ml-1"></i>
                    </button>
                    <button type="button" class="flex-1 py-2.5 px-4 bg-gray-400 text-white font-medium rounded-lg cursor-not-allowed opacity-80" disabled>
                        Export PDF (Pro) 📄
                    </button>
                </form>
            </div>
        </div>


    </div>
    </div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Chart.js Data and Configuration ---
    var chartData = <?php echo json_encode($chart_data); ?>;

    var ctx = document.getElementById('incomeExpenseChart').getContext('2d');
    var incomeExpenseChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Total Amount (Platform)',
                data: chartData.data,
                backgroundColor: [
                    'rgba(16, 185, 129, 0.9)', // Primary (Income) - Slightly less transparent
                    'rgba(239, 68, 68, 0.9)'  // Red (Expense) - Slightly less transparent
                ],
                borderColor: [
                    '#10B981', 
                    '#EF4444'
                ],
                borderWidth: 1.5 // Slightly thicker border
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Allows the container to define height
            layout: {
                padding: {
                    top: 10 // Add a little space at the top
                }
            },
            scales: {
                xAxes: [{
                    // Improved gridline styling
                    gridLines: {
                        display: false
                    }
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        // FIX: Currency formatting in ticks
                        callback: function(value, index, values) {
                            return '₹' + value.toLocaleString('en-IN', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                        }
                    },
                    // Improved gridline styling
                    gridLines: {
                        borderDash: [5, 5],
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }]
            },
            tooltips: {
                // Styling the tooltip
                mode: 'index',
                intersect: false,
                bodyFontFamily: 'Poppins',
                callbacks: {
                    label: function(tooltipItem, data) {
                        var label = data.labels[tooltipItem.index] || '';
                        // FIX: Currency formatting in tooltips
                        return label + ': ₹' + tooltipItem.yLabel.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                }
            },
            legend: {
                display: false
            },
            title: {
                display: false // Title is now in the H4 tag
            }
        }
    });

    // FIX: JavaScript to close the mobile menu when a navigation link is clicked
    document.querySelectorAll('.nav-link').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            if (mobileMenuToggle && mobileMenuToggle.checked) {
                // Delay hiding the menu slightly to allow the scroll to happen smoothly
                setTimeout(() => {
                    mobileMenuToggle.checked = false;
                }, 100);
            }
        });
    });
});
</script>
</body>
</html>
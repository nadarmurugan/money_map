<?php
// ===================================
// manage_goals.php (Goal Management Page - FUNCTIONAL)
// ===================================

// --- SESSION CHECK ---
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// --- DATABASE CONFIG & CONNECTION (same as dashboard.php) ---
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
        try { 
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options); 
        }
        catch(PDOException $e){ 
            error_log("Database connection failed: " . $e->getMessage());
            die("We're currently experiencing technical difficulties. 😟"); 
        }
    }
    return $pdo;
}
$pdo = db_connect();
$user_id = $_SESSION['user_id'];

// --- FETCH USER INFO ---
$stmt = $pdo->prepare("SELECT COALESCE(fullname, 'MoneyMapper User') as fullname FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_name = htmlspecialchars($user['fullname'] ?? 'MoneyMapper'); 
$first_name = htmlspecialchars(explode(' ', $user_name)[0]);

// --- GOAL CREATION LOGIC ---
$message = null;
$message_type = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['goal_name'])) {
    $goal_name = trim($_POST['goal_name']);
    $target_amount = floatval($_POST['target_amount']);
    $initial_contribution = floatval($_POST['initial_contribution']);
    $target_date = !empty($_POST['target_date']) ? $_POST['target_date'] : null;
    $start_date = date('Y-m-d'); 

    if ($target_amount <= 0 || $initial_contribution < 0 || $initial_contribution > $target_amount) {
        $message = "Invalid amounts provided. Target must be positive, and initial contribution must be non-negative and less than or equal to the target.";
        $message_type = 'error';
    } else {
        try {
            // Check if goal is already achieved by initial contribution
            $status = ($initial_contribution >= $target_amount) ? 'achieved' : 'active';

            $sql = "INSERT INTO goals (user_id, goal_name, target_amount, saved_amount, start_date, target_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $goal_name, $target_amount, $initial_contribution, $start_date, $target_date, $status]);

            $_SESSION['message'] = "Goal '{$goal_name}' created successfully, and your initial contribution has been recorded!";
            $_SESSION['message_type'] = 'success';
            header('Location: manage_goals.php');
            exit();

        } catch (PDOException $e) {
            $message = "Error creating goal: " . $e->getMessage();
            $message_type = 'error';
            error_log("Goal creation error: " . $e->getMessage());
        }
    }
}

// --- MESSAGE DISPLAY (AFTER REDIRECT) ---
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// --- REAL GOAL DATA FETCH (UPDATED TO FETCH STATUS) ---
try {
    // Fetch all active and achieved goals for the user, order achieved goals first.
    $stmt = $pdo->prepare("SELECT id, goal_name, target_amount, saved_amount, target_date, status FROM goals WHERE user_id=? AND status IN ('active', 'achieved') ORDER BY status DESC, start_date DESC");
    $stmt->execute([$user_id]);
    $goals_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $goals_list = [];
    $message = $message ?? "Could not load goals: " . $e->getMessage(); 
    $message_type = $message_type ?? 'error';
}

function format_currency($amount, $sign = '₹') {
    return $sign . number_format($amount, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Savings Goals | MoneyMap Pro 🎯</title>
<meta name="description" content="Manage and track your personal savings goals.">

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
                'red': { 50: '#FEF2F2', 100: '#FEE2E2', 600: '#DC2626', 800: '#991B1B' }, 
                'blue': { 50: '#EFF6FF', 500: '#3B82F6', 600: '#2563EB' }, 
                // Added Gold color for Achieved Goals
                'gold': { 50: '#FFFBEB', 500: '#F59E0B', 600: '#D97706' },
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

<style>
/* Global Styles */
body{font-family:'Poppins',sans-serif; background:#f4f7f9;} 
.dashboard-card{
    background:rgba(255,255,255,1);
    border-radius:1rem; 
    border:1px solid theme('colors.gray.200'); 
    box-shadow:0 8px 20px rgba(0,0,0,0.08); 
    transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

/* Sidebar Specifics (For Responsiveness) */
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
.nav-item.active { 
    box-shadow: 0 4px 10px rgba(5, 150, 105, 0.4); 
}

/* Styling for Achieved Goal Card (Golden/Yellow) */
.goal-achieved {
    background-color: theme('colors.gold.50');
    /* Ensure black text color for high contrast */
    color: theme('colors.gray.900'); 
}
.goal-achieved:hover {
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2);
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

<!-- Sidebar (UPDATED ACTIVE STATE) -->
<aside id="sidebar" class="sidebar fixed top-0 left-0 bg-white lg:block h-full border-r border-gray-200 p-6">
    <div class="flex flex-col h-full">
        <a href="dashboard.php" class="inline-flex items-center text-3xl font-extrabold text-gray-900 p-2 mb-10">
            <i class="fa-solid fa-map-location-dot text-primary-600 mr-2"></i>Money<span class="text-gray-900">Map</span>
        </a>

        <nav class="flex-grow space-y-2">
            <!-- Dashboard (DEACTIVATED) -->
            <a href="../dashboard.php" class="nav-item flex items-center space-x-4 text-gray-700 hover:text-primary-800">
                <i class="fa-solid fa-house w-6 text-xl"></i>
                <span class="text-lg">Dashboard</span>
            </a>
            <!-- Savings Goals (ACTIVATED) -->
            <a href="manage_goals.php" class="nav-item active bg-primary-600 text-white flex items-center space-x-4 text-black" style="background: linear-gradient(90deg, theme('colors.primary.600') 0%, theme('colors.primary.500') 100%);">
    <i class="fa-solid fa-piggy-bank w-6 text-xl"></i>
                <span class="text-lg">Savings Goals</span>
            </a>

            

            <a href="../pages/add-transaction.php" class="nav-item flex items-center space-x-4 text-gray-700 hover:text-primary-800">
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
                   <a href="pages/profile.php" class="flex-shrink-0 group block">
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

<!-- Header (reused from dashboard.php) -->
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

    <!-- Scroll Animated Content -->
    <h1 class="pt-[6rem] sm:pt-[6rem] md:pt-[2rem] text-3xl font-extrabold text-gray-900 mb-2 md:text-4xl scroll-animate" data-scroll-animate style="transition-delay: 0s;"><i class="fa-solid fa-bullseye-arrow text-primary-600 mr-2"></i> Savings Goal Manager</h1>
    <p class="text-lg text-gray-600 mb-8 sm:text-base scroll-animate" data-scroll-animate style="transition-delay: 0.1s;">Define your future financial milestones and track your progress.</p>

    <!-- STATUS MESSAGE DISPLAY (NEW) -->
    <?php if ($message): ?>
        <div class="p-4 rounded-lg <?= $message_type === 'success' ? 'bg-primary-100 text-primary-800 border border-primary-400' : 'bg-red-100 text-red-800 border border-red-400' ?> scroll-animate" 
            data-scroll-animate role="alert" style="transition-delay: 0.2s;">
            <p class="font-medium"><?= htmlspecialchars($message) ?></p>
        </div>
    <?php endif; ?>
    <!-- END STATUS MESSAGE DISPLAY -->

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- New Goal Creation Form -->
        <div class="dashboard-card p-6 lg:col-span-1 scroll-animate" data-scroll-animate style="transition-delay: 0.3s;">
            <h3 class="text-2xl font-semibold text-gray-800 mb-6 border-b pb-3">Create New Goal</h3>
            <form action="manage_goals.php" method="POST" class="space-y-4">
                <div>
                    <label for="goal_name" class="block text-sm font-medium text-gray-700 mb-1">Goal Name</label>
                    <input type="text" id="goal_name" name="goal_name" required placeholder="e.g., House Down Payment"
                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-primary-500 focus:border-primary-500 transition duration-150 shadow-sm">
                </div>
                <div>
                    <label for="target_amount" class="block text-sm font-medium text-gray-700 mb-1">Target Amount (₹)</label>
                    <input type="number" id="target_amount" name="target_amount" required min="1" step="0.01" placeholder="100000.00"
                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-primary-500 focus:border-primary-500 transition duration-150 shadow-sm">
                </div>
                <div>
                    <label for="initial_contribution" class="block text-sm font-medium text-gray-700 mb-1">Initial Contribution (₹)</label>
                    <input type="number" id="initial_contribution" name="initial_contribution" value="0.00" min="0" step="0.01"
                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-primary-500 focus:border-primary-500 transition duration-150 shadow-sm">
                </div>
                <div>
                    <label for="target_date" class="block text-sm font-medium text-gray-700 mb-1">Target Completion Date (Optional)</label>
                    <input type="date" id="target_date" name="target_date"
                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-primary-500 focus:border-primary-500 transition duration-150 shadow-sm">
                </div>
                <button type="submit" class="w-full bg-primary-600 text-white p-3 rounded-lg hover:bg-primary-700 transition duration-300 font-medium text-lg shadow-md hover:shadow-lg">
                    <i class="fa-solid fa-check-circle mr-2"></i> Save Goal
                </button>
            </form>
        </div>

        <!-- Goal List / Progress View -->
        <div class="dashboard-card p-6 lg:col-span-2 scroll-animate" data-scroll-animate style="transition-delay: 0.4s;">
            <h3 class="text-2xl font-semibold text-gray-800 mb-6 border-b pb-3">Existing Goals</h3>
            <div class="space-y-4">
                <?php if (!empty($goals_list)): ?>
                    <?php foreach($goals_list as $goal): 
                        // Use DB fields: target_amount and saved_amount
                        $progress_percent = ((float)$goal['target_amount'] > 0) ? min(100, round(((float)$goal['saved_amount'] / (float)$goal['target_amount']) * 100)) : 0;
                        
                        // Dynamic card classes based on status
                        $card_class = "p-4 border border-gray-200 rounded-lg hover:shadow-md transition duration-300";
                        $progress_bar_color = "bg-primary-500";
                        if ($goal['status'] === 'achieved') {
                            $card_class .= " goal-achieved border-2 border-gold-600";
                            $progress_bar_color = "bg-gold-500";
                        }
                    ?>
                    <div class="<?= $card_class ?>">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="text-xl font-semibold <?= $goal['status'] === 'achieved' ? 'text-gray-900' : 'text-gray-900' ?>">
                                <?= htmlspecialchars($goal['goal_name']) ?>
                                <?php if ($goal['status'] === 'achieved'): ?>
                                    <span class="text-xs font-bold bg-gold-600 text-black py-0.5 px-2 rounded-full ml-2">ACHIEVED <i class="fa-solid fa-trophy"></i></span>
                                <?php endif; ?>
                            </h4>
                            <div class="flex space-x-2">
                                <a href="edit_goal.php?id=<?= $goal['id'] ?>" title="Edit Goal" class="text-blue-500 hover:text-blue-600 transition"><i class="fa-solid fa-edit"></i></a>
                                <a href="delete_goal.php?id=<?= $goal['id'] ?>" title="Delete Goal" onclick="return confirm('Are you sure you want to delete this goal? This action cannot be undone.');" class="text-red-500 hover:text-red-600 transition"><i class="fa-solid fa-trash-alt"></i></a>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500">
                            Saved: <span class="font-bold text-primary-600"><?= format_currency($goal['saved_amount']) ?></span> 
                            of Target: <span class="font-bold text-gray-800"><?= format_currency($goal['target_amount']) ?></span>
                        </p>
                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-3 mt-3">
                            <div class="h-3 rounded-full <?= $progress_bar_color ?> transition-all duration-500 ease-out" style="width: <?= $progress_percent ?>%;"></div>
                        </div>
                        <div class="flex justify-between items-center mt-2 text-xs text-gray-500">
                            <span class="font-bold text-primary-700"><?= $progress_percent ?>% Complete</span>
                            <?php if ($goal['target_date']): ?>
                            <span>Target Date: <?= date('M j, Y', strtotime($goal['target_date'])) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-4 pt-3 border-t border-gray-100 flex justify-end">
                             <?php if ($goal['status'] === 'active'): ?>
                             <a href="make_contribution.php?id=<?= $goal['id'] ?>" class="bg-primary-500 text-white py-2 px-4 rounded-lg hover:bg-primary-600 transition duration-300 font-medium text-sm shadow-md">
                                 <i class="fa-solid fa-plus-square mr-1"></i> Make Contribution
                             </a>
                             <?php else: ?>
                             <span class="text-sm font-medium text-gold-600">Goal achieved! 🎉</span>
                             <?php endif; ?>
                         </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-gray-500 p-8 border border-dashed rounded-lg">No active goals found. Use the form to start saving!</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

</main>


<script>
// =======================================================
// RESPONSIVENESS AND SCROLL ANIMATION LOGIC
// =======================================================

// --- Sidebar Toggle Logic ---
const menuBtn = document.getElementById('menu-btn');
const sidebar = document.getElementById('sidebar');

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

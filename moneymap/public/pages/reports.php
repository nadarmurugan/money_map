<?php
// ===================================
// reports.php (Comprehensive Financial Reporting)
// ===================================

// --- SESSION CHECK ---
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
        try { $pdo = new PDO($dsn, DB_USER, DB_PASS, $options); }
        catch(PDOException $e){ error_log("Database connection failed: " . $e->getMessage()); die("Database connection failed. 😟"); }
    }
    return $pdo;
}
$pdo = db_connect();

// --- FETCH USER INFO (FIXED: ADDED MISSING BLOCK) ---
$stmt = $pdo->prepare("SELECT COALESCE(fullname, 'MoneyMapper User') as fullname FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_name = htmlspecialchars($user['fullname'] ?? 'MoneyMapper User'); 
$first_name = htmlspecialchars(explode(' ', $user_name)[0]);
// --- END FIX ---

// --- HELPER FUNCTIONS ---
function fetch_total($pdo, $user_id, $type){
    $stmt=$pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id=? AND type=?");
    $stmt->execute([$user_id,$type]);
    return (float)$stmt->fetchColumn();
}
function format_currency($amount, $sign = '₹') {
    return $sign . number_format($amount, 2);
}

// --- FETCH CORE METRICS ---
$total_income = fetch_total($pdo, $user_id, 'income');
$total_expense = fetch_total($pdo, $user_id, 'expense');
$net_balance = $total_income - $total_expense;
// Calculate Savings Rate (Total Income - Total Expense) / Total Income
$savings_rate = ($total_income > 0) ? round(($net_balance / $total_income) * 100) : 0;
$goal_savings = fetch_total($pdo, $user_id, 'contribution'); // Assuming 'contribution' is a type used for saving to goals

// --- MONTHLY TREND DATA (Last 6 Months) ---
$monthly_data = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(date, '%Y-%m') as month_year,
            type,
            SUM(amount) as total
        FROM transactions 
        WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY month_year, type
        ORDER BY month_year ASC
    ");
    $stmt->execute([$user_id]);
    $raw_data = $stmt->fetchAll();

    $structured_data = [];
    $all_month_years = [];

    // Generate last 6 months keys for consistent labeling
    for ($i = 5; $i >= 0; $i--) {
        $month_year = date('Y-m', strtotime("-$i month"));
        $month_label = date('M Y', strtotime("-$i month"));
        $all_month_years[$month_year] = $month_label;
    }

    // Populate data based on SQL results
    foreach ($raw_data as $row) {
        $month_year = $row['month_year'];
        if (!isset($structured_data[$month_year])) {
            $structured_data[$month_year] = ['income' => 0.0, 'expense' => 0.0];
        }
        $structured_data[$month_year][$row['type']] = (float)$row['total'];
    }

    // Final structure for Chart.js
    $chart_labels = [];
    $income_data = [];
    $expense_data = [];

    foreach ($all_month_years as $month_year => $label) {
        $chart_labels[] = $label;
        $data = $structured_data[$month_year] ?? ['income' => 0.0, 'expense' => 0.0];
        $income_data[] = $data['income'];
        $expense_data[] = $data['expense'];
    }

} catch (PDOException $e) {
    error_log("Report aggregation failed: " . $e->getMessage());
    $chart_labels = ['No Data'];
    $income_data = [0];
    $expense_data = [0];
}

// --- FINANCIAL HEALTH SUMMARY ---
function get_financial_summary($net_balance, $savings_rate) {
    if ($net_balance >= 0) {
        $text = "Excellent! You currently have a positive net balance in the tracked period. ";
        if ($savings_rate >= 20) {
            $text .= "Your savings rate of {$savings_rate}% shows strong financial discipline, aligning you well with future financial freedom. Keep this momentum!";
            $icon = "fa-chart-line text-primary-600";
        } elseif ($savings_rate > 0) {
            $text .= "Your positive savings rate ({$savings_rate}%) is a good starting point. Consider reviewing non-essential expenses to boost your savings toward 20% or more.";
            $icon = "fa-thumbs-up text-blue-500";
        } else {
            $text .= "While your overall balance is positive, your net flow is close to zero. Re-evaluate monthly habits to create a significant buffer.";
            $icon = "fa-lightbulb text-yellow-500";
        }
    } else {
        $text = "Critical Warning: You have a negative net balance. ";
        $text .= "This indicates your expenses have exceeded your income. Immediate action is required to review discretionary spending and prevent debt accumulation.";
        $icon = "fa-exclamation-triangle text-red-600";
    }
    return ['text' => $text, 'icon' => $icon];
}
$summary = get_financial_summary($net_balance, $savings_rate);


// --- JSON DATA EXPORT (NEW ENDPOINT FOR AJAX POLLING) ---
if (isset($_GET['fetch_data']) && $_GET['fetch_data'] === 'true') {
    header('Content-Type: application/json');
    echo json_encode([
        'total_income' => $total_income,
        'total_expense' => $total_expense,
        'net_balance' => $net_balance,
        'savings_rate' => $savings_rate,
        'summary_text' => $summary['text'],
        'summary_icon' => $summary['icon'],
        'chart_labels' => $chart_labels,
        'income_data' => $income_data,
        'expense_data' => $expense_data,
    ]);
    exit();
}
// --- END JSON EXPORT ---
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Financial Reports | MoneyMap Pro</title>

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
                'blue': { 50: '#EFF6FF', 500: '#3B82F6', 600: '#2563EB' },
                'yellow': { 500: '#EAB308' },
                'orange': { 500: '#F97316' }
            },
            keyframes: {
                'pulse-sm': { '0%, 100%': { opacity: 1 }, '50%': { opacity: .7 } },
                'spin-slow': { '0%': { transform: 'rotate(0deg)' }, '100%': { transform: 'rotate(360deg)' } } // Added for data loading
            },
            animation: {
                'pulse-sm': 'pulse-sm 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'spin-slow': 'spin-slow 2s linear infinite' // Added for data loading
            }
        }
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<style>
/* Global & Card Styles */
body{font-family:'Poppins',sans-serif; background:#f4f7f9; overflow-x: hidden;} 
.dashboard-card{
    background:white;
    border-radius:1rem; 
    border:1px solid theme('colors.gray.200'); 
    box-shadow:0 8px 20px rgba(0,0,0,0.08); 
    transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.stats-card{
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-left-width: 5px; 
}

/* Sidebar Styles (Copied for consistency) */
.sidebar { width: 280px; transition: transform 0.3s ease-in-out, width 0.3s ease; box-shadow: 4px 0 15px rgba(0,0,0,0.08); z-index: 50; }
.nav-item { transition: all 0.2s ease; padding: 0.75rem 1.25rem; border-radius: 0.6rem; }
.nav-item:hover { background-color: theme('colors.primary.100'); }
.nav-item.active { background: linear-gradient(90deg, theme('colors.primary.600') 0%, theme('colors.primary.500') 100%); box-shadow: 0 4px 10px rgba(5, 150, 105, 0.4); }

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

/* Custom Scroll Animation Styles */
.scroll-animate {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.scroll-animate.animated {
    opacity: 1;
    transform: translateY(0);
}
</style>
</head>
<body class="min-h-screen">

<!-- Sidebar -->
<aside id="sidebar" class="sidebar fixed top-0 left-0 bg-white lg:block h-full border-r border-gray-200 p-6">
    <div class="flex flex-col h-full">
        <a href="dashboard.php" class="inline-flex items-center text-3xl font-extrabold text-gray-900 p-2 mb-10">
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
            <!-- New Transaction -->
            <a href="../pages/add-transaction.php" class="nav-item flex items-center space-x-4 text-gray-700 hover:text-primary-800">
                <i class="fa-solid fa-plus-circle w-6 text-xl"></i>
                <span class="text-lg">New Transaction</span>
            </a>
            <!-- Analytics & Reports (ACTIVE) -->
            <a href="reports.php" class="nav-item active bg-primary-600 text-white flex items-center space-x-4 text-black">
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

<!-- Header (Mobile Menu) -->
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

    <h1 class="pt-[6rem] sm:pt-[6rem] md:pt-[2rem] text-3xl font-extrabold text-gray-900 mb-2 md:text-4xl scroll-animate" data-scroll-animate style="transition-delay: 0s;"><i class="fa-solid fa-chart-pie text-primary-600 mr-2"></i> Financial Performance Report</h1>
    <p class="text-lg text-gray-600 mb-8 sm:text-base scroll-animate" data-scroll-animate style="transition-delay: 0.1s;">A detailed analysis of your earning and spending habits over time.</p>
    
    <!-- Analysis Summary & Report Download -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Core Metric Cards -->
        <div class="dashboard-card p-6 lg:col-span-2 scroll-animate" id="metrics-card" data-scroll-animate style="transition-delay: 0.2s;">
            <div class="flex justify-between items-center mb-5 border-b pb-3">
                 <h3 class="text-xl font-semibold text-gray-800"><i class="fa-solid fa-wallet mr-2 text-blue-600"></i> Lifetime Financial Overview</h3>
                 <!-- New Spinner for Loading State -->
                 <i id="loading-spinner" class="fa-solid fa-circle-notch text-primary-500 animate-spin-slow text-lg hidden" title="Updating data..."></i>
            </div>
           
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-3 rounded-lg border border-primary-100 bg-primary-50">
                    <p class="text-sm text-gray-600">Total Income</p>
                    <p class="text-xl font-bold text-primary-600 mt-1" id="metric-income"><?= format_currency($total_income) ?></p>
                </div>
                <div class="text-center p-3 rounded-lg border border-red-100 bg-red-50">
                    <p class="text-sm text-gray-600">Total Expense</p>
                    <p class="text-xl font-bold text-red-600 mt-1" id="metric-expense"><?= format_currency($total_expense) ?></p>
                </div>
                <div class="text-center p-3 rounded-lg border border-blue-100 bg-blue-50">
                    <p class="text-sm text-gray-600">Net Balance</p>
                    <p class="text-xl font-bold <?= $net_balance >= 0 ? 'text-blue-600' : 'text-red-600' ?> mt-1" id="metric-balance"><?= format_currency($net_balance) ?></p>
                </div>
                <div class="text-center p-3 rounded-lg border border-yellow-100 bg-yellow-50">
                    <p class="text-sm text-gray-600">Savings Rate</p>
                    <p class="text-xl font-bold text-orange-500 mt-1" id="metric-savings-rate"><?= $savings_rate ?>%</p>
                </div>
            </div>

            <!-- Textual Summary -->
            <div class="mt-6 pt-4 border-t border-gray-100" id="summary-section">
                <h4 class="text-lg font-semibold text-gray-800 mb-2"><i id="summary-icon" class="<?= $summary['icon'] ?> mr-2"></i> Financial Health Check</h4>
                <p class="text-gray-700 leading-relaxed" id="summary-text"><?= $summary['text'] ?></p>
            </div>
        </div>

        <!-- Report Generation Panel -->
        <div class="dashboard-card p-6 lg:col-span-1 flex flex-col justify-between scroll-animate" data-scroll-animate style="transition-delay: 0.3s;">
            <div class="space-y-4">
                <h3 class="text-xl font-semibold text-gray-800"><i class="fa-solid fa-file-pdf mr-2 text-red-600"></i> Generate MoneyMap Report</h3>
                <p class="text-sm text-gray-600">Download a complete, professionally formatted PDF report summarizing your metrics, trends, and category breakdowns.</p>
                
                <select id="reportPeriod" class="w-full border border-gray-300 rounded-lg p-3 bg-white shadow-sm focus:ring-primary-500 focus:border-primary-500 transition duration-150">
                    <option value="6m" selected>Last 6 Months Report</option>
                    <option value="1y">Last 1 Year Report</option>
                    <option value="all">All Time Report</option>
                </select>
            </div>
            
            <button id="downloadReportBtn" onclick="simulateDownload()" class="w-full bg-red-600 text-white p-3 rounded-lg hover:bg-red-700 transition duration-300 font-bold text-lg shadow-xl mt-6">
                <i class="fa-solid fa-download mr-2"></i> Download PDF Report
            </button>
        </div>

    </div>

    <!-- Monthly Trend Chart -->
    <div class="dashboard-card p-6 scroll-animate" data-scroll-animate style="transition-delay: 0.4s;">
        <h3 class="text-xl font-semibold text-gray-800 mb-4"><i class="fa-solid fa-chart-area mr-2 text-primary-600"></i> Monthly Income vs. Expense Trend</h3>
        <div class="relative w-full h-80 sm:h-96"> 
            <canvas id="monthlyTrendChart"></canvas>
        </div>
    </div>

</main>

<script>
// --- CHART.JS CONFIGURATION ---
Chart.register(ChartDataLabels);

const chartLabels = <?= json_encode($chart_labels) ?>;
const incomeData = <?= json_encode($income_data) ?>;
const expenseData = <?= json_encode($expense_data) ?>;

let monthlyChartInstance = null; // Store chart instance for updates

/**
 * Custom currency formatter matching PHP's implementation (₹#,###.##)
 * @param {number} amount 
 * @returns {string}
 */
function formatCurrencyJS(amount) {
    if (typeof amount !== 'number') return '₹0.00';
    return '₹' + amount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}


// Function to initialize the chart only once
function initializeChart(labels, income, expense) {
    const ctx = document.getElementById('monthlyTrendChart');
    if (!ctx) return;
    
    if (monthlyChartInstance) {
        monthlyChartInstance.destroy();
    }

    monthlyChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Income',
                    data: income,
                    backgroundColor: 'rgba(5, 150, 105, 0.8)', // Primary-600
                    borderColor: 'rgba(5, 150, 105, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Expense',
                    data: expense,
                    backgroundColor: 'rgba(220, 38, 38, 0.8)', // Red-600
                    borderColor: 'rgba(220, 38, 38, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: false, grid: { display: false }, title: { display: true, text: 'Month', font: { size: 14 } } },
                y: {
                    stacked: false,
                    beginAtZero: true,
                    title: { display: true, text: 'Amount (₹)', font: { size: 14 } },
                    // Ensure the y-axis dynamically adjusts the max value when new data comes in
                    suggestedMax: Math.max(...income, ...expense, 100) * 1.1 
                }
            },
            plugins: {
                legend: { position: 'top' },
                tooltip: { 
                    callbacks: {
                        label: (context) => {
                            let value = context.parsed.y;
                            return context.dataset.label + ': ' + formatCurrencyJS(value);
                        }
                    }
                },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    color: '#666',
                    font: { size: 10, weight: 'bold' },
                    formatter: (value) => value > 0 ? formatCurrencyJS(value).replace('₹', '₹').split('.')[0] : '', // Show whole numbers
                }
            }
        }
    });
}

// --- REAL-TIME DATA POLLING FUNCTION (NEW) ---
const updateMetrics = async () => {
    const spinner = document.getElementById('loading-spinner');
    if (!spinner) return;
    
    spinner.classList.remove('hidden'); // Show spinner

    try {
        const response = await fetch('reports.php?fetch_data=true');
        const data = await response.json();

        // 1. Update Metric Cards
        document.getElementById('metric-income').textContent = formatCurrencyJS(data.total_income);
        document.getElementById('metric-expense').textContent = formatCurrencyJS(data.total_expense);
        
        const balanceElement = document.getElementById('metric-balance');
        balanceElement.textContent = formatCurrencyJS(data.net_balance);
        balanceElement.className = `text-xl font-bold mt-1 ${data.net_balance >= 0 ? 'text-blue-600' : 'text-red-600'}`;
        
        document.getElementById('metric-savings-rate').textContent = `${data.savings_rate}%`;

        // 2. Update Summary Section
        document.getElementById('summary-text').textContent = data.summary_text.replace(/\*\*/g, ''); // Remove markdown for plain text
        
        const iconElement = document.getElementById('summary-icon');
        // Clear existing icon classes (fa-chart-line, fa-thumbs-up, fa-lightbulb, fa-exclamation-triangle)
        iconElement.className = iconElement.className.split(' ').filter(c => !c.startsWith('fa-') || c.includes('mr-')).join(' ');
        iconElement.classList.add(data.summary_icon.split(' ')[0]);
        iconElement.classList.add(data.summary_icon.split(' ')[1]);

        // 3. Update Chart
        if (monthlyChartInstance) {
            monthlyChartInstance.data.labels = data.chart_labels;
            monthlyChartInstance.data.datasets[0].data = data.income_data;
            monthlyChartInstance.data.datasets[1].data = data.expense_data;
            
            // Re-adjust suggested Max for the y-axis
            monthlyChartInstance.options.scales.y.suggestedMax = Math.max(...data.income_data, ...data.expense_data, 100) * 1.1;

            monthlyChartInstance.update();
        } else {
             // If chart hasn't been initialized (e.g., loaded out of view), initialize it
            initializeChart(data.chart_labels, data.income_data, data.expense_data);
        }

    } catch (error) {
        console.error("Failed to fetch updated metrics:", error);
        // Display user-friendly message if fetch fails
        document.getElementById('summary-text').textContent = "Error updating data. Check console for details.";
    } finally {
        spinner.classList.add('hidden'); // Hide spinner
    }
};


// --- REPORT DOWNLOAD SIMULATION ---
function simulateDownload() {
    const period = document.getElementById('reportPeriod').value;
    const btn = document.getElementById('downloadReportBtn');
    
    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Generating Report...';

    // Simulate network delay before actual download starts
    setTimeout(async () => {
        
        // Fetch the very latest data before generating report
        const response = await fetch('reports.php?fetch_data=true');
        const data = await response.json();
        
        // 1. Determine file name
        let filename = 'MoneyMap_Report_Summary.pdf';
        if (period === '6m') filename = 'MoneyMap_Report_Last_6_Months.pdf';
        if (period === '1y') filename = 'MoneyMap_Report_Last_1_Year.pdf';
        if (period === 'all') filename = 'MoneyMap_Report_All_Time.pdf';

        // 2. Create Placeholder Report Content (Using fetched data)
        const reportContent = 
`--- MoneyMap Financial Report (${period.toUpperCase()}) ---
Generated on: ${new Date().toLocaleDateString()}
User: <?= $user_name ?>

Metrics:
Total Income: ${formatCurrencyJS(data.total_income)}
Total Expense: ${formatCurrencyJS(data.total_expense)}
Net Balance: ${formatCurrencyJS(data.net_balance)}
Savings Rate: ${data.savings_rate}%

Financial Health Summary:
${data.summary_text.replace(/\*\*/g, '')}

Monthly Trends (Last 6 Months):
${data.chart_labels.map((label, index) => 
    `${label}: Income ${formatCurrencyJS(data.income_data[index])}, Expense ${formatCurrencyJS(data.expense_data[index])}`
).join('\n')}

NOTE: This is a simulated plain text report. A real system would generate a detailed PDF file here.
`;

        // 3. Create Blob and Anchor for Download
        const blob = new Blob([reportContent], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);


        // 4. Update UI to success/reset
        btn.innerHTML = '<i class="fa-solid fa-check-circle mr-2"></i> Download Initiated!';
        btn.classList.replace('bg-red-600', 'bg-green-600');
        
        // Reset after 3 seconds
        setTimeout(() => {
            btn.innerHTML = '<i class="fa-solid fa-download mr-2"></i> Download PDF Report';
            btn.classList.replace('bg-green-600', 'bg-red-600');
            btn.disabled = false;
        }, 3000);

    }, 1000); // Simulated delay
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
    const options = {
        root: null, 
        rootMargin: '0px 0px -10% 0px',
        threshold: 0.05 
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                observer.unobserve(entry.target);
            }
        });
    }, options);

    document.querySelectorAll('.scroll-animate').forEach(element => {
        observer.observe(element);
    });
};

window.addEventListener('load', () => {
    setupScrollAnimations();
    initializeChart(chartLabels, incomeData, expenseData);
    
    // Start polling for real-time updates every 15 seconds
    setInterval(updateMetrics, 15000); 
});
</script>

</body>
</html>

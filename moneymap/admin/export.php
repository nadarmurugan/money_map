<?php
// ===================================
// export.php (MoneyMap Admin Panel - Data Export Handler)
// ===================================

// --- SESSION CHECK & ADMIN AUTH ---
session_start();

// Check if the specific admin session variable is set
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect unauthenticated users
    header('Location: admin_login.php');
    exit();
}

// Check for required POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['data_type']) || !isset($_POST['format'])) {
    $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Invalid export request.'];
    header('Location: admin_dashboard.php');
    exit();
}

$data_type = $_POST['data_type'];
$format = $_POST['format'];

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
            die("Database connection failed during export."); 
        }
    }
    return $pdo;
}
$pdo = db_connect();

// --- SQL QUERY DEFINITIONS ---
$queries = [
    'users' => [
        'sql' => "SELECT id, fullname, email, created_at FROM users ORDER BY id ASC",
        'headers' => ['ID', 'Full Name', 'Email', 'Registered On'],
        'filename' => 'moneymap_users_export'
    ],
    'transactions' => [
        'sql' => "SELECT id, user_id, type, amount, category, description, created_at FROM transactions ORDER BY created_at DESC",
        'headers' => ['ID', 'User ID', 'Type', 'Amount', 'Category', 'Description', 'Date'],
        'filename' => 'moneymap_transactions_export'
    ]
];

// Check if the requested data type is valid
if (!array_key_exists($data_type, $queries)) {
    $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Invalid data type specified for export.'];
    header('Location: admin_dashboard.php');
    exit();
}

$config = $queries[$data_type];
$data = [];

try {
    $stmt = $pdo->query($config['sql']);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Export query failed: " . $e->getMessage());
    $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Failed to fetch data from the database.'];
    header('Location: admin_dashboard.php');
    exit();
}


// ===================================
// --- EXPORT LOGIC ---
// ===================================

if ($format === 'csv') {
    
    // --- CSV EXPORT ---

    $filename = $config['filename'] . '_' . date('Ymd_His') . '.csv';
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

    // Output the column headers
    fputcsv($output, $config['headers']);

    // Output the data rows
    foreach ($data as $row) {
        // Ensure the order matches the headers
        $csv_row = [];
        foreach ($config['headers'] as $header) {
            // This relies on the headers matching the database column names case-insensitively, 
            // or the keys being manually mapped from the $row array.
            // Since we defined specific SELECT statements, we map the columns:
            if ($data_type === 'users') {
                 $csv_row = [$row['id'], $row['fullname'], $row['email'], $row['created_at']];
            } elseif ($data_type === 'transactions') {
                 $csv_row = [$row['id'], $row['user_id'], $row['type'], $row['amount'], $row['category'], $row['description'], $row['created_at']];
            }
        }
        fputcsv($output, $csv_row);
    }

    fclose($output);
    exit();

} elseif ($format === 'pdf') {
    
    // --- PDF EXPORT (Requires external library like dompdf) ---
    
    // *****************************************************************
    // NOTE: This PDF section is NON-FUNCTIONAL without installing 
    // a PDF library (e.g., composer require dompdf/dompdf).
    // It is provided as a placeholder structure.
    // *****************************************************************
    
    $filename = $config['filename'] . '_' . date('Ymd_His') . '.pdf';
    
    $html = '<!DOCTYPE html><html><head><title>Export</title>
             <style>
                 body { font-family: Poppins, sans-serif; }
                 h1 { color: #10B981; }
                 table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                 th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 10px; }
                 th { background-color: #f2f2f2; }
             </style>
             </head><body>';
    
    $html .= '<h1>MoneyMap Admin Export - ' . ucfirst($data_type) . '</h1>';
    
    if (empty($data)) {
        $html .= '<p>No data found to export.</p>';
    } else {
        $html .= '<table><thead><tr>';
        foreach ($config['headers'] as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            if ($data_type === 'users') {
                $html .= '<td>' . htmlspecialchars($row['id']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['fullname']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['email']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['created_at']) . '</td>';
            } elseif ($data_type === 'transactions') {
                $html .= '<td>' . htmlspecialchars($row['id']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['user_id']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['type']) . '</td>';
                $html .= '<td>' . htmlspecialchars(number_format($row['amount'], 2)) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['category']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['description']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['created_at']) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
    }

    $html .= '</body></html>';

    // --- DOMPDF or TCPDF Integration placeholder ---
    /* require 'vendor/autoload.php'; // Path to Composer autoload
    use Dompdf\Dompdf;
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream($filename); 
    */
    
    // Fallback/Simulated PDF Output (for non-integrated systems)
    // Since we cannot run a library, we redirect with an error message.
    $_SESSION['admin_message'] = [
        'type' => 'error', 
        'text' => 'PDF export is currently disabled as it requires a third-party library (e.g., dompdf) which is not installed.'
    ];
    header('Location: admin_dashboard.php');
    exit();

} else {
    // Invalid format
    $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Invalid export format requested.'];
    header('Location: admin_dashboard.php');
    exit();
}
?>
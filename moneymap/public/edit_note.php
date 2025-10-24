<?php
// ===================================
// edit_note.php (Note Editing Interface)
// ===================================

// --- SESSION CHECK ---
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// --- DATABASE CONFIG (Reusing constants from dashboard) ---
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

$note = null;
$error_message = '';
$note_id = 0;

// --- 1. HANDLE POST REQUEST (UPDATE NOTE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['note_id'], $_POST['note_content'])) {
    $note_id = (int)$_POST['note_id'];
    $new_content = trim($_POST['note_content']);
    
    if (empty($new_content)) {
        // If content is empty, we set an error message and proceed to re-display the form
        $error_message = 'Note content cannot be empty. Please enter your thoughts.';
    } else {
        try {
            // FIX: Removed 'updated_at = NOW()' since the user's table schema does not include an updated_at column.
            // If the user adds this column later, this line should be reinstated.
            $stmt = $pdo->prepare("UPDATE user_notes SET note_content = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$new_content, $note_id, $user_id]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['note_message'] = ['type' => 'success', 'text' => 'Note updated successfully!'];
            } else {
                $_SESSION['note_message'] = ['type' => 'error', 'text' => 'Update failed. Note not found or no changes were made.'];
            }
            
            // Redirect back to the dashboard after successful update
            header('Location: dashboard.php');
            exit();
            
        } catch (PDOException $e) {
            error_log("Note update failed: " . $e->getMessage());
            $error_message = 'A database error occurred while updating the note.';
        }
    }
}

// --- 2. HANDLE GET REQUEST (FETCH NOTE FOR EDITING) ---
if (isset($_GET['note_id'])) {
    $note_id = (int)$_GET['note_id'];
    
    try {
        // Fetch the note, ensuring it belongs to the current user
        $stmt = $pdo->prepare("SELECT id, note_content FROM user_notes WHERE id = ? AND user_id = ?");
        $stmt->execute([$note_id, $user_id]);
        $note = $stmt->fetch();
        
        if (!$note) {
            // If the note doesn't exist or doesn't belong to the user, redirect
            $_SESSION['note_message'] = ['type' => 'error', 'text' => 'The requested note could not be found or accessed.'];
            header('Location: dashboard.php');
            exit();
        }
    } catch (PDOException $e) {
        error_log("Note fetch failed: " . $e->getMessage());
        $_SESSION['note_message'] = ['type' => 'error', 'text' => 'Could not retrieve note for editing due to a database error.'];
        header('Location: dashboard.php');
        exit();
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // If no note_id is provided in GET, redirect
    $_SESSION['note_message'] = ['type' => 'error', 'text' => 'No note ID specified for editing.'];
    header('Location: dashboard.php');
    exit();
}
// If it was a failed POST submission, $note will be null, and we need to reconstruct it 
// so the form can be pre-filled with the attempted content.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $note_id > 0) {
    $note = ['id' => $note_id, 'note_content' => $new_content ?? ''];
}


// --- FETCH USER INFO FOR HEADER (Simplified) ---
$stmt = $pdo->prepare("SELECT COALESCE(fullname, 'MoneyMapper User') as fullname FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_name = htmlspecialchars($user['fullname'] ?? 'MoneyMapper'); 
$first_name = htmlspecialchars(explode(' ', $user_name)[0]);

// Helper to check if we have a note to display the form
if (!$note) {
    // Should be unreachable if initial GET/POST handling worked, but as a safeguard:
    $error_message = 'Fatal error: Note data is missing.';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Note | MoneyMap Pro 📝</title>
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
                'red': { 50: '#FEF2F2', 100: '#FEE2E2', 600: '#DC2626' }, 
                'blue': { 50: '#EFF6FF', 500: '#3B82F6', 600: '#2563EB' }, 
            },
            keyframes: {
                'slide-in': { '0%': { opacity: 0, transform: 'translateY(10px)' }, '100%': { opacity: 1, transform: 'translateY(0)' } },
            },
            animation: {
                'slide-in': 'slide-in 0.6s ease-out',
            }
        }
    }
}
</script>

<style>
body{font-family:'Poppins',sans-serif; background:#f4f7f9;} 
.container-card{
    background:rgba(255,255,255,1);
    border-radius:1rem; 
    border:1px solid theme('colors.gray.200'); 
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
    animation: slide-in 0.6s backwards;
}
.input-focus:focus {
    box-shadow: 0 0 0 3px theme('colors.primary.200');
    border-color: theme('colors.primary.600');
}
</style>
</head>
<body class="min-h-screen flex items-start justify-center p-4 sm:p-8">

<main class="w-full max-w-2xl mt-10">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-extrabold text-gray-900 flex items-center">
            <i class="fa-solid fa-file-pen text-primary-600 mr-3"></i> Edit Journal Note
        </h1>
        <a href="dashboard.php" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition duration-150 flex items-center">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="container-card p-6 sm:p-8">
        <?php if (!empty($error_message)): ?>
            <div class="p-4 mb-4 rounded-lg bg-red-100 text-red-800 font-medium border border-red-300">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($note): ?>
        <p class="text-gray-600 mb-6 border-b pb-4">
            You are editing Note ID: <span class="font-bold text-primary-700"><?= $note['id'] ?></span>. Save your changes below.
        </p>

        <form method="POST" action="edit_note.php" class="space-y-6">
            <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
            
            <div>
                <label for="note_content" class="block text-sm font-medium text-gray-700 mb-2">Edit Note Content</label>
                <textarea 
                    name="note_content" 
                    id="note_content" 
                    rows="8" 
                    required
                    placeholder="Enter your updated note here..." 
                    class="w-full border border-gray-300 rounded-lg shadow-inner p-4 resize-none text-base focus:ring-primary-500 focus:border-primary-500 transition duration-150 input-focus"
                ><?= htmlspecialchars($note['note_content']) ?></textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="dashboard.php" class="bg-gray-300 text-gray-800 py-3 px-6 rounded-lg hover:bg-gray-400 transition duration-300 font-medium shadow-md">
                    Cancel
                </a>
                <button type="submit" class="bg-primary-600 text-white py-3 px-6 rounded-lg hover:bg-primary-700 transition duration-300 font-medium shadow-md hover:shadow-lg flex items-center">
                    <i class="fa-solid fa-save mr-2"></i> Save Changes
                </button>
            </div>
        </form>
        <?php else: ?>
             <p class="text-center text-gray-500 p-8 border border-dashed rounded-lg">
                <i class="fa-solid fa-exclamation-triangle text-3xl text-red-400 mb-3"></i><br>
                Unable to load the note for editing. Please return to the dashboard.
            </p>
        <?php endif; ?>
    </div>
</main>

</body>
</html>

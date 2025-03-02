<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require '../../db.php';

$log_file = '../../logs/app.log';

function logMessage($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// ✅ Return JSON error if session values are missing
if (!isset($_SESSION['company_id']) || !isset($_SESSION['user_id'])) {
    logMessage("Unauthorized access attempt.");
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];
$value = $_GET['term'] ?? ''; 
$type = $_GET['type'] ?? ''; 

logMessage("Request received - company_id: $company_id, user_id: $user_id, term: $value, type: $type");

// ✅ Return JSON error if parameters are missing
if (empty($value) || empty($type)) {
    logMessage("Missing input parameters.");
    echo json_encode([]);
    exit;
}

$tableMap = [
    'material' => ['table' => 'rm_master', 'column' => 'material'],
    'unit' => ['table' => 'rm_master_units', 'column' => 'unit']
];

if (!array_key_exists($type, $tableMap)) {
    logMessage("Invalid type: $type");
    echo json_encode([]);
    exit;
}

$table = $tableMap[$type]['table'];
$column = $tableMap[$type]['column'];

try {
    $query = "SELECT DISTINCT `$column` FROM `$table` WHERE `$column` LIKE ? AND company_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($con, $query);

    if (!$stmt) {
        logMessage("SQL Prepare Error: " . mysqli_error($con));
        echo json_encode(['error' => 'SQL Prepare Error']);
        exit;
    }

    $searchTerm = "%$value%";
    mysqli_stmt_bind_param($stmt, "sii", $searchTerm, $company_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $results = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $results[] = $row[$column];
    }

    mysqli_stmt_close($stmt);

    // ✅ Ensure JSON output even if empty
    logMessage("Results: " . json_encode($results));
    echo json_encode($results ?: []);

} catch (Exception $e) {
    logMessage("DB Error: " . $e->getMessage());
    echo json_encode(['error' => 'Database Error']);
}
?>

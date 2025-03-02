<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once('../../db.php');

// Ensure session variables exist
if (!isset($_SESSION['company_id']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];

// Logging setup
$log_file = '../../logs/app.log';

// Ensure the logs directory exists
// if (!file_exists('../../logs')) {
//     mkdir('../../logs', 0777, true);
// }

function logMessage($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

logMessage("API request received.");

// Get query parameters
$value = $_GET['term'] ?? ''; 
$type = $_GET['type'] ?? ''; 

if (empty($value) || empty($type)) {
    logMessage("Missing query parameters: term=$value, type=$type");
    echo json_encode([]);
    exit;
}

logMessage("Query parameters: term=$value, type=$type");

$tableMap = [
    'product' => ['table' => 'finished_products', 'column' => 'product_name'],
    'customer' => ['table' => 'customers', 'column' => 'billing_name']
];

if (!array_key_exists($type, $tableMap)) {
    logMessage("Invalid type: $type");
    echo json_encode([]);
    exit;
}

$table = $tableMap[$type]['table'];
$column = $tableMap[$type]['column'];

// Prepare SQL query with WHERE clause for company_id and user_id
$query = "SELECT DISTINCT `$column` FROM `$table` WHERE `$column` LIKE ? AND company_id = ? AND user_id = ?";
$stmt = mysqli_prepare($con, $query);

if (!$stmt) {
    logMessage("Query preparation failed: " . mysqli_error($con));
    echo json_encode(['error' => 'Database query failed.']);
    exit;
}

// Bind parameters and execute
$searchValue = "%$value%";
mysqli_stmt_bind_param($stmt, "sii", $searchValue, $company_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Fetch results into an array
$results = [];
while ($row = mysqli_fetch_assoc($result)) {
    $results[] = $row[$column];
}
mysqli_stmt_close($stmt);

logMessage("Query executed successfully. Results found: " . count($results));
echo json_encode($results);
?>

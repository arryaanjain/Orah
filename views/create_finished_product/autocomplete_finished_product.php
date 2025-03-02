<?php
session_start();
require '../../db.php';

header('Content-Type: application/json');

// Get query parameters
$company_id = $_SESSION['company_id'] ?? '';
$value = $_GET['term'] ?? ''; 
$type = $_GET['type'] ?? ''; 

if (empty($company_id) || empty($value) || empty($type)) {
    echo json_encode([]);
    exit;
}

// Define table and column mapping
$tableMap = [
    'material' => ['table' => 'rm_master', 'column' => 'material'],
    'unit' => ['table' => 'rm_master_units', 'column' => 'unit']
];

if (!isset($tableMap[$type])) {
    echo json_encode([]);
    exit;
}

$table = $tableMap[$type]['table'];
$column = $tableMap[$type]['column'];

// Prepare and execute the SQL statement
$query = "SELECT DISTINCT `$column` FROM `$table` WHERE `$column` LIKE ? AND company_id = ?";
$stmt = mysqli_prepare($con, $query);
$searchTerm = "%" . $value . "%";
mysqli_stmt_bind_param($stmt, "si", $searchTerm, $company_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$results = [];
while ($row = mysqli_fetch_assoc($result)) {
    $results[] = $row[$column];
}

echo json_encode($results);
?>

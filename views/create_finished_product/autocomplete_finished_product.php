<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connect to the MySQL server
$dsn = "mysql:host=localhost";
$username = "root";
$password = "";

try {
    $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    file_put_contents('debug.log', "Connected to MySQL successfully\n", FILE_APPEND);
    
    // Get query parameters
    $company_name = $_GET['company_name'] ?? ''; 
    $value = $_GET['term'] ?? ''; 
    $type = $_GET['type'] ?? ''; 
    
    file_put_contents('debug.log', "company_name: $company_name, term: $value, type: $type\n", FILE_APPEND);
    
    if (empty($company_name) || empty($value) || empty($type)) {
        file_put_contents('debug.log', "Missing input parameters\n", FILE_APPEND);
        echo json_encode([]);
        exit;
    }

    // Dynamically switch to the correct company database
    $pdo->exec("USE `$company_name`");
    file_put_contents('debug.log', "Switched to database: $company_name\n", FILE_APPEND);

    $tableMap = [
        'material' => ['table' => 'rm_master', 'column' => 'material'],
        'unit' => ['table' => 'rm_master_units', 'column' => 'unit']
    ];

    if (!array_key_exists($type, $tableMap)) {
        file_put_contents('debug.log', "Invalid type: $type\n", FILE_APPEND);
        echo json_encode([]);
        exit;
    }

    $table = $tableMap[$type]['table'];
    $column = $tableMap[$type]['column'];

    // Prepare and execute the SQL statement
    try {
        $statement = $pdo->prepare("SELECT DISTINCT `$column` FROM `$table` WHERE `$column` LIKE ?");
        $statement->execute(["%" . $value . "%"]);
        file_put_contents('debug.log', "SQL executed successfully: SELECT DISTINCT `$column` FROM `$table` WHERE `$column` LIKE '%$value%'\n", FILE_APPEND);
    } catch (PDOException $e) {
        file_put_contents('debug.log', "SQL Execution failed: " . $e->getMessage() . "\n", FILE_APPEND);
        echo json_encode(['error' => 'SQL query failed']);
        exit;
    }

    $results = $statement->fetchAll(PDO::FETCH_COLUMN);
    if (empty($results)) {
        file_put_contents('debug.log', "No results found for: $value\n", FILE_APPEND);
    } else {
        file_put_contents('debug.log', "Results: " . json_encode($results) . "\n", FILE_APPEND);
    }

    echo json_encode($results);

} catch (PDOException $e) {
    file_put_contents('debug.log', "DB Connection failed: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['error' => $e->getMessage()]);
}

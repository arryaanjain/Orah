<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Logging setup
$log_file = 'logs/api.log';

// Ensure the logs directory exists
if (!file_exists('logs')) {
    mkdir('logs', 0777, true);
}

function logMessage($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

logMessage("API request received.");

// Connect to the MySQL server
$dsn = "mysql:host=localhost";
$username = "root";
$password = "";

try {
    $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    logMessage("Connected to MySQL server.");

    // Get query parameters
    $company_name = $_GET['company_name'] ?? ''; 
    $value = $_GET['term'] ?? ''; 
    $type = $_GET['type'] ?? ''; 
    
    if (empty($company_name) || empty($value) || empty($type)) {
        logMessage("Missing query parameters: company_name=$company_name, term=$value, type=$type");
        echo json_encode([]);
        exit;
    }

    logMessage("Query parameters: company_name=$company_name, term=$value, type=$type");

    // Dynamically switch to the correct company database
    $pdo->exec("USE `$company_name`");
    logMessage("Switched to database: $company_name");

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

    // Prepare and execute the SQL statement
    $statement = $pdo->prepare("SELECT DISTINCT `$column` FROM `$table` WHERE `$column` LIKE ?");
    $statement->execute(["%" . $value . "%"]);
    $results = $statement->fetchAll(PDO::FETCH_COLUMN);

    logMessage("Query executed successfully: SELECT DISTINCT `$column` FROM `$table` WHERE `$column` LIKE '%$value%'");
    logMessage("Results found: " . count($results));

    echo json_encode($results);

} catch (PDOException $e) {
    $errorMessage = "Database error: " . $e->getMessage();
    logMessage($errorMessage);
    echo json_encode(['error' => $errorMessage]);
}
?>

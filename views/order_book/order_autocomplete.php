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

    // Get query parameters
    $company_name = $_GET['company_name'] ?? ''; 
    $value = $_GET['term'] ?? ''; 
    $type = $_GET['type'] ?? ''; 
    
    if (empty($company_name) || empty($value) || empty($type)) {
        echo json_encode([]);
        exit;
    }

    // Dynamically switch to the correct company database
    $pdo->exec("USE `$company_name`");

    $tableMap = [
        'product' => ['table' => 'finished_products', 'column' => 'product_name'],
        'customer' => ['table' => 'customers', 'column' => 'billing_name'] // Added customer type
    ];

    if (!array_key_exists($type, $tableMap)) {
        echo json_encode([]);
        exit;
    }

    $table = $tableMap[$type]['table'];
    $column = $tableMap[$type]['column'];

    // Prepare and execute the SQL statement
    $statement = $pdo->prepare("SELECT DISTINCT `$column` FROM `$table` WHERE `$column` LIKE ?");
    $statement->execute(["%" . $value . "%"]);

    $results = $statement->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($results);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>

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
    $product_name = $_GET['product_name'] ?? ''; 

    file_put_contents('debug.log', "product_name: $product_name\n", FILE_APPEND);

    if (empty($product_name)) {
        file_put_contents('debug.log', "Missing product_name parameter\n", FILE_APPEND);
        echo json_encode([]);
        exit;
    }

    // Get company name from session or query (as applicable)
    $company_name = $_GET['company_name'] ?? ''; 
    if (empty($company_name)) {
        file_put_contents('debug.log', "Missing company_name session or parameter\n", FILE_APPEND);
        echo json_encode([]);
        exit;
    }

    // Dynamically switch to the correct company database
    $pdo->exec("USE `$company_name`");
    file_put_contents('debug.log', "Switched to database: $company_name\n", FILE_APPEND);

    // Query to fetch id, material, qty, and unit for the selected product
    $stmt = $pdo->prepare("SELECT id, material, qty, unit FROM `$product_name`");
    $stmt->execute();

    // Fetch results
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($materials)) {
        file_put_contents('debug.log', "No materials found for product_name: $product_name\n", FILE_APPEND);
    } else {
        file_put_contents('debug.log', "Materials fetched: " . json_encode($materials) . "\n", FILE_APPEND);
    }

    echo json_encode($materials);

} catch (PDOException $e) {
    file_put_contents('debug.log', "DB Connection failed: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['error' => $e->getMessage()]);
}

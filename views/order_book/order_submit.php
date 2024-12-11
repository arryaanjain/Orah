<?php
// Connect to the MySQL server
$dsn = "mysql:host=localhost";
$username = "root";
$password = "";

try {
    $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Get form data
    $company_name = $_POST['company_name'] ?? '';
    $order_dates = $_POST['order_date'] ?? []; // Array of order dates
    $products = $_POST['column1'] ?? [];  // Array of product values
    $quantities = $_POST['column2'] ?? [];  // Array of quantity values
    $billing_names = $_POST['billing_name'] ?? []; // Array of billing names

    // Validate required fields
    if (empty($company_name) || empty($products) || empty($quantities) || empty($order_dates) || empty($billing_names)) {
        echo "Missing required fields";
        exit;
    }

    // Dynamically switch to the correct company database
    $pdo->exec("USE `$company_name`");

    // Prepare the statement to get customer_id based on billing_name
    $stmt = $pdo->prepare("SELECT id FROM customers WHERE billing_name = ? LIMIT 1");

    // Prepare the insert query for order_book
    $insertStmt = $pdo->prepare("INSERT INTO order_book (order_date, product_name, qty, customer_id) VALUES (?, ?, ?, ?)");

    // Insert each row into the order_book table
    for ($i = 0; $i < count($products); $i++) {
        // Get customer_id based on billing_name
        $stmt->execute([$billing_names[$i]]);
        $customer_id = $stmt->fetchColumn();

        if (!$customer_id) {
            echo "Customer with billing name '{$billing_names[$i]}' not found.";
            exit;
        }

        // Insert the order into the order_book
        $insertStmt->execute([$order_dates[$i], $products[$i], $quantities[$i], $customer_id]);
    }

    // // Include the file to calculate totalQuantity
    // require 'calculate.php';
    
    // Display success message
    echo '<div class="alert alert-success" role="alert">Order book has been successfully updated.</div><br><a href="/PIMS/order_book.php" class="btn btn-primary">Go to Order Book Form</a>';
    exit;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

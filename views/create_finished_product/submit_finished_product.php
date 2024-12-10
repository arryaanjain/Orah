<?php
// Connect to the MySQL server
$dsn = "mysql:host=localhost";
$username = "root";
$password = "";

try {
    $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Get form data
    $company_name = $_POST['company_name'] ?? '';
    $product_name = $_POST['product_name'] ?? '';
    $materials = $_POST['material'] ?? [];
    $quantities = $_POST['qty'] ?? [];
    $units = $_POST['unit'] ?? [];

    // Validate required fields
    if (empty($product_name) || empty($materials) || empty($quantities) || empty($units)) {
        echo "Missing required fields";
        exit;
    }

    // **Sanitize product name to create a valid table name**
    $sanitized_product_name = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($product_name));

    // Dynamically switch to the correct company database
    $pdo->exec("USE `$company_name`");

    // **Create a new table for the product**
    $createTableSQL = "CREATE TABLE IF NOT EXISTS `$sanitized_product_name` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        material VARCHAR(255) NOT NULL,
        qty DECIMAL(10, 2) NOT NULL,
        unit VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($createTableSQL);
    file_put_contents('debug.log', "Table `$sanitized_product_name` created successfully\n", FILE_APPEND);

    // **Insert into finished_products table** (only for metadata if required)
    $stmtProduct = $pdo->prepare("INSERT INTO finished_products (product_name) VALUES (?)");
    $stmtProduct->execute([$product_name]);
    $product_id = $pdo->lastInsertId();

    // **Insert each row into the newly created product-specific table**
    $stmtMaterial = $pdo->prepare("INSERT INTO `$sanitized_product_name` (material, qty, unit) VALUES (?, ?, ?)");
    for ($i = 0; $i < count($materials); $i++) {
        $stmtMaterial->execute([$materials[$i], $quantities[$i], $units[$i]]);
    }

    echo '<div class="alert alert-success" role="alert">
            Product and its materials have been successfully created.
          </div><br>
          <a href="/PIMS/create_finished_product.php" class="btn btn-primary">Go Back</a>';

} catch (PDOException $e) {
    file_put_contents('debug.log', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    echo "Error: " . $e->getMessage();
}

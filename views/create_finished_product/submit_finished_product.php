<?php
session_start();
require '../../db.php';

// Get form data
$company_id = $_SESSION['company_id'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';
$product_name = $_POST['product_name'] ?? '';
$description = $_POST['description'] ?? '';
$materials = $_POST['material'] ?? [];
$quantities = $_POST['qty'] ?? [];
$units = $_POST['unit'] ?? [];

if (empty($company_id) || empty($user_id) || empty($product_name) || empty($materials) || empty($quantities) || empty($units)) {
    $_SESSION['message'] = "Missing required fields";
    $_SESSION['message_type'] = "danger";
    header("Location: /PIMS/create_finished_product.php");
    exit;
}

// **Sanitize product name for valid table name**
$sanitized_product_name = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($product_name));

// **Check if product already exists**
$checkQuery = "SELECT id FROM finished_products WHERE product_name = ? AND company_id = ?";
$stmtCheck = mysqli_prepare($con, $checkQuery);
mysqli_stmt_bind_param($stmtCheck, "si", $product_name, $company_id);
mysqli_stmt_execute($stmtCheck);
mysqli_stmt_store_result($stmtCheck);

if (mysqli_stmt_num_rows($stmtCheck) > 0) {
    $_SESSION['message'] = "Error: Product '$product_name' already exists!";
    $_SESSION['message_type'] = "danger";
    header("Location: /PIMS/create_finished_product.php");
    exit;
}
mysqli_stmt_close($stmtCheck);

// **Insert into finished_products**
$insertProductSQL = "INSERT INTO finished_products (product_name, description, company_id, user_id, creation_date, status) 
                     VALUES (?, ?, ?, ?, CURDATE(), 'active')";
$stmtProduct = mysqli_prepare($con, $insertProductSQL);
mysqli_stmt_bind_param($stmtProduct, "ssii", $product_name, $description, $company_id, $user_id);
mysqli_stmt_execute($stmtProduct);
$product_id = mysqli_insert_id($con);
mysqli_stmt_close($stmtProduct);

// **Create a table for the new product**
$createTableSQL = "CREATE TABLE IF NOT EXISTS `$sanitized_product_name` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material VARCHAR(255) NOT NULL,
    qty DECIMAL(10, 2) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    company_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!mysqli_query($con, $createTableSQL)) {
    file_put_contents('debug.log', "Error creating table `$sanitized_product_name`: " . mysqli_error($con) . "\n", FILE_APPEND);
    $_SESSION['message'] = "Error creating product table.";
    $_SESSION['message_type'] = "danger";
    header("Location: /PIMS/create_finished_product.php");
    exit;
}

// **Insert materials into the product table**
$insertMaterialSQL = "INSERT INTO `$sanitized_product_name` (material, qty, unit, company_id, user_id) VALUES (?, ?, ?, ?, ?)";
$stmtMaterial = mysqli_prepare($con, $insertMaterialSQL);

for ($i = 0; $i < count($materials); $i++) {
    if (!empty($materials[$i]) && !empty($quantities[$i]) && !empty($units[$i])) {
        mysqli_stmt_bind_param($stmtMaterial, "sdsii", $materials[$i], $quantities[$i], $units[$i], $company_id, $user_id);
        mysqli_stmt_execute($stmtMaterial);
    }
}
mysqli_stmt_close($stmtMaterial);

$_SESSION['message'] = "Product and materials added successfully!";
$_SESSION['message_type'] = "success";
header("Location: /PIMS/create_finished_product.php");
exit;
?>

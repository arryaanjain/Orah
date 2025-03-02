<?php
session_start();
require_once('../../db.php');

header('Content-Type: text/html; charset=UTF-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure session variables exist
if (!isset($_SESSION['company_id']) || !isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: /PIMS/order_book.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];

$order_dates = $_POST['order_date'] ?? [];
$products = $_POST['product_name'] ?? [];  
$quantities = $_POST['qty'] ?? [];  
$billing_names = $_POST['billing_name'] ?? [];

// Validate required fields
if (empty($products) || empty($quantities) || empty($order_dates) || empty($billing_names)) {
    $_SESSION['error'] = "Missing required fields.";
    header("Location: /PIMS/order_book.php");
    exit();
}

$stmtCustomer = mysqli_prepare($con, "SELECT id FROM customers WHERE billing_name = ? AND company_id = ? AND user_id = ? LIMIT 1");
$stmtProduct = mysqli_prepare($con, "SELECT id FROM finished_products WHERE product_name = ? AND company_id = ? AND user_id = ? LIMIT 1");
$stmtInsert = mysqli_prepare($con, "INSERT INTO order_book (order_date, product_id, qty, customer_id, company_id, user_id) VALUES (?, ?, ?, ?, ?, ?)");

$allInserted = true;

for ($i = 0; $i < count($products); $i++) {
    $order_date = $order_dates[$i];
    $product_name = $products[$i];
    $quantity = $quantities[$i];
    $billing_name = $billing_names[$i];

    mysqli_stmt_bind_param($stmtProduct, "sii", $product_name, $company_id, $user_id);
    mysqli_stmt_execute($stmtProduct);
    mysqli_stmt_bind_result($stmtProduct, $product_id);
    mysqli_stmt_fetch($stmtProduct);
    mysqli_stmt_free_result($stmtProduct);

    if (!$product_id) {
        $_SESSION['error'] = "Product '$product_name' not found.";
        header("Location: /PIMS/order_book.php");
        exit();
    }

    mysqli_stmt_bind_param($stmtCustomer, "sii", $billing_name, $company_id, $user_id);
    mysqli_stmt_execute($stmtCustomer);
    mysqli_stmt_bind_result($stmtCustomer, $customer_id);
    mysqli_stmt_fetch($stmtCustomer);
    mysqli_stmt_free_result($stmtCustomer);

    if (!$customer_id) {
        $_SESSION['error'] = "Customer '$billing_name' not found.";
        header("Location: /PIMS/order_book.php");
        exit();
    }

    mysqli_stmt_bind_param($stmtInsert, "siiiii", $order_date, $product_id, $quantity, $customer_id, $company_id, $user_id);
    if (!mysqli_stmt_execute($stmtInsert)) {
        $allInserted = false;
    }
}

mysqli_stmt_close($stmtCustomer);
mysqli_stmt_close($stmtProduct);
mysqli_stmt_close($stmtInsert);
mysqli_close($con);

if ($allInserted) {
    $_SESSION['success'] = "✅ Order book updated successfully!";
} else {
    $_SESSION['error'] = "Some orders could not be recorded.";
}

header("Location: /PIMS/order_book.php");
exit();

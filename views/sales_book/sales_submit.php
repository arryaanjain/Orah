<?php
require_once('../../db.php'); 
session_start();

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$log_file = '../../logs/app.log';
function logMessage($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

$company_id = 6;
$user_id = 6;
// $company_id = $_SESSION['company_id'];
// $user_id = $_SESSION['user_id'];

logMessage("===== Sales Processing Started =====");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $salesData = $_POST;
    logMessage("Received POST data: " . json_encode($salesData));

    if (!isset($salesData['sales_date'], $salesData['product_name'], $salesData['qty'], $salesData['billing_name'])) {
        logMessage("Error: Incomplete sales data.");
        die(json_encode(["error" => "Incomplete sales data."]));
    }

    // Ensure all arrays are of the same length
    $numEntries = count($salesData['sales_date']);
    
    for ($i = 0; $i < $numEntries; $i++) {
        $sales_date = $salesData['sales_date'][$i];
        $product_name = $salesData['product_name'][$i];
        $dispatched_qty = (int)$salesData['qty'][$i];
        $billing_name = $salesData['billing_name'][$i];

        logMessage("Processing sale: Date: $sales_date, Product: $product_name, Qty: $dispatched_qty, Customer: $billing_name");

        // Fetch product_id from finished_products
        $query = "SELECT id FROM finished_products WHERE product_name = ? AND company_id = ? LIMIT 1";
        $stmt = $con->prepare($query);
        $stmt->bind_param("si", $product_name, $company_id);
        $stmt->execute();
        $stmt->bind_result($product_id);
        $stmt->fetch();
        $stmt->close();

        if (!$product_id) {
            logMessage("Error: Product not found in finished_products.");
            continue; // Skip this entry and process the next
        }
        logMessage("Fetched Product ID: $product_id");

        // Fetch customer_id from customers
        $query = "SELECT id FROM customers WHERE billing_name = ? AND company_id = ? LIMIT 1";
        $stmt = $con->prepare($query);
        $stmt->bind_param("si", $billing_name, $company_id);
        $stmt->execute();
        $stmt->bind_result($customer_id);
        $stmt->fetch();
        $stmt->close();

        if (!$customer_id) {
            logMessage("Error: Customer not found.");
            continue; // Skip this entry and process the next
        }
        logMessage("Fetched Customer ID: $customer_id");

        // Fetch order details from order_book
        $query = "SELECT id, qty FROM order_book WHERE product_id = ? AND customer_id = ? AND company_id = ? AND user_id = ? LIMIT 1";
        $stmt = $con->prepare($query);
        $stmt->bind_param("iiii", $product_id, $customer_id, $company_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();

        if (!$order) {
            logMessage("Error: Order not found for product: $product_name and customer: $billing_name");
            continue; // Skip this entry and process the next
        }

        $order_id = $order['id'];
        $original_order_qty = (int)$order['qty'];
        logMessage("Fetched Order ID: $order_id, Original Order Qty: $original_order_qty");

        // Check inventory using calculate-order-material.php API
    $inventory_api_url = "http://localhost/PIMS/views/sales_book/calculate-order-material.php";

    // Ensure order_id is available
    if (!isset($order_id)) {
        logMessage("Error: order_id is missing.");
        die(json_encode(["error" => "Missing order_id."]));
    }

    $post_data = json_encode(["order_id" => $order_id, "products" => [$product_name]]);
    logMessage("Calling Inventory API: $inventory_api_url with data: $post_data");

    $ch = curl_init($inventory_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);

    logMessage("Inventory API Response: $response");

    $inventoryData = json_decode($response, true);

    if (!$inventoryData || !isset($inventoryData['inventory_status'])) {
        logMessage("Error: Inventory check failed for order_id: $order_id.");
        continue; // Skip this entry and process the next
    }

    $inventory = $inventoryData['inventory_status']; // Correct extraction
    $totalRequiredQty = 0;

    foreach ($inventory as $item) {
        $totalRequiredQty += $item['requiredQty'];
    }

    logMessage("Total Required Qty for inventory: $totalRequiredQty");

    // STEP 1: Record the sale in sales_book (now includes order_id)
    $query = "INSERT INTO sales_book (order_id, sales_date, qty, customer_id, company_id, user_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($query);
    $stmt->bind_param("isiiii", $order_id, $sales_date, $dispatched_qty, $customer_id, $company_id, $user_id);
    $stmt->execute();
    $stmt->close();
    logMessage("Sale recorded in sales_book with order_id: $order_id.");

    // STEP 2: Deduct materials by adding a negative record in rm_purchase
    foreach ($inventory as $item) {
        $material = $item['material'];
        $required_qty = $item['requiredQty'];
        $unit = $item['unit'];  // Now fetching unit from API response

        // Step 2.1: Get material_id from rm_master
        $query = "SELECT id FROM rm_master WHERE material = ? AND company_id = ? LIMIT 1";
        $stmt = $con->prepare($query);
        $stmt->bind_param("si", $material, $company_id);
        $stmt->execute();
        $stmt->bind_result($material_id);
        $stmt->fetch();
        $stmt->close();

        if (!$material_id) {
            logMessage("Error: Material not found in rm_master for material: $material");
            continue; // Skip this material and process the next
        }

        // Step 2.2: Get unit_id from rm_master_units
        $query = "SELECT id FROM rm_master_units WHERE unit = ? LIMIT 1";
        $stmt = $con->prepare($query);
        $stmt->bind_param("s", $unit);
        $stmt->execute();
        $stmt->bind_result($unit_id);
        $stmt->fetch();
        $stmt->close();

        if (!$unit_id) {
            logMessage("Error: Unit not found in rm_master_units for unit: $unit");
            continue; // Skip this material and process the next
        }

        // Step 2.3: Insert negative quantity into rm_purchase (now includes unit_id)
        $query = "INSERT INTO rm_purchase (purchase_date, material_id, qty, unit_id, company_id, user_id) 
                VALUES (NOW(), ?, ?, ?, ?, ?)";
        $negative_qty = -$required_qty;
        $stmt = $con->prepare($query);
        $stmt->bind_param("iiiii", $material_id, $negative_qty, $unit_id, $company_id, $user_id);
        $stmt->execute();
        $stmt->close();

        logMessage("Material deducted in rm_purchase for Material ID: $material_id, Qty: $negative_qty, Unit ID: $unit_id");
    }

    // STEP 3: Handle Partial Dispatch - Create a new order if some quantity remains
    $remaining_qty = $original_order_qty - $dispatched_qty;
    if ($remaining_qty > 0) {
        $query = "INSERT INTO order_book (order_id, product_id, qty, customer_id, order_date, company_id, user_id) 
                VALUES (?, ?, ?, ?, NOW(), ?, ?)";
        $stmt = $con->prepare($query);
        $stmt->bind_param("iiiiii", $order_id, $product_id, $remaining_qty, $customer_id, $company_id, $user_id);
        $stmt->execute();
        $stmt->close();
        logMessage("Partial order recorded in order_book for Remaining Qty: $remaining_qty, order_id: $order_id");
    }

    logMessage("===== Sales Processing Completed Successfully for order_id: $order_id =====");
    echo json_encode(["success" => "All sales processed successfully.", "order_id" => $order_id]);
    }   
}
else {
    logMessage("Error: Invalid request method.");
    die(json_encode(["error" => "Invalid request. Only POST allowed."]));
}
?>
<?php
require_once "../../db.php";
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$company_id = 6;
$user_id = 6;
// $company_id = $_SESSION['company_id'];
// $user_id = $_SESSION['user_id'];

$log_file = '../../logs/app.log';

function logMessage($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

function calculateOrderMaterialRequirement($order_id) {
    global $con, $company_id, $user_id;

    if (!$con) {
        logMessage("Database connection failed: " . mysqli_connect_error());
        die(json_encode(["error" => "Database connection failed."]));
    }

    try {
        logMessage("Processing order ID: $order_id");

        // Step 1: Fetch product_id and qty from order_book
        $query = "SELECT product_id, qty FROM order_book WHERE id = ? AND company_id = ? AND user_id = ?";
        $stmt = $con->prepare($query);
        if (!$stmt) {
            logMessage("Query preparation failed (fetching order details): " . $con->error);
            die(json_encode(["error" => "Database error."]));
        }
        $stmt->bind_param("iii", $order_id, $company_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $orderData = $result->fetch_assoc();
        $stmt->close();

        if (!$orderData) {
            logMessage("No order found for order_id '$order_id'.");
            die(json_encode(["error" => "Invalid order ID."]));
        }

        $product_id = $orderData['product_id'];
        $order_qty = $orderData['qty'];

        logMessage("Order contains product_id: $product_id, quantity: $order_qty");

        // Step 2: Get product name
        $query = "SELECT product_name FROM finished_products WHERE id = ? AND company_id = ? AND user_id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("iii", $product_id, $company_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $productRow = $result->fetch_assoc();
        $stmt->close();

        if (!$productRow) {
            logMessage("No product found for product_id '$product_id'.");
            die(json_encode(["error" => "Product not found."]));
        }

        $product_name = strtolower(str_replace(' ', '_', $productRow['product_name']));
        logMessage("Product name: $product_name");

        // Step 3: Fetch materials and qty per unit
        $query = "SELECT material, qty, unit FROM `$product_name` WHERE company_id = ? AND user_id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("ii", $company_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $productMaterials = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (!$productMaterials) {
            logMessage("No material data found for product '$product_name'.");
            die(json_encode(["error" => "Product materials not found."]));
        }

        logMessage("Retrieved " . count($productMaterials) . " rows from table '$product_name'");

        // Step 4: Calculate required quantity for each material (including unit)
        $totalRequiredQty = [];
        foreach ($productMaterials as $row) {
            $material = $row['material'];
            $requiredQty = $row['qty'] * $order_qty;
            $unit = $row['unit'];

            // Store required quantity and unit together
            $totalRequiredQty[$material] = [
                'requiredQty' => ($totalRequiredQty[$material]['requiredQty'] ?? 0) + $requiredQty,
                'unit' => $unit
            ];
        }

        logMessage("Required material quantities with units: " . json_encode($totalRequiredQty));

        // Step 5: Fetch material IDs from rm_master
        $materials = array_keys($totalRequiredQty);
        $placeholders = implode(',', array_fill(0, count($materials), '?'));
        $query = "SELECT id, material FROM rm_master WHERE material IN ($placeholders)";
        $stmt = $con->prepare($query);
        if (!$stmt) {
            logMessage("Query preparation failed (fetching material IDs): " . $con->error);
            die(json_encode(["error" => "Database error."]));
        }

        $types = str_repeat('s', count($materials));
        $stmt->bind_param($types, ...$materials);
        $stmt->execute();
        $result = $stmt->get_result();
        

        $materialMap = [];
        while ($row = $result->fetch_assoc()) {
            $materialMap[$row['material']] = $row['id'];
        }
        $stmt->close();

        logMessage("Material IDs: " . json_encode($materialMap));

        // Step 6: Fetch available quantities from rm_purchase
        $material_ids = array_values($materialMap);
        if (!empty($material_ids)) {
            $placeholders = implode(',', array_fill(0, count($material_ids), '?'));
            $query = "SELECT material_id, SUM(qty) AS total_qty FROM rm_purchase 
                      WHERE material_id IN ($placeholders) AND company_id = ? AND user_id = ? 
                      GROUP BY material_id";
            $stmt = $con->prepare($query);
            if (!$stmt) {
                logMessage("Query preparation failed (fetching rm_purchase quantities): " . $con->error);
                die(json_encode(["error" => "Database error."]));
            }

            $types = str_repeat('i', count($material_ids)) . 'ii';
            $params = array_merge($material_ids, [$company_id, $user_id]);

            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $rmData = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            $rmData = [];
        }

        logMessage("Final Inventory Data: " . json_encode($rmData));

        // Step 7: Map available quantities
        $availableQtyMap = [];
        foreach ($rmData as $row) {
            $availableQtyMap[$row['material_id']] = $row['total_qty'];
        }

        // Step 8: Calculate final inventory status (including unit)
        $inventoryStatus = [];
        foreach ($totalRequiredQty as $material => $data) {
            $material_id = $materialMap[$material] ?? null;
            $availableQty = $availableQtyMap[$material_id] ?? 0;
            $difference = $availableQty - $data['requiredQty'];
            $unit = $data['unit'];

            $inventoryStatus[] = [
                'material' => $material,
                'requiredQty' => $data['requiredQty'],
                'availableQty' => $availableQty,
                'difference' => $difference,
                'unit' => $unit  // Include unit in response
            ];
        }

        logMessage("Inventory status for order '$order_id' with units: " . json_encode($inventoryStatus));

        // Return JSON response
        echo json_encode([
            "order_id" => $order_id,
            "product" => $product_name,
            "inventory_status" => $inventoryStatus
        ]);

    } catch (Exception $e) {
        logMessage("Unexpected Error with Order '$order_id': " . $e->getMessage());
        die(json_encode(["error" => "Unexpected error occurred."]));
    }
}

// **Handle API Request** (Supports JSON)
$inputData = json_decode(file_get_contents("php://input"), true);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($inputData['order_id'])) {
    logMessage("API requested with order_id: " . $inputData['order_id']);
    calculateOrderMaterialRequirement($inputData['order_id']);
} else {
    logMessage("Invalid request: Either method is not POST or order_id is missing.");
    die(json_encode(["error" => "Invalid API request."]));
}
?>

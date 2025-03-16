<?php
require_once('../../db.php'); // Include database connection
session_start();
// if (!isset($_SESSION['company_id']) || !isset($_SESSION['user_id'])) {
//     die(json_encode(["error" => "Session variables not set."]));
// }

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$log_file = '../../logs/app.log';
function logMessage($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// $company_id = $_SESSION['company_id'];
// $user_id = $_SESSION['user_id'];

$company_id = 6;
$user_id = 6;
/**
 * Function to calculate total quantity for given products.
 */
function calculateTotalQuantity($products) {
    global $con, $company_id, $user_id;

    if (!$con) {
        logMessage("Database connection failed: " . mysqli_connect_error());
        die(json_encode(["error" => "Database connection failed."]));
    }

    $response = [];

    foreach ($products as $product) {
        $product = strtolower(str_replace(' ', '_', $product));

        try {
            logMessage("Processing product: $product");
            // Step 1: Fetch all product IDs for the given product name
            $product_ids = [];
            $query = "SELECT id FROM finished_products WHERE product_name = ? AND company_id = ? AND user_id = ?";
            $stmt = $con->prepare($query);
            if (!$stmt) {
                logMessage("Query preparation failed (fetching product IDs): " . $con->error);
                die(json_encode(["error" => "Database error."]));
            }
            $stmt->bind_param("sii", $product, $company_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $product_ids[] = $row['id'];
            }
            $stmt->close();

            // Log retrieved product IDs
            logMessage("Product IDs for '$product': " . json_encode($product_ids));

            // Step 2: Check if any product IDs were found
            if (empty($product_ids)) {
                logMessage("No product IDs found for '$product'. Skipping quantity calculation.");
                $allQuantity = 0;
            } else {
                // Step 3: Modify query to sum `qty` where product_id is in the $product_ids array
                $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
                $query = "SELECT SUM(qty) FROM order_book WHERE product_id IN ($placeholders) AND company_id = ? AND user_id = ?";
                $stmt = $con->prepare($query);
                if (!$stmt) {
                    logMessage("Query preparation failed (summing qty): " . $con->error);
                    die(json_encode(["error" => "Database error."]));
                }

                // Create parameter types string and bind parameters dynamically
                $types = str_repeat('i', count($product_ids)) . 'ii';
                $params = array_merge($product_ids, [$company_id, $user_id]);

                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $stmt->bind_result($allQuantity);
                $stmt->fetch();
                $stmt->close();
            }

            // Ensure $allQuantity is at least 0
            $allQuantity = $allQuantity ?: 0;

            // Log final quantity
            logMessage("Total quantity for '$product': $allQuantity");

            // Step 2: Retrieve fields from `$product` table
            $query = "SELECT material, qty, unit FROM `$product` WHERE company_id = ? AND user_id = ?";
            $stmt = $con->prepare($query);
            $stmt->bind_param("ii", $company_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $productData = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            if (!$productData) {
                logMessage("No data found for product table '$product'.");
                continue;
            }

            logMessage("Retrieved " . count($productData) . " rows from table '$product'");

            // Step 1: Collect all materials from productData
            $materials = array_unique(array_column($productData, 'material'));

            // Step 2: Fetch all material IDs from rm_master in a single query
            $placeholders = implode(',', array_fill(0, count($materials), '?'));
            $query = "SELECT id, material FROM rm_master WHERE material IN ($placeholders)";
            $stmt = $con->prepare($query);
            if (!$stmt) {
                logMessage("Query preparation failed (fetching material IDs): " . $con->error);
                die(json_encode(["error" => "Database error."]));
            }

            // Bind parameters dynamically
            $types = str_repeat('s', count($materials));
            $stmt->bind_param($types, ...$materials);
            $stmt->execute();
            $result = $stmt->get_result();

            $materialMap = [];
            $material_ids = [];

            while ($row = $result->fetch_assoc()) {
                $material_ids[] = $row['id'];
                $materialMap[$row['material']][] = $row['id'];
            }

            $stmt->close();
            logMessage("Material IDs: " . json_encode($materialMap));

            // Step 3: Calculate required quantities per material
            $totalRequiredQty = [];
            foreach ($productData as $row) {
                $material = $row['material'];
                $requiredQty = $row['qty'] * $allQuantity;
                $totalRequiredQty[$material] = ($totalRequiredQty[$material] ?? 0) + $requiredQty;
            }

            // Step 4: Fetch available quantities from rm_purchase in a single query
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

                // Bind parameters dynamically
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
            logMessage("Retrieved " . count($rmData) . " rows from 'rm_purchase'");

            $inventoryStatus = [];
            logMessage("totalRequiredQty is: " . json_encode($rmData));
            $i = 0;
            foreach ($totalRequiredQty as $material => $requiredQty) {
                $availableQty = 0;
                foreach ($rmData as $rmRow) {
                    if ($rmRow['material_id'] === $material_ids[$i]) {
                        $availableQty = $rmRow['total_qty'];
                        $i++;
                        break;
                    }
                }
                $difference = $availableQty - $requiredQty;

                $inventoryStatus[] = [
                    'material' => $material,
                    'requiredQty' => $requiredQty,
                    'availableQty' => $availableQty,
                    'difference' => $difference
                ];
            }

            logMessage("Inventory status for product '$product': " . json_encode($inventoryStatus));

            // Add product result to the response array
            $response[$product] = $inventoryStatus;

        } catch (Exception $e) {
            logMessage("Unexpected Error with Product '$product': " . $e->getMessage());
            die(json_encode(["error" => "Unexpected error occurred."]));
        }
    }

    // Return final JSON response
    echo json_encode(["products" => $response]);
}

// ---- LISTEN FOR POST REQUEST ---- //
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data from the request
    $inputData = file_get_contents("php://input");
    $decodedData = json_decode($inputData, true);

    if (!isset($decodedData['products']) || !is_array($decodedData['products'])) {
        die(json_encode(["error" => "Invalid product data."]));
    }

    // Call the function to process the products
    calculateTotalQuantity($decodedData['products']);
} else {
    die(json_encode(["error" => "Invalid request. Only POST requests are allowed."]));
}

/**
 * Function to display inventory status in HTML.
 */
function displayInventoryStatus($product, $inventoryStatus) {
    echo "<hr>";
    echo "<h3>Inventory Status for Product: <strong>$product</strong></h3>";
    echo "<table border='1'>
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Required Quantity</th>
                    <th>Available Quantity</th>
                    <th>Difference</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>";

    foreach ($inventoryStatus as $status) {
        echo "<tr>";
        echo "<td>{$status['material']}</td>";
        echo "<td>{$status['requiredQty']}</td>";
        echo "<td>{$status['availableQty']}</td>";
        echo "<td>{$status['difference']}</td>";

        if ($status['difference'] < 0) {
            echo "<td style='color: red;'>You need " . abs($status['difference']) . " more</td>";
        } else {
            echo "<td style='color: green;'>Inventory Competent</td>";
        }

        echo "</tr>";
    }

    echo "</tbody></table><br/>";
}

// Example usage:
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['products'])) {
//     $products = json_decode($_POST['products'], true);
//     if (!is_array($products)) {
//         die(json_encode(["error" => "Invalid product data."]));
//     }
//     calculateTotalQuantity($products);
// } else {
//     die(json_encode(["error" => "Invalid request."]));
// }
?>

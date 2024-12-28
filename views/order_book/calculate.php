<?php
// Ensure the 'logs' directory exists
if (!file_exists('logs')) {
    mkdir('logs', 0777, true);
}

$log_file = 'logs/app.log';

/**
 * Function to log messages to a file with a timestamp.
 */
function logMessage($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

function calculateTotalQuantity($pdo, $products) {
    foreach ($products as $product) {
        $product = strtolower($product);  // Convert product name to lowercase
        try {
            logMessage("Processing product: $product");

            // Step 1: Get the total quantity for this product from the order_book
            $stmt = $pdo->prepare("SELECT SUM(qty) FROM order_book WHERE product_name = ?");
            $stmt->execute([$product]);
            $allQuantity = $stmt->fetchColumn() ?: 0; // Fallback to 0 if no quantity is found
            logMessage("Total quantity for product '$product': $allQuantity");

            // Step 2: Retrieve fields from the product table
            $query = "SELECT material, qty, unit FROM `$product`";
            $productData = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
            logMessage("Retrieved " . count($productData) . " rows from table '$product'");

            // Extract columns from the product data
            $oneProductMaterial = array_column($productData, 'material');
            $oneProductQty = array_column($productData, 'qty');
            $oneProductUnit = array_column($productData, 'unit');

            // Step 3: Accumulate total required quantities for this specific product
            $totalRequiredQty = [];
            foreach ($oneProductMaterial as $index => $material) {
                $requiredQty = $oneProductQty[$index] * $allQuantity;
                if (isset($totalRequiredQty[$material])) {
                    $totalRequiredQty[$material] += $requiredQty; // Accumulate if it already exists
                } else {
                    $totalRequiredQty[$material] = $requiredQty; // Initialize if it doesn't exist
                }
            }
            logMessage("Total required quantities for product '$product': " . json_encode($totalRequiredQty));

            // Step 4: Retrieve available quantities from the rm_purchase table
            $rmQuery = "SELECT material, SUM(qty) AS total_qty, unit FROM rm_purchase GROUP BY material";
            $rmData = $pdo->query($rmQuery)->fetchAll(PDO::FETCH_ASSOC);
            logMessage("Retrieved " . count($rmData) . " rows from 'rm_purchase'");

            $myMaterial = array_column($rmData, 'material');
            $myQty = array_column($rmData, 'total_qty'); // Total quantity for each material

            // Step 5: Compare the required quantities with available quantities in rm_purchase
            $inventoryStatus = [];
            foreach ($totalRequiredQty as $material => $requiredQty) {
                $inventoryIndex = array_search($material, $myMaterial);
                $availableQty = $inventoryIndex !== false ? $myQty[$inventoryIndex] : 0;
                $difference = $availableQty - $requiredQty;

                // Store the inventory status (whether more is needed or inventory is competent)
                $inventoryStatus[] = [
                    'material' => $material,
                    'requiredQty' => $requiredQty,
                    'availableQty' => $availableQty,
                    'difference' => $difference
                ];
            }

            logMessage("Inventory status for product '$product': " . json_encode($inventoryStatus));

            // Step 6: Display the inventory status for this specific product
            displayInventoryStatus($product, $inventoryStatus);

        } catch (PDOException $e) {
            logMessage("Database Error with Product '$product': " . $e->getMessage());
        } catch (Exception $e) {
            logMessage("Unexpected Error with Product '$product': " . $e->getMessage());
        }
    }
}

/**
 * Function to display inventory status (difference between required and available quantity)
 * for a specific product.
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
?>

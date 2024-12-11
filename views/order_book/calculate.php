<?php
function calculateTotalQuantity($pdo, $products) {
    foreach ($products as $product) {
        try {
            // Step 1: Get the total quantity for this product from the order_book
            $stmt = $pdo->prepare("SELECT SUM(qty) FROM order_book WHERE product_name = ?");
            $stmt->execute([$product]);
            $allQuantity = $stmt->fetchColumn();

            // Step 2: Retrieve fields from the product table (assuming product table name is the product name)
            $query = "SELECT material, qty, unit FROM `$product`";
            $productData = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

            $oneProductMaterial = array_column($productData, 'material');
            $oneProductQty = array_column($productData, 'qty');
            $oneProductUnit = array_column($productData, 'unit');

            // Step 3: Get the data from the rm_purchase table
            $rmQuery = "SELECT material, qty, unit FROM rm_purchase";
            $rmData = $pdo->query($rmQuery)->fetchAll(PDO::FETCH_ASSOC);

            $myMaterial = array_column($rmData, 'material');
            $myQty = array_column($rmData, 'qty');
            $myUnit = array_column($rmData, 'unit');

            // Step 4: Calculate totalMaterial, totalQty, and totalUnit
            $totalMaterial = $oneProductMaterial;
            $totalQty = array_map(function($qty) use ($allQuantity) {
                return $qty * $allQuantity;
            }, $oneProductQty);
            $totalUnit = $oneProductUnit;

            // Step 5: Display the results (instead of storing or updating them in the database)
            displayTotalData($product, $totalMaterial, $totalQty, $totalUnit);

        } catch (PDOException $e) {
            error_log("Database Error with Product '$product': " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Unexpected Error with Product '$product': " . $e->getMessage());
        }
    }
}

/**
 * Function to display the calculated total data (for viewing purposes only)
 */
function displayTotalData($product, $totalMaterial, $totalQty, $totalUnit) {
    echo "<hr>";
    echo "<h3>Totals for Product: $product</h3>";
    echo "<table border='1'>
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody>";

    // Display the totals in a table
    foreach ($totalMaterial as $index => $material) {
        echo "<tr>
                <td>{$material}</td>
                <td>{$totalQty[$index]}</td>
                <td>{$totalUnit[$index]}</td>
              </tr>";
    }

    echo "</tbody></table><br/>";
}
?>

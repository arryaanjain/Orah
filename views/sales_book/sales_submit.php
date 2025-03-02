<?php

// Database connection
try {
    $company_name = $_POST['company_name'] ?? '';
    $pdo = new PDO("mysql:host=localhost", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("USE `$company_name`");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if required data is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_name']) && !empty($_POST['order_name'])) {
    $orderData = json_decode($_POST['order_name'], true);

    if (!$orderData) {
        die("Invalid order data.");
    }

    $productName = $orderData['product_name'];
    $qtyRequired = $orderData['qty'];
    $place = $orderData['place'];

    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Fetch `order_date` and `customer_id` from `order_book`
        $fetchOrderDetails = $pdo->prepare("
            SELECT order_date, customer_id 
            FROM order_book 
            WHERE product_name = :product_name
        ");
        $fetchOrderDetails->execute([':product_name' => $productName]);
        $orderDetails = $fetchOrderDetails->fetch(PDO::FETCH_ASSOC);

        if (!$orderDetails) {
            throw new Exception("No matching order found in order_book for product_name: $productName");
        }

        $orderDate = $orderDetails['order_date'];
        $customerId = $orderDetails['customer_id'];

        // Insert into sales_book
        $insertSalesBook = $pdo->prepare("
            INSERT INTO sales_book (order_date, customer_id, product_name, qty, created_at)
            VALUES (:order_date, :customer_id, :product_name, :qty, NOW())
        ");
        $insertSalesBook->execute([
            ':order_date' => $orderDate,
            ':customer_id' => $customerId,
            ':product_name' => $productName,
            ':qty' => $qtyRequired,
        ]);

        // Fetch matching entries from rm_purchase
        $fetchRmPurchase = $pdo->prepare("
            SELECT id, qty
            FROM rm_purchase
            WHERE product_name = :product_name
            ORDER BY created_at ASC
        ");
        $fetchRmPurchase->execute([':product_name' => $productName]);
        $rmEntries = $fetchRmPurchase->fetchAll(PDO::FETCH_ASSOC);

        $remainingQty = $qtyRequired;

        foreach ($rmEntries as $entry) {
            if ($remainingQty <= 0) break;

            $rmId = $entry['id'];
            $rmQty = $entry['qty'];

            if ($rmQty <= $remainingQty) {
                // Fully consume this entry
                $deleteEntry = $pdo->prepare("DELETE FROM rm_purchase WHERE id = :id");
                $deleteEntry->execute([':id' => $rmId]);
                $remainingQty -= $rmQty;
            } else {
                // Partially consume this entry
                $updateEntry = $pdo->prepare("UPDATE rm_purchase SET qty = :qty WHERE id = :id");
                $updateEntry->execute([':qty' => $rmQty - $remainingQty, ':id' => $rmId]);
                $remainingQty = 0;
            }
        }

        // If all is good, commit the transaction
        $pdo->commit();

        echo "Sales order processed successfully.";
    } catch (Exception $e) {
        // Rollback on error
        $pdo->rollBack();
        die("Error processing sales order: " . $e->getMessage());
    }
} else {
    die("Invalid request.");
}
?>

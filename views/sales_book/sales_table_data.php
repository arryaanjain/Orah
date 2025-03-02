<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dsn = "mysql:host=localhost";
    $username = "root";
    $password = "";

    // Define the log file location
    $logFile = __DIR__ . '/logs/error_log.txt';

    // Custom function to log errors
    function logError($message, $file) {
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[$timestamp] $message\n";
        error_log($formattedMessage, 3, $file); // Log message to file
    }

    try {
        $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->exec("USE `{$_POST['company_name']}`");

        $product_name = strtolower($_POST['product_name']) ?? '';

        $qty = (int)($_POST['qty'] ?? 0);

        // Log incoming POST data (useful for debugging request issues)
        logError("Received POST data: " . json_encode($_POST), $logFile);

        if (!empty($product_name) && $qty > 0) {
            // Fetch product details
            $stmt = $pdo->prepare("SELECT id, material, qty AS oneQty, unit FROM `$product_name`");
            $stmt->execute();
            $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Log materials fetched from the product table
            logError("Fetched materials for product `$product_name`: " . json_encode($materials), $logFile);

            // Fetch available inventory from rm_purchase
            $inventoryStmt = $pdo->query("SELECT id, material, SUM(qty) AS availableQty, unit FROM rm_purchase GROUP BY material");
            $inventory = $inventoryStmt->fetchAll(PDO::FETCH_ASSOC);

            // Log inventory details
            logError("Fetched inventory from `rm_purchase`: " . json_encode($inventory), $logFile);

            // Convert inventory to a key-value pair for easy lookup
            $inventoryMap = [];
            foreach ($inventory as $item) {
                $inventoryMap[$item['material']] = $item;
            }

            // Build the table rows
            foreach ($materials as $material) {
                $requiredQty = $material['oneQty'] * $qty;
                $availableQty = $inventoryMap[$material['material']]['availableQty'] ?? 0;

                echo "<tr>
                        <td>" . htmlspecialchars($material['material']) . "</td>
                        <td contenteditable='true' class='editable requiredQty'>" . htmlspecialchars($requiredQty) . "</td>
                        <td contenteditable='true' class='editable availableQty'>" . htmlspecialchars($availableQty) . "</td>
                        <td>" . htmlspecialchars($material['unit']) . "</td>
                        <td>
                            <button type='button' class='btn btn-warning saveEdit' data-bs-toggle='modal' data-bs-target='#editModal" . $material['id'] . "'>Edit</button>
                        </td>
                    </tr>";

                // Modal for editing material record
                echo "<div class='modal fade' id='editModal" . $material['id'] . "' tabindex='-1' aria-labelledby='editModalLabel' aria-hidden='true'>
                        <div class='modal-dialog'>
                            <div class='modal-content'>
                                <div class='modal-header'>
                                    <h5 class='modal-title'>Edit Material Record</h5>
                                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                </div>
                                <div class='modal-body'>
                                    <form method='POST' action='/PIMS/views/sales_book/sales_table_edit.php'>
                                        <input type='hidden' name='edit_record_id' value='" . $material['id'] . "'>
                                        <div class='mb-3'>
                                            <label for='material' class='form-label'>Material Name</label>
                                            <input type='text' class='form-control' name='material' value='" . htmlspecialchars($material['material']) . "' required>
                                        </div>
                                        <div class='mb-3'>
                                            <label for='qty' class='form-label'>Quantity</label>
                                            <input type='number' class='form-control' name='qty' value='" . htmlspecialchars($material['oneQty']) . "' required>
                                        </div>
                                        <div class='mb-3'>
                                            <label for='unit' class='form-label'>Unit</label>
                                            <input type='text' class='form-control' name='unit' value='" . htmlspecialchars($material['unit']) . "' required>
                                        </div>
                                        <button type='submit' class='btn btn-primary'>Save changes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>";
            }
        } else {
            logError("Error: Invalid product name or quantity.", $logFile);
            echo "<p>Error: Invalid product name or quantity.</p>";
        }
    } catch (PDOException $e) {
        // Log detailed error information
        logError("Database Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine(), $logFile);
        echo "Error: A problem occurred while processing your request.";
    } catch (Exception $e) {
        // Log general errors
        logError("General Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine(), $logFile);
        echo "Error: An unexpected issue occurred.";
    }
}

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

        $updatedData = $_POST['updatedData']; // This should be an array of updates from the front-end

        // Log incoming POST data (useful for debugging request issues)
        logError("Received POST data: " . json_encode($_POST), $logFile);

        foreach ($updatedData as $row) {
            $material = $row['material'];
            $newRequiredQty = (float)$row['requiredQty'];
            $newAvailableQty = (float)$row['availableQty'];
            $unit = $row['unit'];

            // Update the required qty for the material in the relevant table
            $updateStmt = $pdo->prepare("UPDATE `:product_name` SET qty = :newQty WHERE material = :material");
            $updateStmt->bindValue(':product_name', $_POST['product_name']);
            $updateStmt->bindValue(':newQty', $newRequiredQty);
            $updateStmt->bindValue(':material', $material);
            $updateStmt->execute();

            // Log each update query for tracking purposes
            logError("Updated material: " . json_encode([ 
                'material' => $material, 
                'newRequiredQty' => $newRequiredQty, 
                'unit' => $unit 
            ]), $logFile);
        }

        echo json_encode(["status" => "success", "message" => "Data updated successfully."]);
    } catch (PDOException $e) {
        // Log detailed error information
        logError("Database Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine(), $logFile);
        echo json_encode(["status" => "error", "message" => "A problem occurred while processing your request."]);
    } catch (Exception $e) {
        // Log general errors
        logError("General Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine(), $logFile);
        echo json_encode(["status" => "error", "message" => "An unexpected issue occurred."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

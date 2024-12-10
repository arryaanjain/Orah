<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log the start of the request
file_put_contents('debug.log', "Request started: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// Check if all required fields are present in the POST request
if (isset($_POST['material_id'], $_POST['material'], $_POST['qty'], $_POST['unit'], $_POST['company_name'], $_POST['product_name'])) {
    $company_name = $_POST['company_name'];
    $product_name = $_POST['product_name'];  // Use product name for the table
    $materialId = $_POST['material_id'];
    $material = $_POST['material'];
    $qty = $_POST['qty'];
    $unit = $_POST['unit'];

    file_put_contents('debug.log', "Received data: company_name: $company_name, product_name: $product_name, material_id: $materialId, material: $material, qty: $qty, unit: $unit\n", FILE_APPEND);

    // Create a connection to the company-specific database
    $conn = new mysqli("localhost", "root", "", $company_name);

    // Check the connection
    if ($conn->connect_error) {
        file_put_contents('debug.log', "DB Connection failed: " . $conn->connect_error . "\n", FILE_APPEND);
        die("Connection failed: " . $conn->connect_error);
    }
    file_put_contents('debug.log', "Connected to database: $company_name\n", FILE_APPEND);

    // Prepare the update query using the product name for the table
    $updateQuery = "UPDATE `$product_name` SET material = ?, qty = ?, unit = ? WHERE id = ?";

    // Prepare and bind parameters
    if ($stmt = $conn->prepare($updateQuery)) {
        $stmt->bind_param("sisi", $material, $qty, $unit, $materialId);
        file_put_contents('debug.log', "Prepared statement for update query: $updateQuery\n", FILE_APPEND);

        // Execute the query
        if ($stmt->execute()) {
            // Log success
            file_put_contents('debug.log', "Material updated successfully for material_id: $materialId\n", FILE_APPEND);
            // Send a success response
            echo json_encode(["status" => "success", "message" => "Material updated successfully"]);
        } else {
            // Log failure
            file_put_contents('debug.log', "Failed to update material for material_id: $materialId\n", FILE_APPEND);
            // Send an error response
            echo json_encode(["status" => "error", "message" => "Failed to update material"]);
        }

        $stmt->close();
    } else {
        // Error preparing the statement
        file_put_contents('debug.log', "Failed to prepare SQL statement for update\n", FILE_APPEND);
        echo json_encode(["status" => "error", "message" => "Failed to prepare SQL statement"]);
    }

    // Close the connection
    $conn->close();
    file_put_contents('debug.log', "Database connection closed\n", FILE_APPEND);
} else {
    // Missing data in the request
    file_put_contents('debug.log', "Missing required fields in the request\n", FILE_APPEND);
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
}

file_put_contents('debug.log', "Request ended: " . date('Y-m-d H:i:s') . "\n\n", FILE_APPEND);
?>

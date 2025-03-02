<?php
// Check if the required fields are present in the POST request
if (isset($_POST['material_id'], $_POST['company_name'], $_POST['product_name'])) {
    $company_name = $_POST['company_name'];
    $product_name = $_POST['product_name'];  // Use product name for the table
    $materialId = $_POST['material_id'];

    // Create a connection to the company-specific database
    $conn = new mysqli("localhost", "root", "", $company_name);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare the delete query using the product name for the table
    $deleteQuery = "DELETE FROM `$product_name` WHERE id = ?";

    // Prepare and bind parameters
    if ($stmt = $conn->prepare($deleteQuery)) {
        $stmt->bind_param("i", $materialId);

        // Execute the query
        if ($stmt->execute()) {
            // Send a success response
            echo json_encode(["status" => "success", "message" => "Material deleted successfully"]);
        } else {
            // Send an error response
            echo json_encode(["status" => "error", "message" => "Failed to delete material"]);
        }

        $stmt->close();
    } else {
        // Error preparing the statement
        echo json_encode(["status" => "error", "message" => "Failed to prepare SQL statement"]);
    }

    // Close the connection
    $conn->close();
} else {
    // Missing data in the request
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
}
?>

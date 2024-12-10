<?php

$conn = new mysqli("localhost", "root", "", $company_name);

// Check the connection
if ($conn->connect_error) {
    die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
}

// Query to fetch all product names from finished_products
$productQuery = "SELECT id, product_name FROM finished_products";
$productResult = $conn->query($productQuery);

// Array to hold all product data
$productsData = [];

if ($productResult->num_rows > 0) {
    // Fetch product names and materials for each product
    while ($row = $productResult->fetch_assoc()) {
        $productName = $row['product_name'];
        
        // Query the product-specific table for material, qty, and unit
        $sanitizedProductName = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($productName));
        $materialQuery = "SELECT material, qty, unit FROM `$sanitizedProductName`";
        $materialResult = $conn->query($materialQuery);
        
        $materials = [];
        if ($materialResult->num_rows > 0) {
            while ($materialRow = $materialResult->fetch_assoc()) {
                $materials[] = $materialRow;
            }
        }

        // Add to products data array
        $productsData[] = [
            'product_name' => $productName,
            'materials' => $materials
        ];
    }
}
?>

<!-- Content Section -->
<div class="container mt-4">
    <h3 class="mb-4">Total Products and Materials</h3>

    <!-- Display products table -->
    <?php if (!empty($productsData)): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Material</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productsData as $product): ?>
                    <?php
                        // Get the number of materials for this product
                        $numMaterials = count($product['materials']);
                    ?>
                    <!-- Loop through each material and display rows for the same product_name -->
                    <?php foreach ($product['materials'] as $index => $material): ?>
                        <tr>
                            <!-- Display product_name only in the first row for the product -->
                            <?php if ($index == 0): ?>
                                <td rowspan="<?php echo $numMaterials; ?>"><?php echo $product['product_name']; ?></td>
                            <?php endif; ?>
                            <td><?php echo $material['material']; ?></td>
                            <td><?php echo $material['qty']; ?></td>
                            <td><?php echo $material['unit']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No products found.</p>
    <?php endif; ?>
</div>

<?php
$conn->close();
?>

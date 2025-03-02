<?php
require('views/partials/head.php');
require('views/partials/navbar.php');
require('views/partials/banner.php');

$company_name = $_SESSION['company_name'];

// Create a connection to the company-specific database
$conn = new mysqli("localhost", "root", "", $company_name);

// Check the connection
if ($conn->connect_error) {
    die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
}

// Query to fetch all product names from finished_products
$productQuery = "SELECT id, product_name FROM finished_products";
$productResult = $conn->query($productQuery);

// Handle form submission for editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_product_id'])) {
        $productId = $_POST['edit_product_id'];
        $newProductName = $_POST['product_name'];
        $sanitizedProductName = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($newProductName));
        $oldProductName = $_POST['old_product_name'];

        // **1. Update the product name in the finished_products table**
        $updateProductQuery = "UPDATE finished_products SET product_name = ? WHERE id = ?";
        $stmt = $conn->prepare($updateProductQuery);
        $stmt->bind_param("si", $newProductName, $productId);
        $stmt->execute();
        $stmt->close();

        // **2. Rename the product-specific table if the name has changed**
        if ($newProductName !== $oldProductName) {
            $oldSanitizedProductName = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($oldProductName));
            $renameTableQuery = "RENAME TABLE `$oldSanitizedProductName` TO `$sanitizedProductName`";
            $conn->query($renameTableQuery);
        }

        // **3. Update materials in the renamed product-specific table**
        $updateProductTableQuery = "UPDATE `$sanitizedProductName` SET material = ?, qty = ?, unit = ? WHERE id = ?";
        for ($i = 0; $i < count($_POST['material']); $i++) {
            $stmtMaterial = $conn->prepare($updateProductTableQuery);
            $stmtMaterial->bind_param("sisi", $_POST['material'][$i], $_POST['qty'][$i], $_POST['unit'][$i], $_POST['material_id'][$i]);
            $stmtMaterial->execute();
        }

        echo '<div class="alert alert-success" role="alert">Product and its materials updated successfully!</div>';
    }
}
?>

<!-- Content Section -->
<div class="container mt-4">
    <h3 class="mb-4">Update Materials for Products</h3>

    <!-- Alert Messages -->
    <?php if (isset($_POST['edit_product_id'])): ?>
        <div class="alert alert-success" role="alert">
            Product and materials updated successfully!
        </div>
    <?php endif; ?>

    <form method="POST" id="updateProductForm">
        <!-- Company Name (hidden input) -->
        <input type="hidden" id="companyName" name="company_name" value="<?php echo $company_name; ?>">

        <div class="mb-4">
            <h4>Select a Product to Edit</h4>
            <select class="form-control mb-3" name="edit_product_id" required>
                <option value="">-- Select Product --</option>
                <?php if ($productResult->num_rows > 0): ?>
                    <?php while ($row = $productResult->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>" data-product-name="<?php echo $row['product_name']; ?>"><?php echo $row['product_name']; ?></option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="">No products available</option>
                <?php endif; ?>
            </select>
        </div>

        <h5 class="mb-3">Product</h5>
        <input type="text" name="product_name" id="product_name" class="form-control mb-3" required readonly>

        <h5 class="mb-3">Materials and Units</h5>
        <table class="table table-bordered" id="materials-table">
            <thead>
                <tr>
                    <th>Material Name</th>
                    <th>QTY</th>
                    <th>Unit</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="materials-container">
                <!-- Dynamic content will be populated here via AJAX -->
            </tbody>
        </table>

        <!-- <button type="submit" class="btn btn-primary mt-4">Save Changes</button> -->
    </form>
</div>

<!-- Bottom Section: Include display_products.php -->
<div class="container mt-4">

    <?php include('display_products.php'); ?>
</div>

<script src="views/records/product_portfolio/script.js"></script>

<?php
$conn->close();
require('views/partials/footer.php');
?>

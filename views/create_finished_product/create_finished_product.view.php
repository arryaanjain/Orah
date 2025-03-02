<?php
require 'db.php';
require 'views/partials/head.php';
require 'views/partials/navbar.php';
require 'views/partials/banner.php';
?>

<link rel="stylesheet" href="views/create_finished_product/style.css">
<script src="views/create_finished_product/script_finished_product.js"></script>

<div class="container mt-4">
    <h3>Create Finished Product</h3>

    <!-- Display Success/Failure Message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?>" role="alert">
            <?= $_SESSION['message']; unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
        </div>
    <?php endif; ?>

    <form action="views/create_finished_product/submit_finished_product.php" method="POST">
        <input type="hidden" id="companyName" name="company_name" value="<?= htmlspecialchars($_SESSION['company_name']) ?>">
        
        <div class="form-group">
            <label for="productName">Product Name</label>
            <input type="text" id="productName" name="product_name" class="form-control" placeholder="Enter product name" required>
        </div>

        <div class="form-group">
            <label for="description">Product Description</label>
            <textarea id="description" name="description" class="form-control" placeholder="Enter product description" required></textarea>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Material Name</th>
                    <th>QTY</th>
                    <th>Unit</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr>
                    <td><input type="text" class="autocomplete material" name="material[]"></td>
                    <td><input type="number" class="quantity" name="qty[]"></td>
                    <td><input type="text" class="autocomplete unit" name="unit[]"></td>
                    <td><button type="button" class="deleteRowBtn">Delete</button></td>
                </tr>
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary">Create Product</button>
    </form>

    <button type="button" id="addRowBtn" class="btn btn-secondary mt-3">Add Row</button>
    <a href="index.php" class="btn btn-info mt-3">Back</a>
</div>

<?php require 'views/partials/footer.php'; ?>

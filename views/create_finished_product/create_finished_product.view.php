<?php
require('views/partials/head.php');
require('views/partials/navbar.php');
require('views/partials/banner.php');
?>

<link rel="stylesheet" href="views/create_finished_product/style.css">
<script src="views/create_finished_product/script_finished_product.js"></script>

<div class="container mt-4">
    <h3>Create Finished Product</h3>

    <form action="/PIMS/views/create_finished_product/submit_finished_product.php" method="POST">
     <input type="hidden" id="companyName" name="company_name" value=<?= htmlspecialchars($_SESSION['company_name'])?>> 
        <!-- Finished Product Name -->
        <div class="form-group">
            <label for="productName">Product Name</label>
            <input type="text" id="productName" name="product_name" class="form-control" placeholder="Enter product name" required>
        </div>

        <!-- Table for Material Requirements -->
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
                <!-- Initial Row -->
                <tr>
                    <td><input type="text" class="autocomplete material" name="material[]"></td>
                    <td><input type="number" class="quantity" name="qty[]"></td>
                    <td><input type="text" class="autocomplete unit" name="unit[]"></td>
                    <td><button type="button" class="deleteRowBtn">Delete</button></td>
                </tr>
            </tbody>
        </table>

        <button type="submit" id="submit" class="btn btn-primary">Create Product</button>
    </form>

    <!-- Add Row Button -->
    <button type="button" id="addRowBtn" class="btn btn-secondary mt-3">Add Row</button>
    <a href="index.php" class="btn btn-info mt-3">Back</a>
</div>

<?php
require('views/partials/footer.php');
?>

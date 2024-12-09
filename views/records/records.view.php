<?php
require ('views/partials/head.php');
require ('views/partials/navbar.php');
require ('views/partials/banner.php');
?>

<!-- Content section starts here -->
<div class="container mt-4">
    <div class="row">
        <!-- First column: Inserted Materials -->
        <div class="col-md-4 border-end">
            <h4>Inserted Materials</h4>
            <a href="inserted_materials.php" class="btn btn-primary w-100">View Inserted Materials</a>
        </div>

        <!-- Second column: Sales -->
        <div class="col-md-4 border-end">
            <h4>My Sales</h4>
            <a href="my_sales.php" class="btn btn-primary w-100">View My Sales</a>
        </div>

        <!-- Third column: Orders -->
        <div class="col-md-4">
            <h4>My Orders</h4>
            <a href="my_orders.php" class="btn btn-primary w-100">View My Orders</a>
        </div>
    </div>
</div>

<?php
require ('views/partials/footer.php');
?>

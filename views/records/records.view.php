<?php
require('views/partials/head.php');
require('views/partials/navbar.php');
require('views/partials/banner.php');
?>

<!-- Content section starts here -->
<div class="container mt-4">
    <div class="row">
        <!-- First column: Inserted Materials -->
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4>Inserted Materials</h4>
                    <a href="inserted_materials.php" class="btn btn-primary w-100">View Inserted Materials</a>
                </div>
            </div>
        </div>

        <!-- Second column: Sales -->
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4>My Sales</h4>
                    <a href="my_sales.php" class="btn btn-success w-100">View My Sales</a>
                </div>
            </div>
        </div>

        <!-- Third column: Orders -->
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4>My Orders</h4>
                    <a href="my_orders.php" class="btn btn-info w-100">View My Orders</a>
                </div>
            </div>
        </div>

        <!-- Fourth column: Material Purchase -->
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5>View Material Purchase</h5>
                    <a href="my_rm_purchase.php" class="btn btn-warning w-100">View Material Purchase</a>
                </div>
            </div>
        </div>
        <!--Fifth: Product Portfolio -->
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5>Product Portfolio</h5>
                    <a href="product_portfolio.php" class="btn btn-warning w-100">Product Portfolio</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require('views/partials/footer.php');
?>

<?php
require('views/partials/head.php');
require('views/partials/navbar.php');
require('views/partials/banner.php');
?>
<script src="views/sales_book/sales_script.js"></script>

<div class="container mt-4">
    <!-- Top Half: Sales Book Input -->
    <div class="row">
        <div class="col-md-12">
            <h3>Sales Book</h3>
            <form action="/PIMS/views/sales_book/sales_submit.php" method="POST">
                <input type="hidden" id="companyName" name="company_name" value="<?= htmlspecialchars($_SESSION['company_name']) ?>">

                <!-- Dropdown for Order Name -->
                <label for="orderName">Select Order Name:</label>
                <select id="orderName" name="order_name" class="form-control">
                    <option value="">Select Order</option>
                    <?php
                    // Fetch available order names dynamically
                    $pdo = new PDO("mysql:host=localhost", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                    $pdo->exec("USE {$_SESSION['company_name']}");
                    $stmt = $pdo->query("SELECT ob.qty, ob.product_name, c.place, CONCAT(ob.qty, 'x ', ob.product_name, ' @ ', c.place) AS order_name 
                                         FROM order_book ob 
                                         JOIN customers c ON ob.customer_id = c.id");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . htmlspecialchars(json_encode($row)) . "'>" . htmlspecialchars($row['order_name']) . "</option>";
                    }
                    ?>
                </select>

                <!-- Table to Display Order Details -->
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Required Quantity</th>
                            <th>Available Quantity</th>
                            <th>Unit</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="salesTableBody">
                        <!-- Dynamic rows populated via JavaScript -->
                    </tbody>
                </table>

                <!-- Submit Button -->
                <button type="submit" id="submitSales" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>

<?php require('views/partials/footer.php'); ?>

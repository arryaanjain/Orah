<?php
require('views/partials/head.php');
require('views/partials/navbar.php');
require('views/partials/banner.php');
require('views/order_book/calculate.php');

require 'db.php';

// Ensure required session variables are set
if (!isset($_SESSION['company_id']) || !isset($_SESSION['user_id'])) {
    die("Unauthorized access!");
}

if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']); 
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}


$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];

header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<script src="views/order_book/order_script.js"></script>

<div class="container mt-4">
    <!-- Top Half: Dynamic Table Section -->
    <div class="row">
        <div class="col-md-12">
            <h3>Order Book Details</h3>
            <form action="/PIMS/views/order_book/order_submit.php" method="POST">

                <!-- Table for order book details -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Order Date</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Billing Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td><input type="date" class="orderDate" name="order_date[]" required></td>
                            <td><input type="text" class="autocomplete product" name="product_name[]" required></td>
                            <td><input type="number" class="quantity" name="qty[]" required></td>
                            <td><input type="text" class="autocomplete billingName" name="billing_name[]" required></td>
                            <td><button type="button" class="deleteRowBtn">Delete</button></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Submit Button -->
                <button type="submit" id="submit" class="btn btn-primary">Submit</button>
            </form>

            <!-- Add Row Button -->
            <button type="button" id="addRowBtn" class="btn btn-secondary mt-3">Add Row</button>
            <!-- Back Button -->
            <a href="index.php" class="btn btn-info mt-3">Back</a>
        </div>
    </div>

    <hr>

    <!-- Table for displaying all submitted data -->
    <div class="row">
        <div class="col-md-12">
            <h3>Submitted Orders</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Order Date</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Billing Name</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $query = "SELECT ob.order_date, fp.product_name, ob.qty, c.billing_name 
                        FROM order_book ob
                        JOIN finished_products fp ON ob.product_id = fp.id
                        JOIN customers c ON ob.customer_id = c.id
                        WHERE ob.company_id = ? AND ob.user_id = ?";

                $stmt = mysqli_prepare($con, $query);
                mysqli_stmt_bind_param($stmt, "ii", $company_id, $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['order_date']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['qty']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['billing_name']) . "</td>";
                    echo "</tr>";
                }
                mysqli_stmt_close($stmt);
                ?>

                </tbody>
            </table>
        </div>

        <?php    
            // Fetch unique products belonging to this company & user
            $productQuery = "SELECT DISTINCT fp.product_name 
                            FROM order_book ob
                            JOIN finished_products fp ON ob.product_id = fp.id
                            WHERE ob.company_id = ? AND ob.user_id = ?";
            $stmtProduct = mysqli_prepare($con, $productQuery);
            mysqli_stmt_bind_param($stmtProduct, "ii", $company_id, $user_id);
            mysqli_stmt_execute($stmtProduct);
            $resultProduct = mysqli_stmt_get_result($stmtProduct);

            // Fetch product names into an array
            $products = [];
            while ($row = mysqli_fetch_assoc($resultProduct)) {
                $products[] = $row['product_name'];
            }
            mysqli_stmt_close($stmtProduct);

            // Call the function if products exist
            if (!empty($products)) {
                calculateTotalQuantity($con, $products);
            }       
            ?>

    </div>
</div>

<?php
mysqli_close($con);
require('views/partials/footer.php');
?>

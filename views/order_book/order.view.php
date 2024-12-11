<?php
require('views/partials/head.php');
require('views/partials/navbar.php');
require('views/partials/banner.php');

// Include the calculate.php file for total material, quantity, and unit calculations
require('views/order_book/calculate.php');
?>
<script src="views/order_book/order_script.js"></script>

<div class="container mt-4">
    <!-- Top Half: Dynamic Table Section -->
    <div class="row">
        <div class="col-md-12">
            <h3>Order Book Details</h3>
            <form action="/PIMS/views/order_book/order_submit.php" method="POST">
                <input type="hidden" id="companyName" name="company_name" value="<?= htmlspecialchars($_SESSION['company_name']) ?>">

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
                            <td><input type="text" class="autocomplete product" name="column1[]" required></td>
                            <td><input type="number" class="quantity" name="column2[]" required></td>
                            <td><input type="text" class="billingName" name="billing_name[]" required></td>
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
                    $dsn = "mysql:host=localhost";
                    $username = "root";
                    $password = "";
                    try {
                        $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                        $pdo->exec("USE {$_SESSION['company_name']}");

                        // Fetch and display all order book entries
                        $stmt = $pdo->query("SELECT order_date, product_name, qty, billing_name FROM order_book ob JOIN customers c ON ob.customer_id = c.id");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['order_date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['qty']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['billing_name']) . "</td>";
                            echo "</tr>";
                        }

                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>Database error occurred. Please try again later.</div>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php    
            // Calculate and display total material, qty, and unit
            $productStmt = $pdo->query("SELECT DISTINCT product_name FROM order_book");
            $products = $productStmt->fetchAll(PDO::FETCH_COLUMN);

            // Call the calculateTotalQuantity function if products are available
            if (!empty($products)) {
                foreach ($products as $product) {
                    calculateTotalQuantity($pdo, [$product]);  // Pass product to calculate for each product
                }
            }
        ?>
    </div>
</div>

<?php
// Close the connection
if (isset($conn)) {
    $conn->close();
}
require('views/partials/footer.php');
?>

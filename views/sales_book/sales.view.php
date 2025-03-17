<?php
require('views/partials/head.php');
require('views/partials/navbar.php');
require('views/partials/banner.php');

require 'db.php';

// Ensure required session variables are set
if (!isset($_SESSION['company_id']) || !isset($_SESSION['user_id'])) {
    die("Unauthorized access!");
}

$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];

// Display success or error messages
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']); 
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
?>

<script src="views/sales_book/sales_script.js"></script>

<div class="container mt-4">
    <!-- Sales Entry Form -->
    <div class="row">
        <div class="col-md-12">
            <h3>Sales Book Entry</h3>
            <form id = "salesForm" action="/PIMS/views/sales_book/sales_submit.php" method="POST">
                
                <!-- Order Ticket Dropdown -->
                <div class="mb-3">
                    <label for="orderTicket" class="form-label">Select Order Ticket</label>
                    <select id="orderTicket" name="order_ticket" class="form-control">
                        <option value="">Select Order</option>
                        <?php
                        $query = "SELECT ob.id, c.billing_name, fp.product_name, ob.qty
                                  FROM order_book ob
                                  JOIN finished_products fp ON ob.product_id = fp.id
                                  JOIN customers c ON ob.customer_id = c.id
                                  WHERE ob.company_id = ? AND ob.user_id = ?";

                        $stmt = mysqli_prepare($con, $query);
                        mysqli_stmt_bind_param($stmt, "ii", $company_id, $user_id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);

                        while ($row = mysqli_fetch_assoc($result)) {
                            $ticketValue = json_encode([
                                'id' => $row['id'],
                                'billing_name' => $row['billing_name'],
                                'product_name' => $row['product_name'],
                                'qty' => $row['qty']
                            ]);
                            echo "<option value='" . htmlspecialchars($ticketValue) . "'>{$row['billing_name']} - {$row['product_name']}</option>";
                        }
                        mysqli_stmt_close($stmt);
                        ?>
                    </select>
                </div>

                <!-- Sales Table -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Sales Date</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Billing Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="salesTableBody">
                        <tr>
                            <td><input type="date" class="salesDate" name="sales_date[]" required></td>
                            <td><input type="text" class="product_name" name="product_name[]" required readonly></td>
                            <td><input type="number" class="quantity" name="qty[]" required readonly></td>
                            <td><input type="text" class="billingName" name="billing_name[]" required readonly></td>
                            <td><button type="button" class="deleteRowBtn">Delete</button></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Submit Button -->
                <button type="submit" id="submit" class="btn btn-primary">Submit</button>
            </form>

            <!-- Add Row Button -->
            <button type="button" id="addSalesRowBtn" class="btn btn-secondary mt-3">Add Row</button>
            <!-- Back Button -->
            <a href="index.php" class="btn btn-info mt-3">Back</a>
        </div>
    </div>

    <hr>

    <!-- Submitted Sales Table -->
    <div class="row">
        <div class="col-md-12">
            <h3>Submitted Sales</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Sales Date</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Billing Name</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $query = "SELECT sb.sales_date, fp.product_name, sb.qty, c.billing_name 
                          FROM sales_book sb
                          JOIN finished_products fp ON sb.product_id = fp.id
                          JOIN customers c ON sb.customer_id = c.id
                          WHERE sb.company_id = ? AND sb.user_id = ?";

                $stmt = mysqli_prepare($con, $query);
                mysqli_stmt_bind_param($stmt, "ii", $company_id, $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['sales_date']) . "</td>";
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
    </div>
</div>

<?php
mysqli_close($con);
require('views/partials/footer.php');
?>

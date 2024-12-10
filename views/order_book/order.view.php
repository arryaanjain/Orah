<?php
require('views/partials/head.php');
require('views/partials/navbar.php');
require('views/partials/banner.php');
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
                        <!-- Existing row -->
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

</div>

<?php
// Close the connection
$conn->close();
require('views/partials/footer.php');
?>

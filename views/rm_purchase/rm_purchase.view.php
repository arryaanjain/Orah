<?php
require('views/partials/head.php');
require('views/partials/navbar.php');
require('views/partials/banner.php');
?>

<link rel="stylesheet" href="views/rm_purchase/style.css">
<script src="views/rm_purchase/script.js"></script>

<div class="container mt-4">
    <!-- Top Half: Dynamic Table Section -->
    <div class="row">
        <div class="col-md-12">
            <h3>Purchase Details</h3>
            <form action="/PIMS/views/rm_purchase/submit.php" method="POST">
                <input type="hidden" id="companyName" name="company_name" value="<?= htmlspecialchars($_SESSION['company_name']) ?>">

                <!-- Table for purchase details -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Purchase Date</th>
                            <th>Material Name</th>
                            <th>QTY</th>
                            <th>Unit</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Existing row -->
                        <tr>
                            <td><input type="date" class="purchaseDate" name="purchase_date[]"></td>
                            <td><input type="text" class="autocomplete material" name="column1[]"></td>
                            <td><input type="number" class="quantity" name="column2[]"></td>
                            <td><input type="text" class="autocomplete unit" name="column3[]"></td>
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

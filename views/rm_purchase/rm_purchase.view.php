<?php
require('views/partials/head.php');
require('views/partials/navbar.php');
require('views/partials/banner.php');

?>

<link rel="stylesheet" href="views/rm_purchase/style.css">

<?php
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']); // Remove message after displaying
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']); // Remove message after displaying
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h3>Purchase Details</h3>
            <form method="POST" action="/PIMS/views/rm_purchase/submit.php">
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
                        <tr>
                            <td><input type="date" name="purchase_date[]" required></td>
                            <td><input type="text" name="column1[]" class="autocomplete material" required></td>
                            <td><input type="number" name="column2[]" required></td>
                            <td><input type="text" name="column3[]" class="autocomplete unit" required></td>
                            <td><button type="button" class="deleteRowBtn">Delete</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
            <button type="button" id="addRowBtn" class="btn btn-secondary mt-3">Add Row</button>
            <a href="index.php" class="btn btn-info mt-3">Back</a>
        </div>
    </div>
</div>

<script src="views/rm_purchase/script.js"></script>

<?php require('views/partials/footer.php'); ?>

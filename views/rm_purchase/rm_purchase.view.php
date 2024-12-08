<?php
require ('views/partials/head.php');
require ('views/partials/navbar.php');
require ('views/partials/banner.php');
?>

<link rel="stylesheet" href="views/rm_purchase/style.css">

<form action="/PIMS/views/rm_purchase/submit.php" method="POST">
    <input type="hidden" id="companyName" name="company_name" value="<?= htmlspecialchars($_SESSION['company_name']) ?>">
    <table>
        <thead>
            <tr>
                <th>Material Name</th>
                <th>QTY</th>
                <th>Unit</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <tr>
                <td><input type="text" class="autocomplete material" name="column1[]"></td>
                <td><input type="number" class="quantity" name="column2[]"></td>
                <td><input type="text" class="autocomplete unit" name="column3[]"></td>
                <td><button type="button" class="deleteRowBtn">Delete</button></td>
            </tr>
        </tbody>
    </table>
    <button type="submit" id="submit">Submit</button>
</form>

<button type="button" id="addRowBtn">Add Row</button>
<a href="index.php" class="btn btn-info">Back</a>

<script src="views/rm_purchase/script.js"></script>

<?php
require('views/partials/footer.php');
?>
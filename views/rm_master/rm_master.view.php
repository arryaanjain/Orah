<?php
require('views/partials/head.php');
require('views/partials/navbar.php');
require('views/partials/banner.php');

if (!isset($_SESSION['company_id']) || !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];
?>

<br>        
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


<form id="materialForm" method="post" action="views/rm_master/submit.php">
    <h3>Insert the raw materials.</h3>
    <div id="userInputs"></div>
    <button type="button" class="btn btn-primary" onclick="addUserInput()">Add Material</button><br><br>

    <h3>Insert the units.</h3>
    <div id="userInputs2"></div>
    <button type="button" class="btn btn-primary" onclick="addUserInput2()">Add Unit</button><br><br>

    <h3>Insert customer details.</h3>
    <div id="customerInputs"></div>
    <button type="button" class="btn btn-primary" onclick="addCustomerInput()">Add Customer</button><br><br>

    <input type="submit" class="btn btn-success" name="submit" value="Submit">
    <a href="index.php" class="btn btn-info">Back</a>           
</form>
<br>

<script src="views/rm_master/script.js"></script>


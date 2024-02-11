<?php
    session_start();
    if (!isset($_SESSION['username'])) {
        header('location: LoginRegisterNew/login.php');
        exit();

    }
    require 'views/partials/head.php';
    require 'views/partials/navbar.php';
?>

<!--<a href="views/test.php" class = "btn btn-info col-lg-2" role ="button">Test/Debug</a>-->
<!--<a href="views/rm_purchase.autocomplete/index.php" class = "btn btn-info col-lg-2" role ="button">Test2/Debug2</a>-->
<!--<a href="views/test3.php" class = "btn btn-info col-lg-2" role ="button">Test3/Debug3</a>-->

<?php
require 'views/partials/footer.php';
?>
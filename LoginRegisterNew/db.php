<?php
    require_once '../functions.php';
    $con = mysqli_connect("localhost", "root", "", "pims");
    if (mysqli_connect_error()) {
        echo "Error connecting to SQL database " . mysqli_connect_error();
    }
    ?>
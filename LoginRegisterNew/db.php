<?php
    require_once '../functions.php';
    $con = mysqli_connect("localhost", "root", "");
    if (mysqli_connect_error()) {
        echo "Error connecting to SQL database " . mysqli_connect_error();
    }
    ?>
<?php
    //require_once '../functions.php';
    
    // Establish connection to the MySQL database
    $con = mysqli_connect("localhost", "root", "", "orah_schema_redone");

    // Check for connection errors
    if (mysqli_connect_error()) {
        die("Error connecting to SQL database: " . mysqli_connect_error());
    }
?>

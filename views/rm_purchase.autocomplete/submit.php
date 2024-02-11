<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pims";

// Check if the form was submitted
if (isset($_POST['submit'])) {
    // Retrieve the user inputs from the form

    $names = $_POST['name'];
    $names2 = $_POST['name2'];

    // Create a connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $stmt = $conn->prepare("INSERT INTO rm_purchase (material) VALUES (?)");
}
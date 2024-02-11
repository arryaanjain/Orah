<?php
// Connect to your database and fetch the matching words
$dsn = "mysql:host=localhost;dbname=pims";
$username = "root";
$password = "";

try {
    $pdo = new PDO($dsn, $username, $password);
    $value = $_GET["term"]; // The term parameter is automatically sent by jQuery UI Autocomplete

    $statement = $pdo->prepare("SELECT unit FROM rm_master_units WHERE unit LIKE ?");
    $statement->execute(["%" . $value . "%"]);
    $words = $statement->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($words); // Return the suggestions as a JSON array
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

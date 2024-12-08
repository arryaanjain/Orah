<?php
// Connect to the MySQL server
$dsn = "mysql:host=localhost";
$username = "root";
$password = "";
try {
    $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Get form data
    $company_name = $_POST['company_name'] ?? '';
    $materials = $_POST['column1'];  // Array of material values
    $quantities = $_POST['column2'];  // Array of quantity values
    $units = $_POST['column3'];  // Array of unit values

    if (empty($company_name) || empty($materials) || empty($quantities) || empty($units)) {
        echo "Missing required fields";
        exit;
    }

    // Dynamically switch to the correct company database
    $pdo->exec("USE `$company_name`");

    // Prepare the insert query
    $stmt = $pdo->prepare("INSERT INTO rm_purchase (material, qty, unit) VALUES (?, ?, ?)");

    // Insert each row into the rm_purchase table
    for ($i = 0; $i < count($materials); $i++) {
        $stmt->execute([$materials[$i], $quantities[$i], $units[$i]]);
    }

    // Redirect back to the form with a success message
    echo '<div class="alert alert-success" role="alert">
            Materials and units have been successfully added to the database.
          </div><br>
          <a href="/PIMS/rm_purchase.php" class="btn btn-primary">Go to Purchase Form</a>';
    exit;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

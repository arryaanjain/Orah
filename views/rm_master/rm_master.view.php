<?php
require('views/partials/head.php');
require('views/partials/navbar.php');
require('views/partials/banner.php');

// Start session to get company name
session_start();

// Check if company_name is set in the session
if (!isset($_SESSION['company_name'])) {
    echo '<div class="alert alert-danger" role="alert">
            Company session not found. Please log in again.
          </div>';
    exit();
}

$company_name = $_SESSION['company_name'];

?>

<br>
<form method="post">
    <h3>Insert the raw materials.</h3>
    <div id="userInputs">
        <!-- User input fields will be added dynamically here -->
    </div>
    <button type="button" class="btn btn-primary" onclick="addUserInput()">Add Material</button><br><br>
    <br>

    <h3>Insert the units.</h3>
    <div id="userInputs2">
        <!-- User input fields will be added dynamically here -->
    </div>
    <button type="button" class="btn btn-primary" onclick="addUserInput2()">Add Unit</button><br><br>
    <br>
    <input type="submit" class="btn btn-success" name="submit">
    <a href="../../index.php" type="button" class="btn btn-dark">Back</a>
</form>
<br>

<script>
    let counter = 1;
    let counter1 = 1;

    function addUserInput() {
        let userInputDiv = document.getElementById('userInputs');
        let inputGroup = document.createElement('div');
        inputGroup.className = 'input-group mb-3';
        inputGroup.id = `material-group-${counter}`;
        
        inputGroup.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <label for="name${counter}" class="me-2">Material Name:</label>
                <input type="text" class="form-control" name="name[]" id="name${counter}" required>
                <button type="button" class="btn btn-danger" onclick="deleteInput('material-group-${counter}')">Delete</button>
            </div>
        `;

        userInputDiv.appendChild(inputGroup);
        counter++;
    }

    function addUserInput2() {
        let userInputDiv2 = document.getElementById('userInputs2');
        let inputGroup = document.createElement('div');
        inputGroup.className = 'input-group mb-3';
        inputGroup.id = `unit-group-${counter1}`;
        
        inputGroup.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <label for="name2${counter1}" class="me-2">Unit Name:</label>
                <input type="text" class="form-control" name="name2[]" id="name2${counter1}" required>
                <button type="button" class="btn btn-danger" onclick="deleteInput('unit-group-${counter1}')">Delete</button>
            </div>
        `;

        userInputDiv2.appendChild(inputGroup);
        counter1++;
    }

    function deleteInput(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.remove();
        }
    }
</script>

<?php
// Check if the form was submitted
if (isset($_POST['submit'])) {
    // Retrieve the user inputs from the form
    $names = $_POST['name'] ?? [];
    $names2 = $_POST['name2'] ?? [];

    // Create a connection to the company-specific database
    $conn = new mysqli("localhost", "root", "", $company_name);

    // Check the connection
    if ($conn->connect_error) {
        die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
    }

    // Prepare and execute the SQL query to insert materials and units
    $stmt = $conn->prepare("INSERT INTO rm_master (material) VALUES (?)");
    $stmt1 = $conn->prepare("INSERT INTO rm_master_units (unit) VALUES (?)");

    // Insert materials
    if (!empty($names)) {
        foreach ($names as $name) {
            $stmt->bind_param("s", $name);
            $stmt->execute();
        }
    }

    // Insert units
    if (!empty($names2)) {
        foreach ($names2 as $unit) {
            $stmt1->bind_param("s", $unit);
            $stmt1->execute();
        }
    }

    // Close the statements and the database connection
    $stmt->close();
    $stmt1->close();
    $conn->close();

    echo '<div class="alert alert-success" role="alert">
            Materials and units have been successfully added to the database.
          </div>';
}
?>

<?php require('views/partials/footer.php'); ?>

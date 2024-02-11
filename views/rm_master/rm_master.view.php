<?php
require ('views/partials/head.php');
require ('views/partials/navbar.php');
require ('views/partials/banner.php');
?>
<br>
<form method="post">
    <h3>Insert the raw materials.</h3>
    <div id="userInputs">
        <!-- User input fields will be added dynamically here -->
    </div>
    <button type="button" onclick="addUserInput()">Add Material</button><br><br>
    <br>

    <h3>Insert the units.</h3>
    <div id="userInputs2">
        <!-- User input fields will be added dynamically here -->
    </div>
    <button type="button" onclick="addUserInput2()">Add Material</button><br><br>
    <br>
    <input type="submit" class="btn btn-success" name="submit">
    <a href="../../index.php" type="button" class="btn btn-dark">Back</a>
</form>
<br>

<script>
    // Counter to keep track of user inputs
    let counter = 1;
    let counter1 = 1;

    // Function to add user input fields dynamically

    function addUserInput() {
        let userInputDiv = document.getElementById('userInputs');

        let inputFields = document.createElement('div');
        inputFields.innerHTML = `
                <label for="name${counter}">Material Name:</label>
                <input type="text" name="name[]" id="name${counter}" required><br><br>

            `;

        userInputDiv.appendChild(inputFields);

        counter++;
    }

    function addUserInput2() {
        let userInputDiv2 = document.getElementById('userInputs2');

        let inputFields = document.createElement('div');
        inputFields.innerHTML = `
                <label for="name2${counter1}">Unit Name:</label>
                <input type="text" name="name2[]" id="name2${counter1}" required><br><br>

            `;

        userInputDiv2.appendChild(inputFields);

        counter1++;
    }
</script>
<?php require ('views/partials/footer.php');?>

>

<?php
// Database connection settings
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

    // Prepare and execute the SQL query to insert users
    $stmt = $conn->prepare("INSERT INTO rm_master (material) VALUES (?)");
    $stmt1 = $conn->prepare("INSERT INTO rm_master_units (unit) VALUES (?)");
    // Bind parameters and execute the statement for each user

   
    for ($i = 0; $i < count($names); $i++) {
        $stmt->bind_param("s", $names[$i]);
        $stmt->execute();
    }

    for ($j = 0; $j < count($names2); $j++) {
        $stmt1->bind_param("s", $names2[$j]);
        $stmt1->execute();
    }
    // Close the statement and the database connection
    $stmt->close();
    $stmt1->close();
    $conn->close();

    echo "Materials have been successfully added to the database.";
}
?>

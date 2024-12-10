<?php
require('views/partials/head.php');
require('views/partials/navbar.php');
require('views/partials/banner.php');

// Start session to get company name
session_start();

$company_name = $_SESSION['company_name'];

// Create logs directory if not exists
if (!file_exists('logs')) {
    mkdir('logs', 0777, true);
}

$log_file = 'logs/app.log';

function logMessage($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

?>

<br>
<form method="post">
    <!-- Section for Raw Materials -->
    <h3>Insert the raw materials.</h3>
    <div id="userInputs">
        <!-- User input fields for materials will be added dynamically here -->
    </div>
    <button type="button" class="btn btn-primary" onclick="addUserInput()">Add Material</button><br><br>

    <!-- Section for Units -->
    <h3>Insert the units.</h3>
    <div id="userInputs2">
        <!-- User input fields for units will be added dynamically here -->
    </div>
    <button type="button" class="btn btn-primary" onclick="addUserInput2()">Add Unit</button><br><br>

    <!-- Section for Customers -->
    <h3>Insert customer details.</h3>
    <div id="customerInputs">
        <!-- User input fields for customer details will be added dynamically here -->
    </div>
    <button type="button" class="btn btn-primary" onclick="addCustomerInput()">Add Customer</button><br><br>

    <input type="submit" class="btn btn-success" name="submit">
    <a href="index.php" class="btn btn-info">Back</a>           
</form>
<br>

<script>
    let materialCounter = 1;
    let unitCounter = 1;
    let customerCounter = 1;

    function addUserInput() {
        let userInputDiv = document.getElementById('userInputs');
        let inputGroup = document.createElement('div');
        inputGroup.className = 'input-group mb-3';
        inputGroup.id = `material-group-${materialCounter}`;
        
        inputGroup.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <label for="name${materialCounter}" class="me-2">Material Name:</label>
                <input type="text" class="form-control" name="material_name[]" id="name${materialCounter}" required>
                <button type="button" class="btn btn-danger" onclick="deleteInput('material-group-${materialCounter}')">Delete</button>
            </div>
        `;

        userInputDiv.appendChild(inputGroup);
        materialCounter++;
    }

    function addUserInput2() {
        let userInputDiv2 = document.getElementById('userInputs2');
        let inputGroup = document.createElement('div');
        inputGroup.className = 'input-group mb-3';
        inputGroup.id = `unit-group-${unitCounter}`;
        
        inputGroup.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <label for="name2${unitCounter}" class="me-2">Unit Name:</label>
                <input type="text" class="form-control" name="unit_name[]" id="name2${unitCounter}" required>
                <button type="button" class="btn btn-danger" onclick="deleteInput('unit-group-${unitCounter}')">Delete</button>
            </div>
        `;

        userInputDiv2.appendChild(inputGroup);
        unitCounter++;
    }

    function addCustomerInput() {
        let customerInputDiv = document.getElementById('customerInputs');
        let inputGroup = document.createElement('div');
        inputGroup.className = 'input-group mb-3';
        inputGroup.id = `customer-group-${customerCounter}`;
        
        inputGroup.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <label for="billing_name${customerCounter}" class="me-2">Billing Name:</label>
                <input type="text" class="form-control" name="billing_name[]" id="billing_name${customerCounter}" required>

                <label for="place${customerCounter}" class="me-2">Place:</label>
                <input type="text" class="form-control" name="place[]" id="place${customerCounter}" required>

                <label for="gst_number${customerCounter}" class="me-2">GST Number:</label>
                <input type="text" class="form-control" name="gst_number[]" id="gst_number${customerCounter}">

                <label for="email${customerCounter}" class="me-2">Email:</label>
                <input type="email" class="form-control" name="email[]" id="email${customerCounter}">

                <label for="phone${customerCounter}" class="me-2">Phone:</label>
                <input type="text" class="form-control" name="phone[]" id="phone${customerCounter}">

                <button type="button" class="btn btn-danger" onclick="deleteInput('customer-group-${customerCounter}')">Delete</button>
            </div>
        `;

        customerInputDiv.appendChild(inputGroup);
        customerCounter++;
    }

    function deleteInput(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.remove();
        }
    }
</script>

<?php
if (isset($_POST['submit'])) {
    try {
        $conn = new mysqli("localhost", "root", "", $company_name);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        logMessage("Database connection successful.");

        $stmt_material = $conn->prepare("INSERT INTO rm_master (material) VALUES (?)");
        foreach ($_POST['material_name'] as $material_name) {
            $stmt_material->bind_param("s", $material_name);
            $stmt_material->execute();
            logMessage("Inserted material: $material_name");
        }

        $stmt_unit = $conn->prepare("INSERT INTO rm_master_units (unit) VALUES (?)");
        foreach ($_POST['unit_name'] as $unit_name) {
            $stmt_unit->bind_param("s", $unit_name);
            $stmt_unit->execute();
            logMessage("Inserted unit: $unit_name");
        }

        $stmt_customer = $conn->prepare("INSERT INTO customers (billing_name, place, gst_number, email, phone) VALUES (?, ?, ?, ?, ?)");
        for ($i = 0; $i < count($_POST['billing_name']); $i++) {
            $stmt_customer->bind_param("sssss", $_POST['billing_name'][$i], $_POST['place'][$i], $_POST['gst_number'][$i], $_POST['email'][$i], $_POST['phone'][$i]);
            $stmt_customer->execute();
            logMessage("Inserted customer: " . $_POST['billing_name'][$i]);
        }
    } catch (Exception $e) {
        logMessage("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dynamic Table Example</title>
    <!-- Include Bootstrap CSS if required -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2>Material Table</h2>
    <table class="table">
        <thead>
        <tr>
            <th>Material Name</th>
            <th>Quantity</th>
            <th>Unit</th>
        </tr>
        </thead>
        <tbody id="materialTable">
        <!-- Table rows will be dynamically added here -->
        </tbody>
    </table>
    <button class="btn btn-primary" onclick="addRow()">Add</button>
</div>

<script>
    function addRow() {
        var table = document.getElementById("materialTable");
        var row = table.insertRow();

        var materialCell = row.insertCell();
        var materialInput = document.createElement("input");
        materialInput.type = "text";
        materialInput.classList.add("material-name");
        materialCell.appendChild(materialInput);

        var quantityCell = row.insertCell();
        var quantityInput = document.createElement("input");
        quantityInput.type = "text";
        quantityCell.appendChild(quantityInput);

        var unitCell = row.insertCell();
        var unitInput = document.createElement("input");
        unitInput.type = "text";
        unitInput.classList.add("unit");
        unitCell.appendChild(unitInput);

        // Attach event listeners for dynamic text completion
        materialInput.addEventListener("keyup", autoCompleteMaterial);
        unitInput.addEventListener("keyup", autoCompleteUnit);
    }

    function autoCompleteMaterial() {
        var input = this;
        var value = input.value;

        // Call a PHP file to fetch matching material names from the database
        // Adjust the PHP file path and query based on your setup
        fetch("fetch_materials.php?value=" + value)
            .then(response => response.json())
            .then(data => {
                // Process the returned material names
                // Here, we assume the returned data is an array of material names
                var materialNames = data;

                // Use a library or custom code to implement autocomplete functionality
                // For example, you can use jQuery UI Autocomplete or implement your own logic
                // to show the suggestions as the user types
                // You can update the code below to match your preferred autocomplete implementation

                $(input).autocomplete({
                    source: materialNames
                });
            });
    }

    function autoCompleteUnit() {
        var input = this;
        var value = input.value;

        // Call a PHP file to fetch matching units from the database
        // Adjust the PHP file path and query based on your setup
        fetch("fetch_units.php?value=" + value)
            .then(response => response.json())
            .then(data => {
                // Process the returned units
                // Here, we assume the returned data is an array of units
                var units = data;

                // Implement autocomplete functionality similar to autoCompleteMaterial()
            });
    }
</script>

<!-- Include Bootstrap JS if required -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<!-- Include any additional JS libraries you may need -->
</body>
</html>

<?php
class Database {
    public PDO $conn;

    /**
     * @param $config
     * @param string $username
     * @param string $password
     */
    public function __construct($config, string $username='root', string $password='') {


        //$dsn = "mysql:host={$config['host']};port={$config['port']};user=root;dbname={$config['dbname']};charset={$config['charset']}";
        //instead of this, we can use existing function to build query

        //dd('mysql:'.http_build_query($config,'',';'));
        $dsn = 'mysql:'.http_build_query($config,'',';');


        $this->conn = new PDO($dsn,$username,$password,[
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    /**
     * @param $query
     * @return false|PDOStatement
     */
    public function query($query): false|PDOStatement
    {

        $statement = $this->conn->prepare($query);
        $statement->execute();

//$posts = $statement->fetch(PDO::FETCH_ASSOC);
//return $posts;
        return $statement;
    }
}
$config =   [
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => 'pims',
        'charset' => 'utf8mb4'
    ]
];
// Assuming you have already established a database connection
$conn = new Database($config);
//$posts = $db->query('select *from rm_master')->fetchAll(PDO::FETCH_ASSOC);
// Get the value from the AJAX request
$value = $_GET['value'];

// Prepare the SQL query to fetch matching material names
$query = "SELECT material_name FROM rm_master WHERE material_name LIKE '%$value%'";

// Execute the query
$result = $conn->query($query);

// Prepare an array to store the material names
$materialNames = array();

// Fetch the results and add them to the array
while ($row = $result->fetch_assoc()) {
    $materialNames[] = $row['material_name'];
}

// Send the material names as JSON response
echo json_encode($materialNames);

// Close the database connection
$conn->close();


// Assuming you have already established a database connection

// Get the value from the AJAX request
$value = $_GET['value'];

// Prepare the SQL query to fetch matching units
$query = "SELECT unit FROM rm_master WHERE unit LIKE '%$value%'";

// Execute the query
$result = $conn->query($query);

// Prepare an array to store the units
$units = array();

// Fetch the results and add them to the array
while ($row = $result->fetch_assoc()) {
    $units[] = $row['unit'];
}

// Send the units as JSON response
echo json_encode($units);

// Close the database connection
$conn->close();


?>
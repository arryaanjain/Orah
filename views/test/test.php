<html>
<head>
    <title>Test/Debug</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PIMS:Inventory Management</title>
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.css">
</head>
<body>
<form method="post">
    <div id="userInputs">
        <!-- User input fields will be added dynamically here -->

        <div class="autocomplete">
            <input type="text" id="myInput" name="myInput" placeholder="Type something...">
            <div class="autocomplete-items.0"></div>
        </div>
    </div>

    <input type="submit" class="btn btn-success" name="submit">
</form>

<form method="post">
    <div id="userInputs">
        <!-- User input fields will be added dynamically here -->

        <div class="autocomplete">
            <input type="text" id="myInput2" name="myInput" placeholder="Type something...">
            <div class="autocomplete-items"></div>
        </div>
    </div>

    <input type="submit" class="btn btn-success" name="submit">
</form>

    <a href="../../index.php" class="btn btn-danger">Home</a>
</body>
</html>

<?php

// Database connection details
$host = 'localhost';
$dbname = 'pims';
$username = 'root';
$password = '';

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Specify the table name
    $tableName = 'rm_master';

    // Prepare and execute the query to fetch all rows from the table
    $stmt = $pdo->prepare("SELECT material FROM $tableName");
    $stmt->execute();

    // Fetch all rows as an associative array
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Copy data elements into a PHP array
    $dataArray = array();
    foreach ($rows as $row) {
        $dataArray[] = $row['material']; // Replace 'column_name' with the actual column name from the table
    }

    // Display the array
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$jsonArray = json_encode($dataArray);
?>

<script>
    <!--Autocomplete script-->
    let data =[];
    data = <?php echo $jsonArray; ?>;
    // Array of sample data for autocomplete

    // Call the autocomplete function on the text input
    autocomplete(document.getElementById("myInput"), data);
    autocomplete(document.getElementById("myInput2"), data);
    // Function to handle the autocomplete functionality
    function autocomplete(input, arr) {
        var currentFocus;

        // Execute when user starts typing
        input.addEventListener("input", function(e) {
            var val = this.value;
            closeAllLists();
            if (!val) {
                return false;
            }
            currentFocus = -1;

            // Create a DIV element that will contain the items (values)
            var itemContainer = document.createElement("div");
            itemContainer.setAttribute("class", "autocomplete-items");
            this.parentNode.appendChild(itemContainer);

            // Find matching values from the data array
            for (var i = 0; i < arr.length; i++) {
                if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                    // Create a DIV element for each matching value
                    var item = document.createElement("div");
                    item.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
                    item.innerHTML += arr[i].substr(val.length);
                    item.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";

                    // Execute when an item is selected
                    item.addEventListener("click", function(e) {
                        input.value = this.getElementsByTagName("input")[0].value;
                        closeAllLists();
                    });

                    itemContainer.appendChild(item);
                }
            }
        });

        // Execute when user presses a key
        input.addEventListener("keydown", function(e) {
            var items = document.getElementsByClassName("autocomplete-items");
            if (e.keyCode == 40) {
                currentFocus++;
                addActive(items);
            } else if (e.keyCode == 38) {
                currentFocus--;
                addActive(items);
            } else if (e.keyCode == 13) {
                e.preventDefault();
                if (currentFocus > -1) {
                    if (items) {
                        items[currentFocus].click();
                    }
                }
            }
        });

        // Function to add "autocomplete-active" class
        function addActive(items) {
            if (!items) {
                return false;
            }
            removeActive(items);
            if (currentFocus >= items.length) {
                currentFocus = 0;
            }
            if (currentFocus < 0) {
                currentFocus = (items.length - 1);
            }
            items[currentFocus].classList.add("autocomplete-active");
        }

        // Function to remove "autocomplete-active" class
        function removeActive(items) {
            for (var i = 0; i < items.length; i++) {
                items[i].classList.remove("autocomplete-active");
            }
        }

        // Function to close the autocomplete dropdown
        function closeAllLists(elmnt) {
            var items = document.getElementsByClassName("autocomplete-items");
            for (var i = 0; i < items.length; i++) {
                if (elmnt !== items[i] && elmnt !== input) {
                    items[i].parentNode.removeChild(items[i]);
                }
            }
        }

        // Execute when user clicks outside the autocomplete dropdown
        document.addEventListener("click", function(e) {
            closeAllLists(e.target);
        });
    }
</script>

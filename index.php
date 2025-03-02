<?php
require 'views/partials/head.php';
require 'views/partials/navbar.php';
require 'db.php'; // Use the MySQLi connection

// Retrieve company ID from session
$company_id = $_SESSION['company_id'] ?? null;
if (!$company_id) {
    die("<div class='alert alert-danger'>No company ID provided. Please ensure the session includes a valid company ID.</div>");
}

$sections = [
    "Raw Material Master" => "rm_master",
    "Raw Material Purchase" => "rm_purchase",
    "Finished Products" => "finished_products",
    "Order Book" => "order_book",
    "Sales Book" => "sales_book"
];
?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }
        .section {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }
        .totals {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .hidden-table {
            display: none;
            margin-top: 20px;
            border-collapse: collapse;
            width: 100%;
        }
        .hidden-table th, .hidden-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .hidden-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
    <script>
        function toggleTable(sectionId) {
            const table = document.getElementById(sectionId);
            table.style.display = table.style.display === 'none' ? 'table' : 'none';
        }
    </script>
</head>
<body>
<header>
    <h1>Inventory Management System</h1>
</header>
<div class="container">
    <?php
    foreach ($sections as $title => $table) {
        $stmt = $con->prepare("SELECT * FROM $table WHERE company_id = ? LIMIT 1");
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $sampleData = $result->fetch_assoc();
        $stmt->close();

        echo "<div class='section' onclick='toggleTable(\"table_$table\")'>";
        echo "<h2>{$title}</h2>";
        echo "<table class='hidden-table' id='table_$table'>";
        
        if ($sampleData) {
            echo "<tr>";
            foreach (array_keys($sampleData) as $header) {
                echo "<th>" . htmlspecialchars($header) . "</th>";
            }
            echo "</tr>";
            
            $stmt = $con->prepare("SELECT * FROM $table WHERE company_id = ?");
            $stmt->bind_param("i", $company_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            $stmt->close();
        } else {
            echo "<tr><th>No data available</th></tr>";
        }
        echo "</table>";
        echo "</div>";
    }
    ?>


<?php
require 'views/partials/footer.php';
?>

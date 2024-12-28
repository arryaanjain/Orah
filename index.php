<?php
require 'views/partials/head.php';
require 'views/partials/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
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
        .section h2 {
            margin-top: 0;
        }
        .section p {
            font-size: 16px;
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
    // PDO Database Connection
    $dsn = "mysql:host=localhost";
    $username = "root";
    $password = "";

    try {
        $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $company_name = $_SESSION['company_name'] ?? '';

        if (empty($company_name)) {
            throw new Exception("No company name provided. Please ensure the form submission includes the company name.");
        }

        // Use the company database
        $pdo->exec("USE `$company_name`");

        // Queries to calculate totals and fetch data
        $totals = [];
        $data = [];

        // Section details
        $sections = [
            "Raw Material Master" => "rm_master",
            "Raw Material Purchase" => "rm_purchase",
            "Finished Products" => "finished_products",
            "Order Book" => "order_book",
            "Sales Book" => "sales_book"
        ];

        foreach ($sections as $title => $table) {
            $totals[$table] = $pdo->query("SELECT COUNT(*) AS total FROM `$table`")->fetch()['total'];
            $data[$table] = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        }

        foreach ($sections as $title => $table) {
            echo "<div class='section' onclick='toggleTable(\"table_$table\")'>";
            echo "<h2>{$title}</h2>";
            echo "<p>Total: <span class='totals'>" . ($totals[$table] ?? 0) . "</span></p>";

            // Hidden table
            echo "<table class='hidden-table' id='table_$table'>";
            if (!empty($data[$table])) {
                // Table headers
                echo "<tr>";
                foreach (array_keys($data[$table][0]) as $header) {
                    echo "<th>" . htmlspecialchars($header) . "</th>";
                }
                echo "</tr>";

                // Table rows
                foreach ($data[$table] as $row) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='100%'>No data available</td></tr>";
            }
            echo "</table>";

            echo "</div>";
        }

    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>" . htmlspecialchars($e->getMessage()) . "</div>";
    }
    ?>
</div>
</body>
</html>

<?php
require 'views/partials/footer.php';
?>

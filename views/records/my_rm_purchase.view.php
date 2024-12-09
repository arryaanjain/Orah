<?php
require('views/partials/head.php');
require('views/partials/navbar.php');
require('views/partials/banner.php');

// Initialize records array and variables for handling edits
$records = [];
$searchDate = '';
$editMessage = '';

// Handle search by date and query the database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_date'])) {
    $searchDate = $_POST['search_date'];

    // Make sure a date is selected
    if ($searchDate) {
        // Get the company name from the session
        $company_name = $_SESSION['company_name'];

        // Create a connection to the company-specific database
        $conn = new mysqli("localhost", "root", "", $company_name);

        // Check the connection
        if ($conn->connect_error) {
            die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
        }

        // Query to fetch records for the given date
        $purchaseQuery = "SELECT id, material, qty, unit, purchase_date FROM rm_purchase WHERE purchase_date = ?";
        $stmt = $conn->prepare($purchaseQuery);
        $stmt->bind_param("s", $searchDate);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch the records
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }

        $stmt->close();
        $conn->close();
    }
}

// Handle editing of records
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_record_id'])) {
    $editRecordId = $_POST['edit_record_id'];
    $material = $_POST['material'];
    $qty = $_POST['qty'];
    $unit = $_POST['unit'];

    // Get the company name from the session
    $company_name = $_SESSION['company_name'];

    // Create a connection to the company-specific database
    $conn = new mysqli("localhost", "root", "", $company_name);

    // Check the connection
    if ($conn->connect_error) {
        die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
    }

    // Query to update the record in the database
    $updateQuery = "UPDATE rm_purchase SET material = ?, qty = ?, unit = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssi", $material, $qty, $unit, $editRecordId);

    if ($stmt->execute()) {
        $editMessage = "<div class='alert alert-success'>Record updated successfully.</div>";
    } else {
        $editMessage = "<div class='alert alert-danger'>Error updating record: " . $stmt->error . "</div>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!-- Content section starts here -->
<div class="container mt-4">
    <div class="row">
        <!-- Search and Edit Section -->
        <div class="col-md-12 mb-4">
            <h3>Search and Edit RM Purchase Records</h3>

            <!-- Display success or error message for editing -->
            <?php echo $editMessage; ?>

            <!-- Search by Date Form -->
            <form method="POST" class="mb-4">
                <div class="mb-3">
                    <label for="search_date" class="form-label">Search by Date</label>
                    <input type="date" class="form-control" name="search_date" id="search_date" required>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <!-- Display Records for the given date -->
            <?php if (!empty($records)): ?>
                <h4>Records for Date: <?php echo htmlspecialchars($searchDate); ?></h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Material</th>
                            <th>QTY</th>
                            <th>Unit</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $count = 1;
                        foreach ($records as $row) {
                            echo "<tr>
                                    <td>{$count}</td>
                                    <td>{$row['material']}</td>
                                    <td>{$row['qty']}</td>
                                    <td>{$row['unit']}</td>
                                    <td><button class='btn btn-warning' data-bs-toggle='modal' data-bs-target='#editModal{$row['id']}'>Edit</button></td>
                                  </tr>";
                            $count++;

                            // Modal for editing purchase record
                            echo "<div class='modal fade' id='editModal{$row['id']}' tabindex='-1' aria-labelledby='editModalLabel' aria-hidden='true'>
                                    <div class='modal-dialog'>
                                        <div class='modal-content'>
                                            <div class='modal-header'>
                                                <h5 class='modal-title' id='editModalLabel'>Edit Purchase Record</h5>
                                                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                            </div>
                                            <div class='modal-body'>
                                                <form method='POST'>
                                                    <input type='hidden' name='edit_record_id' value='{$row['id']}'>
                                                    <div class='mb-3'>
                                                        <label for='material' class='form-label'>Material Name</label>
                                                        <input type='text' class='form-control' name='material' value='{$row['material']}' required>
                                                    </div>
                                                    <div class='mb-3'>
                                                        <label for='qty' class='form-label'>Quantity</label>
                                                        <input type='number' class='form-control' name='qty' value='{$row['qty']}' required>
                                                    </div>
                                                    <div class='mb-3'>
                                                        <label for='unit' class='form-label'>Unit</label>
                                                        <input type='text' class='form-control' name='unit' value='{$row['unit']}' required>
                                                    </div>
                                                    <button type='submit' class='btn btn-primary'>Save changes</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                  </div>";
                        }
                        ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No records found for the given date.</p>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php
require('views/partials/footer.php');
?>

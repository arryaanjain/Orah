<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('views/partials/head.php');
require('views/partials/navbar.php');
require('views/partials/banner.php');

// Check if user is logged in and retrieve session variables
if (!isset($_SESSION['company_id'], $_SESSION['user_id'])) {
    die("<div class='alert alert-danger'>Unauthorized access. Please log in.</div>");
}
// Database Connection
require_once('db.php');

$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];

$records = [];
$searchDate = '';
$editMessage = '';


// Check connection
if ($con->connect_error) {
    die("<div class='alert alert-danger'>Connection failed: " . $con->connect_error . "</div>");
}

// Handle search by date
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_date'])) {
    $searchDate = $_POST['search_date'];

    if ($searchDate) {
        // Fetch records for the given date, filtered by company_id and user_id
        $purchaseQuery = "SELECT p.id, p.purchase_date, p.qty, 
                m.material, u.unit 
        FROM rm_purchase p
        JOIN rm_master m ON p.material_id = m.id
        JOIN rm_master_units u ON p.unit_id = u.id
        WHERE p.purchase_date = ? AND p.company_id = ? AND p.user_id = ?";

        $stmt = $con->prepare($purchaseQuery);
        $stmt->bind_param("sii", $searchDate, $company_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }

        $stmt->close();

    }
}

// Handle editing of records
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_record_id'])) {
    $editRecordId = $_POST['edit_record_id'];
    $material = $_POST['material']; // Submitted material name
    $qty = $_POST['qty'];
    $unit = $_POST['unit']; // Submitted unit name

    // Fetch material_id based on material
    $materialQuery = "SELECT id FROM rm_master WHERE material = ? AND company_id = ? AND user_id = ?";
    $stmtMaterial = $con->prepare($materialQuery);
    $stmtMaterial->bind_param("sii", $material, $company_id, $user_id);
    $stmtMaterial->execute();
    $stmtMaterial->bind_result($material_id);
    $stmtMaterial->fetch();
    $stmtMaterial->close();

    // Fetch unit_id based on unit
    $unitQuery = "SELECT id FROM rm_master_units WHERE unit = ? AND company_id = ? AND user_id = ?";
    $stmtUnit = $con->prepare($unitQuery);
    $stmtUnit->bind_param("sii", $unit, $company_id, $user_id);
    $stmtUnit->execute();
    $stmtUnit->bind_result($unit_id);
    $stmtUnit->fetch();
    $stmtUnit->close();

    // Proceed with update only if IDs are found
    if ($material_id && $unit_id) {
        $updateQuery = "UPDATE rm_purchase 
                        SET material_id = ?, qty = ?, unit_id = ? 
                        WHERE id = ? AND company_id = ? AND user_id = ?";
        $stmt = $con->prepare($updateQuery);
        $stmt->bind_param("iiiiii", $material_id, $qty, $unit_id, $editRecordId, $company_id, $user_id);

        if ($stmt->execute()) {
            $editMessage = "<div class='alert alert-success'>Record updated successfully.</div>";
        } else {
            $editMessage = "<div class='alert alert-danger'>Error updating record: " . $stmt->error . "</div>";
        }

        $stmt->close();
    } else {
        $editMessage = "<div class='alert alert-danger'>Invalid Material or Unit selected.</div>";
    }
}

// Close connection
$con->close();
?>

<!-- Content Section -->
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12 mb-4">
            <h3>Search and Edit RM Purchase Records</h3>
            <?php echo $editMessage; ?>

            <!-- Search Form -->
            <form method="POST" class="mb-4">
                <div class="mb-3">
                    <label for="search_date" class="form-label">Search by Date</label>
                    <input type="date" class="form-control" name="search_date" id="search_date" required>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <!-- Display Records -->
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
                                                <h5 class='modal-title'>Edit Purchase Record</h5>
                                                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                            </div>
                                            <div class='modal-body'>
                                                <form method='POST'>
                                                    <input type='hidden' name='edit_record_id' value='{$row['id']}'>
                                                    <div class='mb-3'>
                                                        <label class='form-label'>Material Name</label>
                                                        <input type='text' class='form-control' name='material' value='{$row['material']}' required>
                                                    </div>
                                                    <div class='mb-3'>
                                                        <label class='form-label'>Quantity</label>
                                                        <input type='number' class='form-control' name='qty' value='{$row['qty']}' required>
                                                    </div>
                                                    <div class='mb-3'>
                                                        <label class='form-label'>Unit</label>
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

<?php require('views/partials/footer.php'); ?>

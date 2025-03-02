<?php
require('views/partials/head.php');
require('views/partials/navbar.php');
require('views/partials/banner.php');
require('db.php'); // Use the central database connection

error_reporting(E_ALL); ini_set('display_errors', 1);

// Retrieve user and company details from session
$user_id = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'];

// Query to fetch all materials and units specific to the company
$materialQuery = "SELECT id, material FROM rm_master WHERE company_id = ?";
$unitQuery = "SELECT id, unit FROM rm_master_units WHERE company_id = ?";

// Prepare and execute material query
$stmt = $con->prepare($materialQuery);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$materialResult = $stmt->get_result();
$stmt->close();

// Prepare and execute unit query
$stmt = $con->prepare($unitQuery);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$unitResult = $stmt->get_result();
$stmt->close();

// Handle form submission for editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_material_id'])) {
        // Edit material
        $materialId = $_POST['edit_material_id'];
        $newMaterial = $_POST['material'];
        
        // Update query
        $updateMaterialQuery = "UPDATE rm_master SET material = ? WHERE id = ? AND company_id = ?";
        $stmt = $con->prepare($updateMaterialQuery);
        $stmt->bind_param("sii", $newMaterial, $materialId, $company_id);
        $stmt->execute();
        $stmt->close();
        
        echo '<div class="alert alert-success" role="alert">Material updated successfully!</div>';
    }

    if (isset($_POST['edit_unit_id'])) {
        // Edit unit
        $unitId = $_POST['edit_unit_id'];
        $newUnit = $_POST['unit'];
        
        // Update query
        $updateUnitQuery = "UPDATE rm_master_units SET unit = ? WHERE id = ? AND company_id = ?";
        $stmt = $con->prepare($updateUnitQuery);
        $stmt->bind_param("sii", $newUnit, $unitId, $company_id);
        $stmt->execute();
        $stmt->close();
        
        echo '<div class="alert alert-success" role="alert">Unit updated successfully!</div>';
    }
}
?>


<!-- Content Section -->
<div class="container mt-4">
    <h3>Inserted Materials</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Material</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Display materials
            if ($materialResult->num_rows > 0) {
                $count = 1;
                while ($row = $materialResult->fetch_assoc()) {
                    echo "<tr>
                            <td>{$count}</td>
                            <td>{$row['material']}</td>
                            <td><button class='btn btn-warning' data-bs-toggle='modal' data-bs-target='#editMaterialModal{$row['id']}'>Edit</button></td>
                          </tr>";
                    $count++;
                    
                    // Modal for editing material
                    echo "<div class='modal fade' id='editMaterialModal{$row['id']}' tabindex='-1' aria-labelledby='editMaterialModalLabel' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='editMaterialModalLabel'>Edit Material</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <div class='modal-body'>
                                        <form method='POST'>
                                            <input type='hidden' name='edit_material_id' value='{$row['id']}'>
                                            <div class='mb-3'>
                                                <label for='material' class='form-label'>Material Name</label>
                                                <input type='text' class='form-control' name='material' value='{$row['material']}' required>
                                            </div>
                                            <button type='submit' class='btn btn-primary'>Save changes</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>";
                }
            } else {
                echo "<tr><td colspan='3' class='text-center'>No materials found</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <h3>Inserted Units</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Unit</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Display units
            if ($unitResult->num_rows > 0) {
                $count = 1;
                while ($row = $unitResult->fetch_assoc()) {
                    echo "<tr>
                            <td>{$count}</td>
                            <td>{$row['unit']}</td>
                            <td><button class='btn btn-warning' data-bs-toggle='modal' data-bs-target='#editUnitModal{$row['id']}'>Edit</button></td>
                          </tr>";
                    $count++;
                    
                    // Modal for editing unit
                    echo "<div class='modal fade' id='editUnitModal{$row['id']}' tabindex='-1' aria-labelledby='editUnitModalLabel' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='editUnitModalLabel'>Edit Unit</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <div class='modal-body'>
                                        <form method='POST'>
                                            <input type='hidden' name='edit_unit_id' value='{$row['id']}'>
                                            <div class='mb-3'>
                                                <label for='unit' class='form-label'>Unit Name</label>
                                                <input type='text' class='form-control' name='unit' value='{$row['unit']}' required>
                                            </div>
                                            <button type='submit' class='btn btn-primary'>Save changes</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>";
                }
            } else {
                echo "<tr><td colspan='3' class='text-center'>No units found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php
// Close the connection
$con->close();

require('views/partials/footer.php');
?>

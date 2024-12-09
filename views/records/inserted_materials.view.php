<?php
require('views/partials/head.php');
require('views/partials/navbar.php');
require('views/partials/banner.php');

//the QUICKEST section created, edit function beautifully GPT'd
$company_name = $_SESSION['company_name'];

// Create a connection to the company-specific database
$conn = new mysqli("localhost", "root", "", $company_name);

// Check the connection
if ($conn->connect_error) {
    die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
}

// Query to fetch all materials and units
$materialQuery = "SELECT id, material FROM rm_master";
$unitQuery = "SELECT id, unit FROM rm_master_units";

// Execute the queries
$materialResult = $conn->query($materialQuery);
$unitResult = $conn->query($unitQuery);

// Handle form submission for editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_material_id'])) {
        // Edit material
        $materialId = $_POST['edit_material_id'];
        $newMaterial = $_POST['material'];
        
        // Update query
        $updateMaterialQuery = "UPDATE rm_master SET material = ? WHERE id = ?";
        $stmt = $conn->prepare($updateMaterialQuery);
        $stmt->bind_param("si", $newMaterial, $materialId);
        $stmt->execute();
        $stmt->close();
        
        echo '<div class="alert alert-success" role="alert">Material updated successfully!</div>';
    }

    if (isset($_POST['edit_unit_id'])) {
        // Edit unit
        $unitId = $_POST['edit_unit_id'];
        $newUnit = $_POST['unit'];
        
        // Update query
        $updateUnitQuery = "UPDATE rm_master_units SET unit = ? WHERE id = ?";
        $stmt = $conn->prepare($updateUnitQuery);
        $stmt->bind_param("si", $newUnit, $unitId);
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
$conn->close();

require('views/partials/footer.php');
?>

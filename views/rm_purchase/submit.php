<?php
session_start();
require '../../db.php';

if (!isset($_SESSION['company_id']) || !isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: /PIMS/rm_purchase.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];

$purchase_dates = $_POST['purchase_date'] ?? [];
$materials = $_POST['column1'] ?? [];
$quantities = $_POST['column2'] ?? [];
$units = $_POST['column3'] ?? [];

if (empty($purchase_dates) || empty($materials) || empty($quantities) || empty($units)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: /PIMS/rm_purchase.php");
    exit();
}

$query = "INSERT INTO rm_purchase (purchase_date, material_id, qty, unit_id, company_id, user_id) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($con, $query);

if (!$stmt) {
    $_SESSION['error'] = "Database error: " . mysqli_error($con);
    header("Location: /PIMS/rm_purchase.php");
    exit();
}

$allInserted = true;

for ($i = 0; $i < count($materials); $i++) {
    $material_name = trim($materials[$i]);
    $unit_name = trim($units[$i]);
    $material_id = null;
    $unit_id = null;

    $stmt_material = mysqli_prepare($con, "SELECT id FROM rm_master WHERE material = ? AND company_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt_material, "sii", $material_name, $company_id, $user_id);
    mysqli_stmt_execute($stmt_material);
    mysqli_stmt_bind_result($stmt_material, $material_id);
    mysqli_stmt_fetch($stmt_material);
    mysqli_stmt_close($stmt_material);

    if (!$material_id) {
        $_SESSION['error'] = "Material '$material_name' not found.";
        header("Location: /PIMS/rm_purchase.php");
        exit();
    }

    $stmt_unit = mysqli_prepare($con, "SELECT id FROM rm_master_units WHERE unit = ? AND company_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt_unit, "sii", $unit_name, $company_id, $user_id);
    mysqli_stmt_execute($stmt_unit);
    mysqli_stmt_bind_result($stmt_unit, $unit_id);
    mysqli_stmt_fetch($stmt_unit);
    mysqli_stmt_close($stmt_unit);

    if (!$unit_id) {
        $_SESSION['error'] = "Unit '$unit_name' not found.";
        header("Location: /PIMS/rm_purchase.php");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "sdisii", $purchase_dates[$i], $material_id, $quantities[$i], $unit_id, $company_id, $user_id);
    if (!mysqli_stmt_execute($stmt)) {
        $allInserted = false;
    }
}

mysqli_stmt_close($stmt);

if ($allInserted) {
    $_SESSION['success'] = "✅ Purchase data saved successfully!";
} else {
    $_SESSION['error'] = "Some data could not be inserted.";
}

header("Location: /PIMS/rm_purchase.php");
exit();

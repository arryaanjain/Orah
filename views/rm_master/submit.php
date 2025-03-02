<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../../db.php';
session_start();

if (!isset($_SESSION['company_id']) || !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];

try {
    if (!empty($_POST['material_name'])) {
        $stmt_material = $con->prepare("INSERT INTO rm_master (material, company_id, user_id) VALUES (?, ?, ?)");
        foreach ($_POST['material_name'] as $material_name) {
            if (!empty($material_name)) {
                $stmt_material->bind_param("sii", $material_name, $company_id, $user_id);
                $stmt_material->execute();
            }
        }
    }

    if (!empty($_POST['unit_name'])) {
        $stmt_unit = $con->prepare("INSERT INTO rm_master_units (unit, company_id, user_id) VALUES (?, ?, ?)");
        foreach ($_POST['unit_name'] as $unit_name) {
            if (!empty($unit_name)) {
                $stmt_unit->bind_param("sii", $unit_name, $company_id, $user_id);
                $stmt_unit->execute();
            }
        }
    }

    if (!empty($_POST['billing_name'])) {
        $stmt_customer = $con->prepare("INSERT INTO customers (billing_name, place, gst_number, email, phone, company_id, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        for ($i = 0; $i < count($_POST['billing_name']); $i++) {
            if (!empty($_POST['billing_name'][$i]) && !empty($_POST['place'][$i])) {
                $stmt_customer->bind_param("ssssiii", $_POST['billing_name'][$i], $_POST['place'][$i], $_POST['gst_number'][$i], $_POST['email'][$i], $_POST['phone'][$i], $company_id, $user_id);
                $stmt_customer->execute();
            }
        }
    }

    $_SESSION['success'] = "✅ Data saved successfully!";
    header("Location: /PIMS/rm_master.php");
    exit();
} catch (Exception $e) {
    $_SESSION['error'] = "❌ Error: " . $e->getMessage();
    header("Location: /PIMS/rm_master.php");
    exit();
}
?>

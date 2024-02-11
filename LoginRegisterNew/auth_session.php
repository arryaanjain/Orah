<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("location: LoginRegisterNew/login.php");
    exit();
}
?>
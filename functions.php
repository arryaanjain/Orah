<?php
function dd($value): void
{
    echo '<pre>';
    var_dump($value);
    echo '</pre>';
}

function sessionChk(): void
{
    if (!isset($_SESSION['username'])) {
        header('location: LoginRegisterNew/login.php');
        exit();

    }
}
class setError {
    function _setError(): void {
        header('Content-Type: application/json');
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }
}
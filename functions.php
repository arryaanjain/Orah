<?php
function dd($value): void
{
    echo '<pre>';
    var_dump($value);
    echo '</pre>';
}

function sessionChk(): void
{
    session_start();
    if (!isset($_SESSION['username'])) {
        header('location: LoginRegisterNew/login.php');
        exit();

    }
    require 'index.php';
}
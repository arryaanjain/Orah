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
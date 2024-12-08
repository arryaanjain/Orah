<?php
session_start();
if (isset($_SESSION["username"])) {
    header("location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Login</title>
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
<?php
require 'db.php'; // Ensure this connects to the central database containing company details.

if (isset($_POST['username'], $_POST['password'], $_POST['company_name'])) {
    $company_name = stripslashes($_REQUEST['company_name']);
    $company_name = mysqli_real_escape_string($con, $company_name);

    $username = stripslashes($_REQUEST['username']);
    $username = mysqli_real_escape_string($con, $username);

    $password = stripslashes($_REQUEST['password']);
    $password = mysqli_real_escape_string($con, $password);

    // Check if the company database exists
    $check_db_query = "SHOW DATABASES LIKE '$company_name'";
    $check_db_result = mysqli_query($con, $check_db_query);

    if (mysqli_num_rows($check_db_result) > 0) {
        // Connect to the company database
        $company_con = new mysqli("localhost", "root", "", $company_name);

        if ($company_con->connect_error) {
            die("Connection failed: " . $company_con->connect_error);
        }

        // Check if the username and password exist in the company's `users` table
        $query = "SELECT * FROM `users` WHERE username='$username' AND password='" . md5($password) . "'";
        $result = mysqli_query($company_con, $query);

        if (mysqli_num_rows($result) > 0) {
            $_SESSION['username'] = $username;
            $_SESSION['company_name'] = $company_name;
            header('location: ../index.php');
        } else {
            echo "<div class='form'>
                      <h3>Incorrect Username/Password.</h3><br/>
                      <p class='link'>Click here to <a href='login.php'>Login</a> again.</p>
                  </div>";
        }

        $company_con->close();
    } else {
        echo "<div class='form'>
                  <h3>Invalid Company Name.</h3><br/>
                  <p class='link'>Click here to <a href='login.php'>Login</a> again.</p>
              </div>";
    }
} else {
?>
    <form class="loginform" id="loginform" method="post" name="login">
        <h1 class="login-title">Login</h1>
        <input type="text" class="login-input" id="company_name" name="company_name" placeholder="Company Name" required/>
        <input type="text" class="login-input" id="username" name="username" placeholder="Username" required/>
        <input type="password" class="login-input" id="password" name="password" placeholder="Password" required/>
        <input type="submit" value="Login" name="submit" class="login-button"/>
        <p class="link"><a href="user_registration_new.php">New Registration</a></p>
    </form>
<?php
}
?>
</body>
</html>

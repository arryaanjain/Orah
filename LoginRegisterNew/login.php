<?php
session_start();
if (isset($_SESSION["username"])) {
    header("location: ../rm_purchase.view.php");
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
require 'db.php';
// When form submitted, check and create user session.
if (isset($_POST['username'])) {
    $username = stripslashes($_REQUEST['username']);    // removes backslashes
    $username = mysqli_real_escape_string($con, $username);
    $password = stripslashes($_REQUEST['password']);
    $password = mysqli_real_escape_string($con, $password);
    // Check user exists in the database

    $query    = "SELECT * FROM `users` WHERE username='".$username."' AND password='" . md5($password) . "'";
    $result = mysqli_query($con, $query);
    $rows = mysqli_num_rows($result);
    if ($rows > 0) {
        $_SESSION['username'] = $username;
       header('location: ../rm_purchase.view.php');
     } else {
        echo "<div class='form'>
                  <h3>Incorrect Username/password.</h3><br/>
                  <p class='link'>Click here to <a href='login.php'>Login</a> again.</p>
                  </div>";
    }
} else {
    ?>
    <form class="loginform" id="loginform" method="post" name="login">
        <h1 class="login-title">Login</h1>
        <input type="text" class="login-input" id="username" name="username" placeholder="Username" autofocus="autofocus" required/>
        <input type="password" class="login-input" id="password" name="password" placeholder="Password" required/>
        <input type="submit" value="Login" name="submit" class="login-button"/>
        <p class="link"><a href="user_registration_new.php">New Registration</a></p>
    </form>
    <?php
}

?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="auth.js"></script>
</body>
</html>
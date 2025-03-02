<?php
session_start();
if (isset($_SESSION["username"])) {
    header("location: ../index.php");
    exit();
}

require '../db.php'; // Ensure connection to the database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form input
    $company_name = trim($_POST['company_name']);
    $username = trim($_POST['username']);
    $password = $_POST['password']; // Do not escape password

    // Use prepared statements to prevent SQL injection
    $check_company_query = "SELECT id FROM companies WHERE LOWER(name) = LOWER(?)";
    $stmt = mysqli_prepare($con, $check_company_query);
    mysqli_stmt_bind_param($stmt, "s", $company_name);
    mysqli_stmt_execute($stmt);
    $company_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($company_result) > 0) {
        $company_data = mysqli_fetch_assoc($company_result);
        $company_id = $company_data['id'];

        // Check if user exists
        $query = "SELECT id, username, password FROM users WHERE username = ? AND company_id = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "si", $username, $company_id);
        mysqli_stmt_execute($stmt);
        $user_result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($user_result) > 0) {
            $user = mysqli_fetch_assoc($user_result);

            if (password_verify($password, $user['password'])) {
                // Secure session variables
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['company_id'] = $company_id;
                $_SESSION['company_name'] = $company_name;

                header('location: ../index.php');
                exit();
            } else {
                $error_message = "Incorrect Username/Password.";
            }
        } else {
            $error_message = "Incorrect Username/Password.";
        }
    } else {
        $error_message = "Invalid Company Name.";
    }
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
    <?php if (!empty($error_message)): ?>
        <div class='form'>
            <h3><?php echo $error_message; ?></h3><br/>
            <p class='link'>Click here to <a href='login.php'>Login</a> again.</p>
        </div>
    <?php else: ?>
        <form class="loginform" id="loginform" method="post" name="login">
            <h1 class="login-title">Login</h1>
            <input type="text" class="login-input" id="company_name" name="company_name" placeholder="Company Name" required/>
            <input type="text" class="login-input" id="username" name="username" placeholder="Username" required/>
            <input type="password" class="login-input" id="password" name="password" placeholder="Password" required/>
            <input type="submit" value="Login" name="submit" class="login-button"/>
            <p class="link"><a href="user_registration_new.php">New Registration</a></p>
        </form>
    <?php endif; ?>
</body>
</html>

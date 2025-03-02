<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Registration</title>
    <link rel="stylesheet" href="style.css"/>
    <script>
        function validatePasswords() {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;
            const submitButton = document.getElementById("submit_button");

            if (password === confirmPassword) {
                submitButton.disabled = false;
                document.getElementById("password_error").textContent = "";
            } else {
                submitButton.disabled = true;
                document.getElementById("password_error").textContent = "Passwords do not match.";
            }
        }
    </script>
</head>
<body>
<?php
require('../db.php'); // Assumes `db.php` connects to the database

// Function to log errors
function logError($message) {
    $logFile = 'logs/app.log'; // Change path if needed
    $formattedMessage = "[" . date("Y-m-d H:i:s") . "] " . $message . "\n";
    error_log($formattedMessage, 3, $logFile);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'], $_POST['email'], $_POST['company_name'])) {
    // Sanitize inputs
    $username = mysqli_real_escape_string($con, stripslashes($_POST['username']));
    $email = mysqli_real_escape_string($con, stripslashes($_POST['email']));
    $password = mysqli_real_escape_string($con, stripslashes($_POST['password']));
    $company_name = mysqli_real_escape_string($con, stripslashes($_POST['company_name']));
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $create_datetime = date("Y-m-d H:i:s");
    logError("Debug: Function reached this point.");


    // Check if the company exists
    $stmt = $con->prepare("SELECT id FROM companies WHERE LOWER(name) = LOWER(?)");    
    if (!$stmt) {
        logError("Prepare failed: " . mysqli_error($con));
    } else {
        logError("Debug: passed company check.");
        $stmt->bind_param("s", $company_name);
        $stmt->execute();
        $result = $stmt->get_result();
    }


    if ($result && $result->num_rows > 0) {
        // Company exists, get the company ID
        $company_data = $result->fetch_assoc();
        $company_id = $company_data['id'];

        // Check if username already exists within the company
        $stmt = $con->prepare("SELECT id FROM users WHERE company_id = ? AND username = ?");
        if (!$stmt) {
            logError("Prepare failed (username check): " . mysqli_error($con));
        } else {
            logError("Passed username check.");
            $stmt->bind_param("is", $company_id, $username);
            $stmt->execute();
            $user_check_result = $stmt->get_result();
        }

        if ($user_check_result && $user_check_result->num_rows > 0) {
            // Username already exists within the same company
            echo "<div class='form'>
                      <h3>Username already exists for this company. Please choose a different username.</h3>
                      <p class='link'>Click here to <a href='user_registration_new.php'>register</a> again.</p>
                  </div>";
        } else {
            // Register the new user
            $stmt = $con->prepare("
                INSERT INTO `users` (company_id, username, email, password, role, date_time) 
                VALUES (?, ?, ?, ?, 'admin', ?)
            ");
            if (!$stmt) {
                logError("Prepare failed (user insertion): " . mysqli_error($con));
            }
            $stmt->bind_param("issss", $company_id, $username, $email, $hashed_password, $create_datetime);

            if ($stmt->execute()) {
                echo "<div class='form'>
                          <h3>User registered successfully under existing company.</h3>
                          <p class='link'>Click here to <a href='login.php'>Login</a></p>
                      </div>";
            } else {
                logError("Execution failed (user insertion): " . mysqli_error($con));
                echo "<div class='form'>
                          <h3>Error registering the user. Please try again.</h3>
                          <p class='link'>Click here to <a href='user_registration_new.php'>register</a> again.</p>
                      </div>";
            }
        }
    } else {
        // Company doesn't exist, create a new company
        $stmt = $con->prepare("INSERT INTO companies (name) VALUES (?)");
        if (!$stmt) {
            logError("Prepare failed (company insertion): " . mysqli_error($con));
        } else {
            logError("Passed company check.");
            $stmt->bind_param("s", $company_name);
        }

        if ($stmt->execute()) {
            $company_id = $con->insert_id;

            // Register the new user
            $stmt = $con->prepare("
                INSERT INTO `users` (company_id, username, email, password, role, date_time) 
                VALUES (?, ?, ?, ?, 'admin', ?)
            ");
            if (!$stmt) {
                logError("Prepare failed (user insertion after company creation): " . mysqli_error($con));
            }
            $stmt->bind_param("issss", $company_id, $username, $email, $hashed_password, $create_datetime);

            if ($stmt->execute()) {
                echo "<div class='form'>
                          <h3>New company created and user registered successfully.</h3>
                          <p class='link'>Click here to <a href='login.php'>Login</a></p>
                      </div>";
            } else {
                logError("Execution failed (user insertion after company creation): " . mysqli_error($con));
                echo "<div class='form'>
                          <h3>Error registering the user. Please try again.</h3>
                          <p class='link'>Click here to <a href='user_registration_new.php'>register</a> again.</p>
                      </div>";
            }
        } else {
            logError("Execution failed (company insertion): " . mysqli_error($con));
            echo "<div class='form'>
                      <h3>Error creating the company. Please try again.</h3>
                      <p class='link'>Click here to <a href='user_registration_new.php'>register</a> again.</p>
                  </div>";
        }
    }
} else {
?>
    <form class="loginform" action="" method="post">
        <h1 class="login-title">Company Registration</h1>
        <input type="text" class="login-input" name="company_name" placeholder="Company Name" required />
        <input type="text" class="login-input" name="username" placeholder="Username" required />
        <input type="email" class="login-input" name="email" placeholder="Email Address" required />
        <input type="password" class="login-input" id="password" name="password" placeholder="Password" required onkeyup="validatePasswords()" />
        <input type="password" class="login-input" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required onkeyup="validatePasswords()" />
        <p id="password_error" style="color: red;"></p>
        <input type="submit" id="submit_button" name="submit" value="Register" class="login-button" disabled>
        <p class="link"><a href="login.php">Click to Login</a></p>
    </form>
<?php } ?>
</body>
</html>

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

require('db.php');

if (isset($_POST['username'], $_POST['password'], $_POST['email'], $_POST['company_name'])) {
    $username = mysqli_real_escape_string($con, stripslashes($_POST['username']));
    $email = mysqli_real_escape_string($con, stripslashes($_POST['email']));
    $password = mysqli_real_escape_string($con, stripslashes($_POST['password']));
    $company_name = mysqli_real_escape_string($con, stripslashes($_POST['company_name']));
    $hashed_password = md5($password);
    $create_datetime = date("Y-m-d H:i:s");

    // Create a new database for the company
    $create_db_query = "CREATE DATABASE `$company_name`";
    if (mysqli_query($con, $create_db_query)) {
        // Connect to the new database
        $company_con = new mysqli("localhost", "root", "", $company_name);

        if ($company_con->connect_error) {
            die("Connection failed: " . $company_con->connect_error);
        }

        // Create `users` table in the new database
        $create_users_table_query = "
            CREATE TABLE IF NOT EXISTS `users` (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL,
                password VARCHAR(255) NOT NULL,
                date_time DATETIME NOT NULL
            )
        ";

        // Create `rm_master` table in the new database
        $create_rm_master_table_query = "
            CREATE TABLE IF NOT EXISTS `rm_master` (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                material VARCHAR(255) NOT NULL
            )
        ";

        // Create `rm_master_units` table in the new database
        $create_rm_master_units_table_query = "
            CREATE TABLE IF NOT EXISTS `rm_master_units` (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                unit VARCHAR(255) NOT NULL
            )
        ";
        // Create rm_purchase
        $create_rm_purchase_table_query = "
            CREATE TABLE IF NOT EXISTS `rm_purchase` (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                purchase_date DATE NOT NULL,
                material VARCHAR(255) NOT NULL,
                qty BIGINT NOT NULL,
                unit VARCHAR(255) NOT NULL
            )
        ";
        //create finished product
        $create_finished_products_table_query = "
            CREATE TABLE IF NOT EXISTS `finished_products` (
                id INT(11) AUTO_INCREMENT PRIMARY KEY, -- Unique identifier for the product
                product_name VARCHAR(255) NOT NULL,    -- Name of the finished product
                creation_date DATE DEFAULT CURRENT_DATE, -- Date the product was created
                status ENUM('active', 'inactive') DEFAULT 'active', -- Status of the product
                description TEXT,                      -- Optional description of the finished product
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp for when the row was created
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Timestamp for the last update
            )
        ";
        //create order_book
        $create_order_book = "
            CREATE TABLE `order_book` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `order_date` DATE NOT NULL,
                `product_name` VARCHAR(255) NOT NULL,
                `qty` DECIMAL(10, 2) NOT NULL,
                `customer_id` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`)
            );
        ";
        //create billing_name, place
        $create_customers = "
            CREATE TABLE `customers` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,           -- Unique identifier for each customer
                `billing_name` VARCHAR(255) NOT NULL,         -- Name of the person or company being billed
                `place` VARCHAR(255) NOT NULL,                -- Location of the customer (city, town, etc.)
                `gst_number` VARCHAR(20) DEFAULT NULL,       -- Optional GST number for the customer
                `email` VARCHAR(255) DEFAULT NULL,            -- Optional email of the customer
                `phone` VARCHAR(15) DEFAULT NULL,             -- Optional phone number of the customer
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp when the customer was created
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Timestamp for when the customer record was last updated
            );
        ";

    
        if (
            $company_con->query($create_users_table_query) &&
            $company_con->query($create_rm_master_table_query) &&
            $company_con->query($create_rm_master_units_table_query) &&
            $company_con->query($create_rm_purchase_table_query) &&
            $company_con->query($create_finished_products_table_query) &&
            $company_con->query($create_customers) &&
            $company_con->query($create_order_book)
        ) {
            // Insert user details into the `users` table
            $insert_user_query = "
                INSERT INTO `users` (username, email, password, date_time)
                VALUES ('$username', '$email', '$hashed_password', '$create_datetime')
            ";
            if ($company_con->query($insert_user_query)) {
                echo "<div class='form'>
                          <h3>Company database, tables, and user registration completed successfully.</h3>
                          <p class='link'>Click here to <a href='login.php'>Login</a></p>
                      </div>";
            } else {
                echo "<div class='form'>
                          <h3>Error inserting user data.</h3>
                          <p class='link'>Click here to <a href='user_registration_new.php'>register</a> again.</p>
                      </div>";
            }
        } else {
            echo "<div class='form'>
                      <h3>Error creating tables in the company database.</h3>
                      <p class='link'>Click here to <a href='user_registration_new.php'>register</a> again.</p>
                  </div>";
        }

        $company_con->close();
    } else {
        // Check if the database already exists
        if (mysqli_errno($con) == 1007) { // Error code 1007: Database already exists
            echo "<div class='form'>
                      <h3>A company database with this name already exists. Please choose a different name.</h3>
                      <p class='link'>Click here to <a href='user_registration_new.php'>register</a> again.</p>
                  </div>";
        } else {
            echo "<div class='form'>
                      <h3>Error creating database for the company.</h3>
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

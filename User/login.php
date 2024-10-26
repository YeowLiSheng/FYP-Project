<?php
session_start(); // Start the session for using session variables

// Check if the form is submitted
if (isset($_POST["loginbtn"])) {
    // Establish a database connection
    $con = mysqli_connect('localhost', 'root', '', 'fyp', 3306);
    
    // Check connection
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit();
    }
    
    // Set character set
    mysqli_set_charset($con, "utf8");
    
    // Sanitize and retrieve form inputs
    $email = trim(mysqli_real_escape_string($con, $_POST["email"]));
    $password = trim(mysqli_real_escape_string($con, $_POST["password"]));
    
    // Prepare the SQL query to get user by email
    $query = "SELECT * FROM user WHERE user_email = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    // Get the result and fetch the user data
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Check if user exists and verify password
    if ($row && password_verify($password, $row['user_password'])) {
        // Successful login
        $_SESSION['user_name'] = $row['user_name'];
        $_SESSION['ID'] = $row['ID'];

        // Redirect to a new page
        echo "<script>alert('Login successful!'); window.location.href='edit_profile.php';</script>";
    } else {
        echo '<script>alert("Invalid Email or Password");</script>';
    }

    // Close the statement and connection
    $stmt->close();
    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        #loginForm {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333333;
        }

        .field {
            margin-bottom: 15px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"],
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .error {
            color: red;
            font-size: 0.9em;
            display: none; /* Initially hide error messages */
        }

        .eye-icon {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 35%;
            user-select: none;
        }

        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }

        .forgot-password a {
            color: #28a745;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <form id="loginForm" method="POST" action="">
        <h2>Login</h2>
        <p style="text-align: center; margin-top: 20px;">
            Don't have an account? <a href="register.php" style="color: #28a745; text-decoration: none;">Resgister</a>
        </p>

        <div class="field">
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" required oninput="checkEmail()">
            <span id="emailError" class="error">Please enter a valid email (must include '@' AND '.')</span>
        </div>

        <div class="field">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <span id="passwordToggle" class="eye-icon" onclick="togglePassword('password', this)">👁️</span>
            <span id="passwordError" class="error">Please enter the correct password.</span>
        </div>

        <p>
            <input type="submit" name="loginbtn" value="Log In">
        </p>

        <div class="forgot-password">
            <p><a href="reset_password.php">Forgot Password?</a></p>
        </div>
    </form>

    <script>
        function checkEmail() {
            const email = document.getElementById("email").value;
            const emailError = document.getElementById("emailError");
            const validEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            emailError.style.display = validEmail.test(email) ? "none" : "block";
        }

        function togglePassword(inputId, toggleIcon) {
            const passwordInput = document.getElementById(inputId);
            const inputType = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', inputType);
        }

        function validateForm() {
            let hasError = false;

            // Check email validity
            const emailInput = document.getElementById("email");
            const emailError = document.getElementById("emailError");
            if (!emailInput.value || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
                emailError.style.display = "block";
                hasError = true;
            } else {
                emailError.style.display = "none"; // Hide error if valid
            }

            return !hasError;
        }

        // Ensure form validation before submission
        document.getElementById("loginForm").onsubmit = function() {
            return validateForm();
        };
    </script>
</body>
</html>

<?php
session_start(); // Start the session

// Check if the form is submitted
if (isset($_POST["resetPasswordBtn"])) {
    // Establish a database connection
    $con = mysqli_connect('localhost', 'root', '', 'fyp', 3306);
    
    // Check connection
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit();
    }

    // Sanitize and retrieve form inputs
    $email = trim(mysqli_real_escape_string($con, $_POST["email"]));
    
    // Prepare the SQL query to check if the email exists
    $query = "SELECT * FROM user WHERE user_email = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    // Get the result and check if the user exists
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // Generate a random OTP
        $otp = rand(100000, 999999); // Generates a 6-digit OTP
        
        // Store OTP in the session
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email; // Store the email in session for later use

        // Send OTP to user's email
        $subject = "Your OTP for Password Reset";
        $message = "Your OTP for resetting your password is: " . $otp;
        $headers = "From: noreply@example.com"; // Change this to your sender email

        if (mail($email, $subject, $message, $headers)) {
            echo "<script>alert('OTP has been sent to your email. Please check your inbox.'); window.location.href='verify_otp.php';</script>";
        } else {
            echo "<script>alert('Failed to send OTP. Please try again later.'); window.location.href='reset_password.php';</script>";
        }
    } else {
        echo "<script>alert('Email not found.'); window.location.href='reset_password.php';</script>";
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
    <title>Reset Password</title>
    <style>
        /* styles.css */

body {
    font-family: Arial, sans-serif;
    background-color: #f0f0f0;
    margin: 0;
    padding: 0;
}

form {
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

input[type="email"],
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

.login-link {
    text-align: center;
    margin-top: 15px;
}

.login-link a {
    color: #28a745;
    text-decoration: none;
}

    </style>
</head>
<body>
    <form method="POST" action="">
        <h2>Reset Password</h2>
        <div class="field">
            <label for="email">Enter your email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <p>
            <input type="submit" name="resetPasswordBtn" value="Get OTP">
        </p>
        <div class="login-link">
            <p><a href="login.php">Back to Login</a></p>
        </div>
    </form>
</body>
</html>

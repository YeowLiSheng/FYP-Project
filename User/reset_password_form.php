<?php
session_start();

// Check if the user has verified OTP
if (!isset($_SESSION['otp'])) {
    echo '<script>alert("OTP verification is required first."); window.location.href = "forgot_password.html";</script>';
    exit();
}

if (isset($_POST['resetPassword'])) {
    // Get new password from form
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Check if passwords match
    if ($newPassword == $confirmPassword) {
        // Update password in the database
        $email = $_SESSION['otp_email'];
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT); // Hash the password

        // Establish a database connection
        $con = mysqli_connect('localhost', 'root', '', 'fyp', 3306);
        
        // Check connection
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
            exit();
        }

        // Update the password in the database
        $query = "UPDATE user SET user_password = ? WHERE user_email = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("ss", $hashedPassword, $email);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo '<script>alert("Password has been reset successfully."); window.location.href = "login.php";</script>';
        } else {
            echo '<script>alert("Password reset failed. Please try again.");</script>';
        }

        // Close the statement and connection
        $stmt->close();
        $con->close();
    } else {
        echo '<script>alert("Passwords do not match. Please try again.");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Your Password</h2>
    <form method="POST">
        <label for="newPassword">New Password:</label>
        <input type="password" name="newPassword" id="newPassword" required>
        <br>
        <label for="confirmPassword">Confirm Password:</label>
        <input type="password" name="confirmPassword" id="confirmPassword" required>
        <br>
        <p><input type="submit" name="resetPassword" value="Reset Password"></p>
    </form>
</body>
</html>

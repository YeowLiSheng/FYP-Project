<?php
session_start(); // Start the session

if (isset($_POST["resetPasswordBtn"])) {
    // Establish a database connection
    $con = mysqli_connect('localhost', 'root', '', 'fyp', 3306);
    
    // Check connection
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit();
    }

    // Sanitize and retrieve form inputs
    $newPassword = trim(mysqli_real_escape_string($con, $_POST["newPassword"]));
    $email = $_SESSION['email']; // Retrieve email from session

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the password in the database
    $query = "UPDATE user SET user_password = ? WHERE user_email = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ss", $hashedPassword, $email);
    
    if ($stmt->execute()) {
        echo "<script>alert('Password has been reset successfully. You can now log in.'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Failed to reset password. Please try again.'); window.location.href='new_password.php';</script>";
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
    <title>New Password</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your existing CSS file -->
</head>
<body>
    <form method="POST" action="">
        <h2>Set New Password</h2>
        <div class="field">
            <label for="newPassword">New Password:</label>
            <input type="password" id="newPassword" name="newPassword" required>
        </div>
        <p>
            <input type="submit" name="resetPasswordBtn" value="Reset Password">
        </p>
    </form>
</body>
</html>

<?php
session_start(); // Start the session

if (isset($_POST["verifyOtpBtn"])) 
{
    // Check if the OTP matches
    if ($_POST['otp'] == $_SESSION['otp']) {
        echo "<script>alert('OTP verified. You can now reset your password.'); window.location.href='new_password.php';</script>";
    } else {
        echo "<script>alert('Invalid OTP. Please try again.'); window.location.href='verify_otp.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your existing CSS file -->
</head>
<body>
    <form method="POST" action="">
        <h2>Verify OTP</h2>
        <div class="field">
            <label for="otp">Enter the OTP sent to your email:</label>
            <input type="text" id="otp" name="otp" required>
        </div>
        <p>
            <input type="submit" name="verifyOtpBtn" value="Verify OTP">
        </p>
        <div class="login-link">
            <p><a href="reset_password.php">Back to Reset Password</a></p>
        </div>
    </form>
</body>
</html>

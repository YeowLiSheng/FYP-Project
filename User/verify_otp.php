<?php
session_start();

// Check if the OTP is sent
if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_email'])) {
    echo '<script>alert("Session expired or OTP not found."); window.location.href = "forgot_password.html";</script>';
    exit();
}

if (isset($_POST['verifyOtp'])) {
    // Get OTP from form
    $userOtp = $_POST['otp'];
    
    // Check if OTP matches
    if ($userOtp == $_SESSION['otp']) {
        // OTP is correct, proceed with password reset
        echo '<script>alert("OTP verified successfully. You can now reset your password."); window.location.href = "reset_password_form.php";</script>';
    } else {
        echo '<script>alert("Invalid OTP. Please try again.");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
</head>
<body>
    <h2>Verify OTP</h2>
    <form method="POST">
        <label for="otp">Enter OTP:</label>
        <input type="text" name="otp" id="otp" required>
        <p><input type="submit" name="verifyOtp" value="Verify OTP"></p>
    </form>
</body>
</html>

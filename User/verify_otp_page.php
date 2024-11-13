<?php
session_start();
$error = "";

if (isset($_POST['verify_otp'])) {
    $entered_otp = $_POST['otp'];
    $otp_generated_time = $_SESSION['otp_generated_time'];
    $current_time = time();
    
    // Check if OTP has expired (1 minute = 60 seconds)
    if ($current_time - $otp_generated_time > 60) {
        $error = "OTP has expired. Please request a new one.";
        unset($_SESSION['otp']); // Clear expired OTP
        unset($_SESSION['otp_generated_time']); // Clear OTP generation time
        $expired = true; // Flag to show "Request New OTP" button
    } elseif ($entered_otp == $_SESSION['otp']) {
        // OTP verified, redirect to reset password page
        header("Location: resetpassword_page.php");
        exit();
    } else {
        $error = "Invalid OTP. Please try again.";
    }
}
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Verify OTP</title>
</head>
<body>
    <div class="container">
        <h1>Verify OTP</h1>
        <form method="POST">
            <label>Enter OTP:</label>
            <input type="text" name="otp" required>
            <input type="submit" value="Verify" name="verify_otp">
        </form>
        <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
        
        <?php if (isset($expired) && $expired): ?>
            <!-- Button to go back to forget_password.php if OTP expired -->
            <form action="forget_password.php" method="GET">
                <button type="submit">Request New OTP</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

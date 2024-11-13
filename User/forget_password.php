<?php
session_start();
include("dataconnection.php");
$error = "";

if (isset($_GET["submitbtn"])) {
    $cust_email = $_GET["email"];
    $cust_email = mysqli_real_escape_string($connect, $cust_email);
    $result = mysqli_query($connect, "SELECT * FROM user WHERE user_email='$cust_email'");
    $count = mysqli_num_rows($result);
    $row = mysqli_fetch_assoc($result);

    if ($count == 1) {
        // Generate OTP
        $otp = rand(100000, 999999); // 6-digit OTP
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $cust_email; // Save email in session to verify later
        $_SESSION['otp_generated_time'] = time(); // Save the current timestamp

        // Email details
        $to = $cust_email;
        $subject = 'Reset Your Password - OTP Verification';
        $content = 'Hi ' . $row["user_name"] . ', welcome back to our website. Use the following OTP to reset your password: <b>' . $otp . '</b>';
        $headers = "From: dreamsstation67@gmail.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if (mail($to, $subject, $content, $headers)) {
            ?>
            <script>
                alert("OTP Sent! Please check your email.");
            </script>
            <?php
            header("refresh:0.5; url=verify_otp_page.php"); // Redirect to OTP verification page
        } else {
            ?>
            <script>
                alert("Failed to send OTP! Please try again.");
            </script>
            <?php
            header("refresh:0.5; url=forgetpassword_page.php");
        }
    } else {
        ?>
        <script>
            alert("Please enter a registered email!");
        </script>
        <?php
    }
}
?>


<!DOCTYPE HTML>
<html>
<head>
    <title>Forget Password</title>
    <style>
        /* Style as needed */
    </style>
</head>
<body>
    <div class="container">
        <h1>Forget Password</h1>
        <form name="login" method="GET" onsubmit="return check()">
            <label>Enter Your Email:</label>
            <input type="email" name="email" required>
            <input type="submit" value="Next" name="submitbtn">
        </form>
        <div class="back-link">
            <a href="login.php">Back to Login Page</a>
        </div>
    </div>
</body>
</html>

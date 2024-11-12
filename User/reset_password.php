<!-- forgot_password.html -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        .error {
            color: red;
            display: none;
        }
    </style>
</head>
<body>
    <form method="POST" action="reset_password.php" onsubmit="checkEmail()">
        <h2>Forgot Password</h2>
        <div class="field">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required oninput="checkEmail()">
            <span id="emailError" class="error">Please enter a valid email (must include '@' AND '.')</span>
        </div>

        <p>
            <input type="submit" name="sendOtp" value="Send OTP">
        </p>
    </form>

    <script>
        function checkEmail() {
            const email = document.getElementById("email").value;
            const emailError = document.getElementById("emailError");
            const validEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            emailError.style.display = validEmail.test(email) ? "none" : "block";
        }
    </script>
</body>
</html>


<?php
session_start(); // Start session to store OTP

require "Mail/phpmailer/PHPMailer.php";
require_once "Mail/phpmailer/PHPMailerAutoload.php";

if (isset($_POST['sendOtp'])) {
    $con = mysqli_connect('localhost', 'root', '', 'fyp', 3306);
    
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit();
    }

    $email = trim(mysqli_real_escape_string($con, $_POST['email']));
    
    $query = "SELECT * FROM user WHERE user_email = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_email'] = $email;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();  
            $mail->Host = 'smtp.gmail.com';  
            $mail->SMTPAuth = true;  
            $mail->Username = 'dreamsttation79@gmail.com'; // Your Gmail username  
            $mail->Password = 'dorh ymmp hpmy oxoj'; // App-specific password
            $mail->SMTPSecure = 'tls'; 
            $mail->Port = 587;

            $mail->setFrom('dreamsttation79@gmail.com', 'Your Company');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body    = "Your OTP for password reset is: <b>$otp</b>";

            $mail->send();
            echo '<script>alert("OTP has been sent to your email."); window.location.href = "verify_otp.php";</script>';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo '<script>alert("Email not found in our records.");</script>';
    }

    $stmt->close();
    $con->close();
}
?>

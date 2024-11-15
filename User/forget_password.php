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
        /* Reset some basic styles for uniformity */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* General body styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Container for the form */
        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        /* Heading style */
        h1 {
            text-align: center;
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        /* Form styling */
        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 16px;
            color: #555;
            margin-bottom: 8px;
        }

        input[type="email"], input[type="submit"] {
            padding: 12px;
            margin-bottom: 16px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            outline: none;
        }

        input[type="email"]:focus, input[type="submit"]:hover {
            border-color: #007BFF;
            transition: 0.3s;
        }

        /* Submit button styling */
        input[type="submit"] {
            background-color: #007BFF;
            color: #fff;
            cursor: pointer;
            font-weight: bold;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        /* Back link styling */
        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #007BFF;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        /* Error message styling */
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: -10px;
            margin-bottom: 10px;
        }

        /* Small device styling */
        @media (max-width: 600px) {
            .container {
                padding: 20px;
                max-width: 90%;
            }
        }

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

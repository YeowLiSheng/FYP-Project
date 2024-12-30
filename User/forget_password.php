<?php
session_start();
include("dataconnection.php");

if (isset($_GET["submitbtn"])) {
    $cust_email = $_GET["email"];
    $cust_email = mysqli_real_escape_string($connect, $cust_email);
    $result = mysqli_query($connect, "SELECT * FROM user WHERE user_email='$cust_email'");
    $count = mysqli_num_rows($result);
    $row = mysqli_fetch_assoc($result);

    if ($count == 1) {
        // Generate OTP
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $cust_email;
        $_SESSION['otp_generated_time'] = time();

        // Email details
        $to = $cust_email;
        $subject = 'Reset Your Password - OTP Verification';
        $content = 'Hi ' . $row["user_name"] . ', welcome back to our website. Use the following OTP to reset your password: <b>' . $otp . '</b>';
        $headers = "From: dreamsstation67@gmail.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if (mail($to, $subject, $content, $headers)) {
            echo "<!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'OTP Sent!',
                        text: 'Please check your email for the OTP.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'verify_otp_page.php';
                    });
                </script>
            </body>
            </html>";
            exit();
        } else {
            echo "<!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Send OTP',
                        text: 'Please try again later.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                    window.location.href = 'forget_password.php';
                });
                </script>
            </body>
            </html>";
            exit();
        }
    } else {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Email Not Found',
                    text: 'Please enter a registered email address.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'forget_password.php';
                });
            </script>


         
        </body>
        </html>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forget Password</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
        font-family: 'Arial', sans-serif;
        background-color: #f7f9fc;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .container {
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        padding: 40px 30px;
        width: 100%;
        max-width: 450px;
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .container:hover {
        transform: translateY(-10px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
    }

    h1 {
        font-size: 24px;
        color: #333;
        margin-bottom: 20px;
        font-weight: 600;
    }

    label {
        font-size: 16px;
        color: #555;
        margin-bottom: 10px;
        display: inline-block;
        text-align: left;
        font-weight: 500;
    }

    input[type="email"], input[type="submit"] {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
        box-sizing: border-box;
        transition: border-color 0.3s ease;
    }

    input[type="email"]:focus, input[type="submit"]:focus {
        border-color: #4CAF50;
        outline: none;
    }

    input[type="submit"] {
        background-color: #4CAF50;
        color: white;
        cursor: pointer;
        font-weight: 600;
        transition: background-color 0.3s ease;
    }

    input[type="submit"]:hover {
        background-color: #45a049;
    }

    p {
        margin-top: 20px;
        font-size: 14px;
    }

    a {
        color: #555;
        font-size: 14px;
        text-decoration: none;
    }

    a:hover {
        color: #4CAF50;
    }

    </style>
</head>
<body>
    <div class="container">
        <h1>Forget Password</h1>
        <form method="GET" action="">
            <label for="email">Enter Your Email:</label><br>
            <input type="email" name="email" id="email" required>
            <input type="submit" name="submitbtn" value="Next">
        </form>
        <p><a href="login.php">Back to Login Page</a></p>
    </div>
</body>
</html>

<?php
session_start();
include("dataconnection.php");
$error = "";

if (isset($_POST['verify_otp'])) {
    $entered_otp = $_POST['otp'];

    // Check if the entered OTP is numeric and exactly 6 digits
    if (!ctype_digit($entered_otp) || strlen($entered_otp) !== 6) {
        $error = "Invalid OTP format. Please enter a 6-digit numeric OTP.";
    } elseif (!isset($_SESSION['otp_generated_time'])) {
        $error = "OTP has expired. Please request a new one.";
        $expired = true;
    } else {
        $otp_generated_time = $_SESSION['otp_generated_time'];
        $current_time = time();

        if ($current_time - $otp_generated_time > 30) {
            $error = "OTP has expired. Please request a new one.";
            unset($_SESSION['otp']); // Clear expired OTP
            unset($_SESSION['otp_generated_time']); // Clear OTP generation time
            $expired = true;
        } elseif ($entered_otp == $_SESSION['otp']) {
    // OTP verified, show success popup and redirect to reset password page
    echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'OTP Verified!',
                    text: 'Your OTP has been successfully verified. You will be redirected to the reset password page.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'resetpassword_page.php';
                });
            </script>
        </body>
        </html>";
    exit();
        } else {
            $error = "Invalid OTP. Please try again.";
        }
    }
}

// Handle OTP resend request
if (isset($_POST['resend_otp'])) {
    if (isset($_SESSION['email'])) {
        $cust_email = $_SESSION['email'];
        
        // Generate new OTP
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_generated_time'] = time(); // Update OTP generation time

        // Resend Email with new OTP
        $to = $cust_email;
        $subject = 'Reset Your Password - New OTP Verification';
        $content = 'Hello, here is your new OTP for password reset: <b>' . $otp . '</b>';
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
                        title: 'New OTP Sent!',
                        text: 'A new OTP has been sent to your email.',
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
                        text: 'Failed to send a new OTP. Please try again.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'verify_otp_page.php';
                    });
                </script>
            </body>
            </html>";
            exit();
        }
    }
}
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Verify OTP</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            background-color: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Container for the form */
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

        /* Heading style */
        h1 {
            text-align: center;
            color: #333;
            font-size: 28px;
            margin-bottom: 25px;
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

        input[type="text"], input[type="submit"], button {
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            outline: none;
        }

        input[type="text"]:focus, input[type="submit"]:hover, button:hover {
            border-color: #4CAF50;
            transition: 0.3s;
        }

        /* Submit button styling */
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        /* Button for requesting new OTP */
        button {
            background-color: #FF6347;
            color: white;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #e55347;
        }

        /* Back link styling */
        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #555;
        font-size: 14px;
        text-decoration: none;
        }

        .back-link a:hover {
            color: #4CAF50;
        }

        /* Error message styling */
        p {
            color: red;
            font-size: 14px;
            margin-top: 10px 0px 0px 10px;
            padding: 10px;
        }

        /* Small device styling */
        @media (max-width: 600px) {
            .container {
                padding: 25px;
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verify OTP</h1>
        <form method="POST">
            <label>Enter OTP:</label>
            <input type="text" name="otp" required>
            <input type="submit" value="Verify" name="verify_otp">
        </form>
        <?php if (!empty($error)) { echo "<p>$error</p>"; } ?>

        <?php if (isset($expired) && $expired): ?>
            <!-- Button to request new OTP if expired -->
            <form method="POST">
                <button type="submit" name="resend_otp">Request New OTP</button>
            </form>
        <?php endif; ?>

        <!-- Back to Website button -->
        <div class="back-link">
            <a href="login.php">Back to Login Page</a>
        </div>
    </div>
</body>
</html>

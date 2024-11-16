<?php
session_start();
include("dataconnection.php");

if (!isset($_SESSION['email'])) {
    header("Location: forgetpassword_page.php");
    exit();
}

$email = $_SESSION['email'];
$error = "";

if (isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $password_regex = "/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/";

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (!preg_match($password_regex, $new_password)) {
        $error = "Password must include 1 uppercase letter, 1 number, 1 special character, and be at least 8 characters long.";
    } else {
        $query = "SELECT user_password FROM user WHERE user_email='$email'";
        $result = mysqli_query($connect, $query);
        $row = mysqli_fetch_assoc($result);

        if ($row['user_password'] === $new_password) {
            $error = "New password cannot be the same as the current password.";
        } else {
            $sql = "UPDATE user SET user_password='$new_password' WHERE user_email='$email'";
            $result = mysqli_query($connect, $sql);

            if ($result) {
                unset($_SESSION['email']);
                ?>
                <script>
                    alert("Password reset successfully! Please log in with your new password.");
                </script>
                <?php
                header("refresh:0.5; url=login.php");
                exit();
            } else {
                $error = "Failed to update password. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>

    <!-- Include Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        /* General styling */
        body {
            font-family: 'Poppins', sans-serif;
            background: #f1f1f1;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: #ffffff;
            padding: 20px 70px 50px 45px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            box-sizing: border-box;
        }

        h1 {
            text-align: center;
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        p {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
        }

        label {
            font-size: 14px;
            margin-bottom: 5px;
            color: #333;
            display: block;
            font-weight: 500;
        }

        .password-container {
            position: relative;
        }

        input[type="password"], input[type="text"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0 15px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            background: #f9f9f9;
            transition: all 0.3s ease;
        }

        input[type="password"]:focus, input[type="text"]:focus {
            border-color: #2575fc;
            background-color: #fff;
            outline: none;
        }

        .eye-icon {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 24px;
            color: #aaa;
            transition: color 0.3s ease;
        }

        .eye-icon:hover {
            color: #2575fc; /* Highlight on hover */
        }

        input[type="submit"] {
            width: 108%;
            padding: 12px;
            background-color: #2575fc;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #6a11cb;
        }

        .error-message {
            color: red;
            text-align: center;
            font-size: 14px;
            margin-top: 10px;
        }

        #error-message {
            text-align: center;
            font-weight: 500;
        }

        @media (max-width: 600px) {
            .container {
                width: 90%;
                padding: 25px;
            }
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

    </style>
</head>
<body>

    <div class="container">
        <h1>Reset Your Password</h1>
        <p>You're about to reset the password for: <strong><?php echo htmlspecialchars($email); ?></strong></p>

        <form method="POST" onsubmit="return validatePassword()">
            <label for="new_password">New Password:</label>
            <div class="password-container">
                <input type="password" name="new_password" id="new_password" required>
                <span class="eye-icon" id="toggleNewPassword" onclick="togglePasswordVisibility('new_password')">
                    <i class="fas fa-eye"></i>
                </span>
            </div>

            <label for="confirm_password">Confirm New Password:</label>
            <div class="password-container">
                <input type="password" name="confirm_password" id="confirm_password" required>
                <span class="eye-icon" id="toggleConfirmPassword" onclick="togglePasswordVisibility('confirm_password')">
                    <i class="fas fa-eye"></i>
                </span>
            </div>

            <input type="submit" value="Reset Password" name="reset_password">
        </form>

        <p id="error-message" class="error-message"><?php if (isset($error)) { echo $error; } ?></p>

        <div class="back-link">
            <a href="verify_otp_page.php">Back to Verify Otp</a>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(id) {
            var passwordField = document.getElementById(id);
            var eyeIcon = document.getElementById("toggle" + id.charAt(0).toUpperCase() + id.slice(1));

            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.innerHTML = '<i class="fas fa-eye-slash"></i>'; // Change to eye-slash icon
            } else {
                passwordField.type = "password";
                eyeIcon.innerHTML = '<i class="fas fa-eye"></i>'; // Change back to eye icon
            }
        }

        function validatePassword() {
            const newPassword = document.getElementById("new_password").value;
            const confirmPassword = document.getElementById("confirm_password").value;
            const errorMessage = document.getElementById("error-message");
            const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

            if (newPassword !== confirmPassword) {
                errorMessage.textContent = "Passwords do not match!";
                return false;
            } else if (!passwordRegex.test(newPassword)) {
                errorMessage.textContent = "Password must include 1 uppercase letter, 1 number, 1 special character, and be at least 8 characters long.";
                return false;
            } else {
                errorMessage.textContent = ""; // Clear any error messages
                return true;
            }
        }
    </script>

</body>
</html>

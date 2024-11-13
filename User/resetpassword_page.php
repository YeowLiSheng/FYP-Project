<?php
session_start();
include("dataconnection.php");

if (!isset($_SESSION['email'])) {
    // Redirect if no email is set in session (user hasn't verified OTP)
    header("Location: forgetpassword_page.php");
    exit();
}

$email = $_SESSION['email'];
$error = "";

if (isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Password validation regex
    $password_regex = "/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/";

    // Validate new password and confirm password match
    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (!preg_match($password_regex, $new_password)) {
        $error = "Password must include 1 uppercase letter, 1 number, 1 special character, and be at least 8 characters long.";
    } else {
        // Check if the new password is different from the current password
        $query = "SELECT user_password FROM user WHERE user_email='$email'";
        $result = mysqli_query($connect, $query);
        $row = mysqli_fetch_assoc($result);

        if ($row['user_password'] === $new_password) {
            $error = "New password cannot be the same as the current password.";
        } else {
            // Update the plain text password in the database
            $sql = "UPDATE user SET user_password='$new_password' WHERE user_email='$email'";
            $result = mysqli_query($connect, $sql);

            if ($result) {
                // Success: clear session and redirect to login
                unset($_SESSION['email']); // Clear session variable
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
<html>
<head>
    <title>Reset Password</title>
    <style>
        .error-message {
            color: red;
        }
        /* Add your additional styling here */
    </style>
    <script>
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
</head>
<body>
    <div class="container">
        <h1>Reset Password</h1>
        <p>Resetting password for: <?php echo htmlspecialchars($email); ?></p>
        <form method="POST" onsubmit="return validatePassword()">
            <label>New Password:</label>
            <input type="password" name="new_password" id="new_password" required>

            <label>Confirm New Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>

            <input type="submit" value="Reset Password" name="reset_password">
        </form>
        <p id="error-message" class="error-message"><?php if (isset($error)) { echo $error; } ?></p>
    </div>
</body>
</html>

<?php
// Include the database connection
include("dataconnection.php");

// Start the session
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the user_id from session
    $user_id = $_SESSION['id']; 

    // Get the new password, confirm password from the form
    $new_password = mysqli_real_escape_string($connect, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($connect, $_POST['confirm_password']);

    // Fetch the user's current password from the database
    $result = mysqli_query($connect, "SELECT user_password FROM user WHERE user_id = '$user_id'");
    $row = mysqli_fetch_assoc($result);
    $current_password = $row['user_password'];

    // Check if the new password is the same as the current password
    if ($new_password === $current_password) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'New password cannot be the same as the current password.',
                    confirmButtonText: 'OK'
                });
            </script>
        </body>
        </html>";
    }
    // Password validation: At least 1 uppercase letter, 1 number, 1 special character, and minimum 8 characters
    elseif (!preg_match("/[A-Z]/", $new_password) || !preg_match("/[0-9]/", $new_password) || !preg_match("/[^\w]/", $new_password) || strlen($new_password) < 8) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Password must include 1 uppercase letter, 1 number, 1 special character, and be 8 characters long.',
                    confirmButtonText: 'OK'
                });
            </script>
        </body>
        </html>";
    }
    // Check if the new password matches the confirm password
    elseif ($new_password !== $confirm_password) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Passwords do not match.',
                    confirmButtonText: 'OK'
                });
            </script>
        </body>
        </html>";
    }
    // If all checks pass, update the password
    else {
        // Update the password in the database
        $query = "UPDATE user SET user_password = '$new_password' WHERE user_id = '$user_id'";
        if (mysqli_query($connect, $query)) {
            echo "<!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Password updated successfully!',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'edit_profile.php';
                    });
                </script>
            </body>
            </html>";
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
                        title: 'Error: " . mysqli_error($connect) . "',
                        confirmButtonText: 'OK'
                    });
                </script>
            </body>
            </html>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Change Password</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f1f1f1;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh; /* Ensure the page height is 100% of the viewport */
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

        h2 {
            text-align: center;
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: 600;
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
            width: 100%;
        }

        input[type="password"], input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0 15px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            background: #f9f9f9;
            transition: all 0.3s ease;
            padding-right: 40px;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif; /* Ensure consistent font */
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        input[type="password"]:focus, input[type="text"]:focus, input[type="submit"]:focus {
            border-color: #4CAF50;
            background-color: #fff;
            outline: none;
        }

        .eye-icon {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            font-size: 24px;
            color: #aaa;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .eye-icon:hover {
            color: #4CAF50;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .back-link button {
            width: 100%;
            padding: 12px;
            background-color: #007bff; /* Blue background */
            color: white; /* White font */
            border: 1px solid #007bff; /* Matching border color */
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease, border-color 0.3s ease;
            margin-top: 15px;
        }

        .back-link button:hover {
            background-color: #0056b3; /* Darker blue on hover */
            border-color: #0056b3; /* Darker border on hover */
        }

        .back-link button:focus {
            outline: none;
            border-color: #0056b3;
            box-shadow: 0 0 5px rgba(0, 86, 179, 0.3); /* Blue glow when focused */
        }
    </style>
</head>
<body>
    <form action="change_password.php" method="POST" class="container">
        <h2>Change Password</h2>

        <!-- New Password -->
        <div class="form-group">
            <label for="new_password">New Password:</label>
            <div class="password-container">
                <input type="password" id="new_password" name="new_password" required>
                <span class="eye-icon" id="toggleNewPassword" onclick="togglePasswordVisibility('new_password', 'toggleNewPassword')">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label for="confirm_password">Confirm New Password:</label>
            <div class="password-container">
                <input type="password" id="confirm_password" name="confirm_password" required>
                <span class="eye-icon" id="toggleConfirmPassword" onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassword')">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
        </div>

        <!-- Submit Button -->
        <input type="submit" value="Change Password">

        <!-- Back to Edit Profile Button -->
        <div class="back-link">
            <button type="button" onclick="window.location.href='verify_password.php';">Back</button>
        </div>
    </form>

    <script>
        function togglePasswordVisibility(inputId, iconId) {
            var passwordField = document.getElementById(inputId);
            var eyeIcon = document.getElementById(iconId);

            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                passwordField.type = "password";
                eyeIcon.innerHTML = '<i class="fas fa-eye"></i>';
            }

            // Refresh styles to maintain consistency
            passwordField.style.fontFamily = getComputedStyle(passwordField).fontFamily;
        }
    </script>
</body>
</html>
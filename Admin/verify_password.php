<?php
// Start the session
session_start();

// Database connection
include("dataconnection.php");

// Check connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Ensure admin is logged in and session ID is available
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
} else {
    die("Admin not logged in. Please log in to continue.");
}

// Fetch admin data (not directly used in this script but kept for reference)
$result = mysqli_query($connect, "SELECT * FROM admin WHERE admin_id ='$admin_id'");

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'];

    // Fetch the password from the database
    $query = "SELECT admin_password FROM admin WHERE admin_id = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("s", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stored_password = $row['admin_password'];

        // Check if the entered password matches
        if ($old_password === $stored_password) {
            // Display a success SweetAlert and redirect to change_password.php
            echo "<!DOCTYPE html>
            <html>
            <head>
                <style>
                    body {
                        height: 100vh;
                        margin: 0;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    }
                    .swal2-container {
                        z-index: 9999; /* Ensure SweetAlert appears above the page content */
                    }
                </style>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Successful password verification!',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'change_password.php?edit_admin=$admin_id';
                    });
                </script>
            </body>
            </html>";
            exit;
        } else {
            $error_message = "Old password is incorrect.";
            echo "<!DOCTYPE html>
            <html>
            <head>
                <style>
                    body {
                        height: 100vh;
                        margin: 0;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    }
                    .swal2-container {
                        z-index: 9999; /* Ensure SweetAlert appears above the page content */
                    }
                </style>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: '$error_message',
                        confirmButtonText: 'OK'
                    });
                </script>
            </body>
            </html>";
        }
    } else {
        $error_message = "Admin not found.";
        echo "<!DOCTYPE html>
        <html>
        <head>
            <style>
                body {
                    height: 100vh;
                    margin: 0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
                .swal2-container {
                    z-index: 9999; /* Ensure SweetAlert appears above the page content */
                }
            </style>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '$error_message',
                    confirmButtonText: 'OK'
                });
            </script>
        </body>
        </html>";
    }
    
    $stmt->close();
}

$connect->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Verify Admin Password</title>
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
            width: 100%;
        }

        input[type="password"], input[type="submit"] {
            width: 100%; /* Ensure both elements take up the full width of their container */
            padding: 12px;
            margin: 10px 0 15px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            background: #f9f9f9;
            transition: all 0.3s ease;
            padding-right: 40px; /* Add padding to make room for the eye icon */
            box-sizing: border-box; /* Ensures padding is included in the width */
        }

        input[type="password"]:focus, input[type="submit"]:focus {
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
            width: 100%;
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
    <form action="verify_password.php" method="POST" class="container">
        <h1>Admin Safety Certification</h1>
        
        <!-- Old Password -->
        <div class="form-group">
            <label for="old_password">Old Password:</label>
            <div class="password-container">
                <input type="password" id="old_password" name="old_password" required>
                <span class="eye-icon" id="toggleOldPassword" onclick="togglePasswordVisibility()">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
        </div>
        
        <!-- Submit Button -->
        <input type="submit" value="Verify">
        
        <!-- Back to Edit Profile Button -->
        <div class="back-link">
            <button type="button" onclick="window.location.href='admin_edit_profile.php';">Go Back to Edit Profile</button>
        </div>
    </form>

    <script>
        function togglePasswordVisibility() {
            var passwordField = document.getElementById('old_password');
            var eyeIcon = document.getElementById("toggleOldPassword");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.innerHTML = "<i class='fas fa-eye-slash'></i>";
            } else {
                passwordField.type = "password";
                eyeIcon.innerHTML = "<i class='fas fa-eye'></i>";
            }
        }
    </script>
</body>
</html>

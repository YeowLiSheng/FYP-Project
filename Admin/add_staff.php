<?php

include 'dataconnection.php' ;
include 'admin_sidebar.php';


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Staff</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>


a {
    text-decoration: none;
}



.content {
    margin-left: 250px; /* Adjust this based on sidebar width */
}

        /* Background styling */
        body {
            background-color: #fafafa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }


        /* Center and style form container */
        .container {
            max-width: 600px;
            margin-top: 120px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Form heading */
        h2 {
            color: black;
            font-size: 1.8em;
            margin-bottom: 15px;
            
            text-align: center;
        }

        /* Label and input styling */
        .form-label {
            font-weight: bold;
            color: #374151;
        }

        .form-control {
            border: 1px solid #ddd;
            border-radius: 6px;
           
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: #00796b;
            box-shadow: 0 0 5px rgba(0, 121, 107, 0.4);
        }

        /* Error message styling */
        .text-danger {
            font-size: 0.85rem;
            color: #d32f2f;
            margin-top: 4px;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            line-height: 1.2;
            letter-spacing: 0.3px;
            display: none; /* Initially hidden */
            padding: 2px 0;
        }

        /* Eye icon styling */

        .eye-icon {
            position: absolute;
            right: 25px; /* Space from the right edge */
            top: 50px;
            transform: translateY(-50%); /* Center vertically */
            cursor: pointer; /* Pointer cursor for the eye icon */
            font-size: 18px; /* Increase the font size */
            color: #888; /* Default color */
            transition: color 0.3s ease; /* Smooth transition */
        }

        .eye-icon:hover {
            color: #333; /* Darker color on hover */
        }

        .eye-icon i {
            font-size: 20px; /* Adjust size if necessary */
            transition: transform 0.3s ease; /* Smooth transformation */
        }

        .eye-icon.clicked i {
            transform: rotate(180deg); /* Flip the eye icon when clicked */
        }

        /* Button styling */
        .btn-primary {
            
            color: white;
         

            width: 100%;
            background-color: #28a745;
            border: none;
            padding: 10px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #218838;
        }


        /* Back Button Styling */

        /* General styles for the button */
        .close-btn {
            display: inline-block;
            text-decoration: none;
            background-color: #ff4d4d; /* Red background */
            color: #fff; /* White text */
            font-size: 20px; /* Visible font size */
            font-weight: bold;
            border: none;
            border-radius: 5px; /* Slightly rounded edges for modern look */
            width: 40px;
            height: 40px;
            text-align: center;
            line-height: 40px; /* Center align the text */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Subtle shadow for depth */
            transition: transform 0.2s, box-shadow 0.2s; /* Smooth hover effects */
            cursor: pointer;
            position: absolute; /* Allows precise positioning */
            margin-top: -50px; /* Adjust top distance */
            right: 490px; /* Align to the right */
        }

        /* Hover effect */
        .close-btn:hover {
            background-color: #ff1a1a; /* Darker red on hover */
            transform: scale(1.1); /* Slight zoom on hover */
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.3); /* Enhanced shadow on hover */
        }

        /* Focus outline for accessibility */
        .close-btn:focus {
            outline: 2px solid #fff; /* White outline for focus */
            outline-offset: 2px;
        }

    </style>
</head>
<body>



    <div class="container">
        <h2>Add Staff</h2>
        <!-- Back Button -->
        <a href="view_admin.php" class="close-btn" aria-label="Close">&times;</a>

        <form action="add_staff.php" method="POST" id="addStaffForm">
            <!-- Admin ID and Full Name in grid -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="adminId" class="form-label">Admin ID (e.g., admin1, admin2)</label>
                    <input type="text" class="form-control" id="adminId" name="adminId">
                    <div id="check_id" class="text-danger" style="display: none;">Admin ID is required</div>
                </div>
                <div class="col-md-6">
                    <label for="fullName" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="fullName" name="fullName">
                    <div id="check_full" class="text-danger" style="display: none;">Full Name must be at least 5 characters</div>
                </div>
            </div>

            <!-- Password and Confirm Password in grid -->
            <div class="row mb-3">
                <div class="col-md-6 position-relative">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <span id="passwordEye" class="eye-icon fas fa-eye" onclick="togglePasswordVisibility('password')"></span>
                    <div id="check_pass" class="text-danger" style="display: none;">Password must be at least 8 characters</div>
                </div>
                <div class="col-md-6 position-relative">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword">
                    <span id="confirmPasswordEye" class="eye-icon fas fa-eye" onclick="togglePasswordVisibility('confirmPassword')"></span>
                    <div id="check_confirm_pass" class="text-danger" style="display: none;">Passwords must match</div>
                </div>
            </div>

            <!-- Email on its own line -->
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email">
                <div id="check_e" class="text-danger" style="display: none;">Enter a valid email address</div>
            </div>

            <!-- Contact Number on its own line -->
            <div class="mb-3">
                <label for="contactNumber" class="form-label">Contact Number</label>
                <input type="text" class="form-control" id="contactNumber" name="contactNumber">
                <div id="check_num" class="text-danger" style="display: none;">Enter a valid contact number</div>
            </div>
            
            <input type="submit" class="btn btn-primary" name="addstaff" value="Add Staff">
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePasswordVisibility(inputId) {
            const inputField = document.getElementById(inputId);
            inputField.type = inputField.type === 'password' ? 'text' : 'password';
        }

        // Form validation on submit
        document.getElementById('addStaffForm').addEventListener('submit', function (event) {
            const adminId = document.getElementById('adminId');
            const fullName = document.getElementById('fullName');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirmPassword');
            const email = document.getElementById('email');
            const contactNumber = document.getElementById('contactNumber');

            let isValid = true;

            // Admin ID validation
            if (!adminId.value) {
                isValid = false;
                document.getElementById('check_id').style.display = 'block';
            } else {
                document.getElementById('check_id').style.display = 'none';
            }

            // Full Name validation
            if (fullName.value.length < 5) {
                isValid = false;
                document.getElementById('check_full').style.display = 'block';
            } else {
                document.getElementById('check_full').style.display = 'none';
            }

            // Password validation
            if (password.value.length < 5) {
                isValid = false;
                document.getElementById('check_pass').style.display = 'block';
            } else {
                document.getElementById('check_pass').style.display = 'none';
            }

            // Confirm Password validation
            if (confirmPassword.value !== password.value) {
                isValid = false;
                document.getElementById('check_confirm_pass').style.display = 'block';
            } else {
                document.getElementById('check_confirm_pass').style.display = 'none';
            }

            // Email validation
            const emailRegex = /^[^@]+@[^@]+\.[a-z]{2,}$/;
            if (!emailRegex.test(email.value)) {
                isValid = false;
                document.getElementById('check_e').style.display = 'block';
            } else {
                document.getElementById('check_e').style.display = 'none';
            }

            // Contact Number validation
            if (!contactNumber.value.match(/^\d{3}-\d{7}|\d{3}-\d{8}$/)) {
                isValid = false;
                document.getElementById('check_num').style.display = 'block';
            } else {
                document.getElementById('check_num').style.display = 'none';
            }

            if (!isValid) {
                event.preventDefault();
                const firstErrorField = document.querySelector('.text-danger[style="display: block;"]');
                if (firstErrorField) {
                    firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    </script>
</body>
</html>



<?php
// Database connection
include 'dataconnection.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (isset($_POST["addstaff"])) {
    // Retrieve form data
    $id = mysqli_real_escape_string($connect, $_POST["adminId"]); 
    $name = mysqli_real_escape_string($connect, $_POST["fullName"]); 
    $password = $_POST["password"];  // Plain text password
    $confirmPassword = $_POST["confirmPassword"];
    $email = $_POST["email"];  
    $contact = $_POST["contactNumber"];  

    $now = new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur'));
    $currentDateTime = $now->format('Y-m-d H:i:s');

    // Check if adminId already exists
    $verify_id_query = mysqli_query($connect, "SELECT * FROM admin WHERE admin_id='$id'");
    if (mysqli_num_rows($verify_id_query) > 0) {
        echo "<script>alert('The Admin ID is already taken. Please choose another Admin ID.');window.location.href='add_staff.php';</script>";
    } else {
        // Check if email already exists
        $verify_email_query = mysqli_query($connect, "SELECT * FROM admin WHERE admin_email='$email'");
        if (mysqli_num_rows($verify_email_query) > 0) {
            echo "<script>alert('The email has already been used. Please choose another email.');window.location.href='add_staff.php';</script>";
        } else {
            // Check if contact number already exists
            $verify_contact_query = mysqli_query($connect, "SELECT * FROM admin WHERE admin_contact_number='$contact'");
            if (mysqli_num_rows($verify_contact_query) > 0) {
                echo "<script>alert('The telephone number is already in use. Please choose another number.');window.location.href='add_staff.php';</script>";
            } else if ($password != $confirmPassword) {
                echo "<script>alert('The password and confirm password must match.');window.location.href='add_staff.php';</script>";
            } else {
                // Insert data into the database without encryption
                $insert_query = mysqli_query($connect, "INSERT INTO admin (admin_id, admin_name, admin_contact_number, admin_password, admin_email, admin_joined_date) 
                VALUES ('$id', '$name', '$contact', '$password', '$email', '$currentDateTime')");
                
                if ($insert_query) {
                    echo "<script>alert('Registration successful.');window.location.href='view_admin.php';</script>";
                } else {
                    echo "Error: " . mysqli_error($connect); // Show the error message
                    echo "<script>alert('Registration failed. Please try again.');window.location.href='add_staff.php';</script>";
                }
            }
        }
    }
}

?>

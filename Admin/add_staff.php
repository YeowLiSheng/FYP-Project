<?php
include 'dataconnection.php';
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
            margin-left: 250px;
        }
        body {
            background-color: #fafafa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 600px;
            margin-top: 120px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: black;
            font-size: 1.8em;
            margin-bottom: 15px;
            text-align: center;
        }
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
        .text-danger {
            font-size: 0.85rem;
            color: #d32f2f;
            margin-top: 4px;
            display: none;
        }
        .eye-icon {
            position: absolute;
            right: 25px;
            top: 50px;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #888;
            transition: color 0.3s ease;
        }
        .eye-icon:hover {
            color: #333;
        }
        .eye-icon i {
            font-size: 20px;
            transition: transform 0.3s ease;
        }
        .eye-icon.clicked i {
            transform: rotate(180deg);
        }
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
        .close-btn {
            text-decoration: none;
            background-color: #ff4d4d;
            color: #fff;
            font-size: 20px;
            font-weight: bold;
            border-radius: 5px;
            width: 40px;
            height: 40px;
            text-align: center;
            line-height: 40px;
            position: absolute;
            margin-top: -50px;
            right: 580px;
        }
        .close-btn:hover {
            background-color: #ff1a1a;
        }
        .strength-bar {
    width: 0;
    height: 5px;
    margin-top: 5px;
}

    </style>
</head>
<body>
    <div class="container">
        <h2>Add Staff</h2>
        <a href="view_admin.php" class="close-btn" aria-label="Close">&times;</a>
        <form action="add_staff.php" method="POST" id="addStaffForm">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="adminId" class="form-label">Admin ID</label>
                    <input type="text" class="form-control" id="adminId" name="adminId" required >
                    <div id="check_id" class="text-danger">Admin ID is required</div>
                </div>
                <div class="col-md-6">
                    <label for="fullName" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="fullName" name="fullName" required >
                    <div id="check_full" class="text-danger">Full Name must be at least 5 characters</div>
                </div>
            </div>
            <div class="row mb-3">
            <div class="col-md-6 position-relative">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required oninput="checkPassword()">
                <span id="passwordEye" class="eye-icon fas fa-eye" onclick="togglePasswordVisibility('password')"></span>
                <div id="passwordStrength" class="strength-bar"></div> <!-- Password Strength Indicator -->
                
            </div>
            <div class="col-md-6 position-relative">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required oninput="checkConfirmPassword()">
                <span id="confirmPasswordEye" class="eye-icon fas fa-eye" onclick="togglePasswordVisibility('confirmPassword')"></span>
                <div id="confirmPasswordStrength" class="strength-bar"></div> <!-- Confirm Password Strength Indicator -->
            </div>
        </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div id="check_e" class="text-danger">Enter a valid email address</div>
            </div>
            <div class="mb-3">
                <label for="contactNumber" class="form-label">Contact Number</label>
                <input type="text" class="form-control" id="contactNumber" name="contactNumber" required>
                <div id="check_num" class="text-danger">Enter a valid contact number</div>
            </div>
            <input type="submit" class="btn btn-primary" name="addstaff" value="Add Staff">
        </form>
    </div>
    <script>
        function togglePasswordVisibility(inputId) {
            const inputField = document.getElementById(inputId);
            inputField.type = inputField.type === 'password' ? 'text' : 'password';
        }

        document.getElementById('addStaffForm').addEventListener('submit', function (event) {
    const password = document.getElementById('password');
    const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    if (!passwordRegex.test(password.value)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Password',
            text: 'Password must include 1 uppercase letter, 1 number, 1 special character, and be 8 characters long.',
            confirmButtonText: 'OK'
        });
        event.preventDefault();
    }
});


function checkPassword() {
    const password = document.getElementById('password').value;
    const strengthBar = document.getElementById('passwordStrength');
    const passwordError = document.getElementById('passwordError');
    const regexUppercase = /[A-Z]/;
    const regexLowercase = /[a-z]/;
    const regexNumber = /[0-9]/;
    const regexSpecial = /[!@#$%^&*(),.?":{}|<>]/;
    const minLength = 8;

    let strength = 0;
    if (password.length >= minLength) strength++;
    if (regexUppercase.test(password)) strength++;
    if (regexLowercase.test(password)) strength++;
    if (regexNumber.test(password)) strength++;
    if (regexSpecial.test(password)) strength++;

    // Display Password Strength
    if (strength === 0) {
        strengthBar.style.width = '0%';
        strengthBar.style.backgroundColor = 'red';
    } else if (strength === 1) {
        strengthBar.style.width = '25%';
        strengthBar.style.backgroundColor = 'orange';
    } else if (strength === 2) {
        strengthBar.style.width = '50%';
        strengthBar.style.backgroundColor = 'yellow';
    } else if (strength === 3) {
        strengthBar.style.width = '75%';
        strengthBar.style.backgroundColor = 'lightgreen';
    } else if (strength === 4) {
        strengthBar.style.width = '100%';
        strengthBar.style.backgroundColor = 'green';
    }

    // Show or hide the error message based on password validity
    if (strength < 4) {
        passwordError.style.display = 'block';
    } else {
        passwordError.style.display = 'none';
    }
}

function checkConfirmPassword() {
    const confirmPassword = document.getElementById('confirmPassword').value;
    const password = document.getElementById('password').value;
    const confirmPasswordError = document.getElementById('check_confirm_pass');
    const confirmPasswordStrengthBar = document.getElementById('confirmPasswordStrength');
    
    let strength = 0;
    const regexUppercase = /[A-Z]/;
    const regexLowercase = /[a-z]/;
    const regexNumber = /[0-9]/;
    const regexSpecial = /[!@#$%^&*(),.?":{}|<>]/;
    const minLength = 8;

    if (confirmPassword.length >= minLength) strength++;
    if (regexUppercase.test(confirmPassword)) strength++;
    if (regexLowercase.test(confirmPassword)) strength++;
    if (regexNumber.test(confirmPassword)) strength++;
    if (regexSpecial.test(confirmPassword)) strength++;

    // Display Confirm Password Strength
    if (strength === 0) {
        confirmPasswordStrengthBar.style.width = '0%';
        confirmPasswordStrengthBar.style.backgroundColor = 'red';
    } else if (strength === 1) {
        confirmPasswordStrengthBar.style.width = '25%';
        confirmPasswordStrengthBar.style.backgroundColor = 'orange';
    } else if (strength === 2) {
        confirmPasswordStrengthBar.style.width = '50%';
        confirmPasswordStrengthBar.style.backgroundColor = 'yellow';
    } else if (strength === 3) {
        confirmPasswordStrengthBar.style.width = '75%';
        confirmPasswordStrengthBar.style.backgroundColor = 'lightgreen';
    } else if (strength === 4) {
        confirmPasswordStrengthBar.style.width = '100%';
        confirmPasswordStrengthBar.style.backgroundColor = 'green';
    }

    // Show or hide the error message if passwords don't match
    if (confirmPassword !== password) {
        confirmPasswordError.style.display = 'block';
    } else {
        confirmPasswordError.style.display = 'none';
    }
}


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
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Admin ID Taken',
                text: 'The Admin ID is already taken. Please choose another Admin ID.',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'add_staff.php';
            });
        </script>";
    } else {
        // Check if email already exists
        $verify_email_query = mysqli_query($connect, "SELECT * FROM admin WHERE admin_email='$email'");
        if (mysqli_num_rows($verify_email_query) > 0) {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Email Already Exists',
                    text: 'The email has already been used. Please choose another email.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'add_staff.php';
                });
            </script>";
        } else {
            // Check if contact number already exists
            $verify_contact_query = mysqli_query($connect, "SELECT * FROM admin WHERE admin_contact_number='$contact'");
            if (mysqli_num_rows($verify_contact_query) > 0) {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Contact Number In Use',
                        text: 'The telephone number is already in use. Please choose another number.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'add_staff.php';
                    });
                </script>";
            } else if ($password != $confirmPassword) {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Password Mismatch',
                        text: 'The password and confirm password must match.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'add_staff.php';
                    });
                </script>";
            } else {
                // Insert data into the database with admin_status set to 0
                $insert_query = mysqli_query($connect, "INSERT INTO admin (admin_id, admin_name, admin_contact_number, admin_password, admin_email, admin_joined_date, admin_status) 
                VALUES ('$id', '$name', '$contact', '$password', '$email', '$currentDateTime', 0)");
                
                if ($insert_query) {
                    // Update admin_status to 1
                    $update_status_query = mysqli_query($connect, "UPDATE admin SET admin_status = 1 WHERE admin_id = '$id'");

                    if ($update_status_query) {
                        echo "<script>
                            Swal.fire({
                                icon: 'success',
                                title: 'Registration Successful',
                                text: 'The staff member has been successfully added and activated.',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = 'view_admin.php';
                            });
                        </script>";
                    } else {
                        echo "<script>
                            Swal.fire({
                                icon: 'error',
                                title: 'Activation Failed',
                                text: 'The staff member was added, but activation failed.',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = 'add_staff.php';
                            });
                        </script>";
                    }
                } else {
                    echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Registration Failed',
                            text: 'An error occurred. Please try again.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = 'add_staff.php';
                        });
                    </script>";
                }
            }
        }
    }
}
?>

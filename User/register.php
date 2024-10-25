
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration </title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        #registrationForm {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333333;
        }

        .field {
            margin-bottom: 15px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"],
        input[type="date"],
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .error {
            color: red;
            font-size: 0.9em;
            display: block;
        }

        .gender-container {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        .gender-label {
            margin-right: 10px;
        }

        .eye-icon {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 35%;
            user-select: none;
        }
    </style>
</head>
<body>
    <form id="registrationForm" method="POST" action="register.php">
        <h2>Register Account</h2>
        <p style="text-align: center; margin-top: 20px;">
        Already have an account? <a href="login.php" style="color: #28a745; text-decoration: none;">Log in</a>
        </p>

        
        <div class="field">
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" required oninput="checkEmail()">
            <span id="emailError" class="error">Please enter a valid email (must include '@' AND '.')</span>
        </div>

        <div class="field">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required oninput="checkName()">
            <span id="nameError" class="error">Name must be at least 6 characters long.</span>
        </div>

        <div class="field">
            <label for="contact">Contact Number:</label>
            <input type="text" id="contact" name="contact" required oninput="checkContact()">
            <span id="contactError" class="error">Format must be xxx-xxxxxxx.</span>
        </div>

        <div class="field">
            <label>Gender:</label>
            <div class="gender-container">
                <span class="gender-label">Female</span>
                <input type="radio" name="gender" value="female" id="genderFemale" required onchange="hideGenderError()">
                <span class="gender-label">Male</span>
                <input type="radio" name="gender" value="male" id="genderMale" required onchange="hideGenderError()">
            </div>
            <span id="genderError" class="error">Please select your gender.</span>
        </div>

        <div class="field">
    <label for="dob">Date of Birth:</label>
    <input type="date" id="dob" name="dob" required oninput="checkDob()">
    <span id="dobError" class="error">Please enter a valid date of birth.</span>
    <span id="dobFutureError" class="error">Date of birth cannot be in the future.</span>
    </div>

        <div class="field">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required oninput="checkPassword()">
            <span id="passwordToggle" class="eye-icon" onclick="togglePassword('password', this)">👁️</span>
            <span id="passwordError" class="error">Password must include 1 uppercase letter, 1 number, 1 special character, and be 15 characters long.</span>
        </div>

        <div class="field">
            <label for="confirmPassword">Confirm Password:</label>
            <input type="password" id="confirmPassword" name="confirmPassword" required oninput="checkConfirmPassword()">
            <span id="confirmPasswordToggle" class="eye-icon" onclick="togglePassword('confirmPassword', this)">👁️</span>
            <span id="confirmPasswordError" class="error">Passwords do not match.</span>
        </div>

        <p>
            <input type="submit" name="signupbtn" value="Sign Up">
        </p>
    </form>

   
    <script>
        function checkEmail() {
            const email = document.getElementById("email").value;
            const emailError = document.getElementById("emailError");
            const validEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            emailError.style.display = validEmail.test(email) ? "none" : "block";
        }

        function checkName() {
            const name = document.getElementById("name").value;
            const nameError = document.getElementById("nameError");
            nameError.style.display = name.length >= 6 ? "none" : "block";
        }

        function checkContact() {
            const contact = document.getElementById("contact").value;
            const contactError = document.getElementById("contactError");
            const validContact = /^\d{3}-\d{7}$/;

            contactError.style.display = validContact.test(contact) ? "none" : "block";
        }

        function hideGenderError() {
            const genderError = document.getElementById("genderError");
            genderError.style.display = "none";
        }

        function checkDob() {
            const dob = document.getElementById("dob").value;
            const dobError = document.getElementById("dobError");
            dobError.style.display = dob ? "none" : "block";
        }

        function checkPassword() {
            const password = document.getElementById("password").value;
            const passwordError = document.getElementById("passwordError");
            const validPassword = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{15,}$/;

            passwordError.style.display = validPassword.test(password) ? "none" : "block";
        }

        function checkConfirmPassword() {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirmPassword").value;
            const confirmPasswordError = document.getElementById("confirmPasswordError");

            confirmPasswordError.style.display = password === confirmPassword ? "none" : "block";
        }

        function togglePassword(inputId, toggleIcon) {
            const passwordInput = document.getElementById(inputId);
            const inputType = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', inputType);
        }




        function setMaxDate() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById("dob").setAttribute('max', today);
    }

    // Call setMaxDate when the page loads
    window.onload = setMaxDate;

    function checkDob() {
        const dobInput = document.getElementById("dob");
        const dobError = document.getElementById("dobError");
        const dobFutureError = document.getElementById("dobFutureError");

        const selectedDate = new Date(dobInput.value);
        const currentDate = new Date();

        // Reset error messages
        dobError.style.display = "none";
        dobFutureError.style.display = "none";

        if (!dobInput.value) {
            dobError.style.display = "block";
            return;
        }

        // Check if selected date is in the future
        if (selectedDate > currentDate) {
            dobFutureError.style.display = "block";
        }
    }
    </script>
</body>
</html>

<?php
include 'dataconnection.php'; // Your database connection file

if (isset($_POST["signupbtn"])) 
{
    // Retrieve form data
    $email = $_POST["email"]; 
    $name = $_POST["name"]; 
    $contact = $_POST["contact"];
    $gender = $_POST["gender"];  
    $dob = $_POST["dob"]; 
    $password = $_POST["password"]; 
    $confirmPassword = $_POST["confirmPassword"];

    $now = new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur'));
    $currentDateTime = $now->format('Y-m-d H:i:s');

    // Check if email already exists
    $verify_query = mysqli_query($connect, "SELECT * FROM user WHERE user_email='$email'");
    if (mysqli_num_rows($verify_query) > 0) {
        echo "<script>alert('The email has already been used. Please choose another email.');window.location.href='register.php';</script>";
    } 
    else if ($password != $confirmPassword) {
        echo "<script>alert('The password and confirm password must match.');window.location.href='register.php';</script>";
    } 
    else {
        // Insert data into the database
        $insert_query = mysqli_query($connect, "INSERT INTO user (user_email, user_name, user_contact_number, user_gender, user_date_of_birth, user_password, user_join_time) 
        VALUES ('$email', '$name', '$contact', '$gender', '$dob', '$password', '$currentDateTime')");
        
        if ($insert_query) {
            echo "<script>alert('Registration successful.');window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Registration failed. Please try again.');window.location.href='register.php';</script>";
        }
    }
}
?>

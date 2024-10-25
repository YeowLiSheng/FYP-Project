
<?php
include 'dataconnection.php'; // Make sure this file contains your database connection

if (isset($_POST["agree"])) {
    $a = $_POST["email"]; // Email
    $b = $_POST["Name"]; // Full name
    $c = $_POST["telephone"]; // Telephone number
    $f = $_POST["password"]; // Password
    $d = $_POST["cpassword"]; // Confirm password

    // Get the current date and time
    $now = new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur'));
    $currentDateTime = $now->format('Y-m-d H:i:s');

    // Check if email already exists
    $verify_query = mysqli_query($connect, "SELECT * FROM user WHERE user_email='$a'");
    if (mysqli_num_rows($verify_query) > 0) {
        echo "<script>alert('The email has already been used. Please choose another email.');window.location.href='user_register.php';</script>";
    } 
    else if ($f != $d) {
        echo "<script>alert('Passwords do not match. Please re-enter your password.');window.location.href='user_register.php';</script>";
    } 
    else {
        // Hash the password
        $hashed_password = password_hash($f, PASSWORD_DEFAULT);

        // Insert user information into the database
        $insert_query = "INSERT INTO user(user_email, user_name, user_contact_number, user_password, user_join_time) VALUES('$a', '$b', '$c', '$hashed_password', '$currentDateTime')";
        if (mysqli_query($connect, $insert_query)) {
            $ID = mysqli_insert_id($connect);
            echo "<script>alert('Register Successful'); window.location.href='user_login.php';</script>";
        } 
        else {
            echo "Error: " . mysqli_error($connect); // Error checking
        }
    }
}

// Close the database connection
mysqli_close($connect);
?>

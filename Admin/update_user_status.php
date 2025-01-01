<?php
// Include your database connection file
include 'dataconnection.php';

// Check if the form is submitted
if (isset($_POST['toggle_status'])) {
    $user_id = $_POST['user_id'];

    // Fetch the current status of the user
    $result = mysqli_query($connect, "SELECT user_status FROM user WHERE user_id = '$user_id'");
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $current_status = $row['user_status'];

        // Toggle the status
        $new_status = ($current_status == 1) ? 0 : 1;

        // Update the status in the database
        $update = mysqli_query($connect, "UPDATE user SET user_status = '$new_status' WHERE user_id = '$user_id'");
        if ($update) {
            header("Location: view_customer.php"); 
            exit();
        } else {
            echo "Error updating user status: " . mysqli_error($connect);
        }
    } else {
        echo "User not found.";
    }
} else {
    echo "Invalid request.";
}
?>

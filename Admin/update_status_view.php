<?php
include 'dataconnection.php';

// Check if email is provided
if (isset($_GET['email'])) {
    $email = $connect->real_escape_string($_GET['email']);
    
    // Update the status in the database
    $updateQuery = "UPDATE contact_us SET status='1' WHERE user_email='$email'";
    if ($connect->query($updateQuery) === TRUE) {
        // Redirect to email client (mailto:)
        header("Location: mailto:$email");
        exit();
    } else {
        echo "Error updating status: " . $connect->error;
    }
} else {
    echo "Email not provided.";
}

// Close the connection
$connect->close();
?>

<?php
// delete_staff.php

// Connect to the database
require_once 'dataconnection.php'; // Ensure you have a database connection file

// Start the session and get the current logged-in admin's ID
session_start();
$admin_id = $_SESSION['admin_id']; // Assuming the admin ID is stored in the session

// Deletion logic in delete_staff.php
if (isset($_GET['staff_id'])) {
    $staff_id = $_GET['staff_id'];

    // Prevent superadmin from deleting themselves
    if ($admin_id === 'superadmin' && $staff_id === '15') {
        // Inform the user and redirect if superadmin tries to delete themselves
        echo "<script>alert('Superadmin cannot delete yourself.');window.location.href='view_admin.php';</script>";
        exit(); // Stop further script execution
    }

    // Make sure to use prepared statements to prevent SQL injection
    $stmt = $connect->prepare("DELETE FROM admin WHERE staff_id = ?");
    $stmt->bind_param("i", $staff_id); // "i" for integer (staff_id)
    $stmt->execute();

    // Check if deletion was successful
    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Staff deleted successfully.');window.location.href='view_admin.php';</script>";
    } else {
        echo "<script>alert('Failed to delete staff or staff not found.');window.location.href='view_admin.php';</script>";
    }

    $stmt->close();
}

$connect->close();
?>

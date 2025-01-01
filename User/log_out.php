<?php
session_start(); // Start the session
include("dataconnection.php");
// session_unset(); // This line is optional; it clears all session variables
session_destroy(); // Destroy the session
echo "<!DOCTYPE html>
<html>
<head>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
</head>
<body>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Successful Log Out',
            text: 'You have been logged out successfully.',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'dashboard.php';
        });
    </script>
</body>
</html>";
?>

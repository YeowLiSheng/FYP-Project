<?php
session_start(); // Start the session
include("dataconnection.php");

session_destroy();

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
            window.location.href = 'admin_login.php';
        });
    </script>
</body>
</html>";
?>

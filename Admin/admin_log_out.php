<?php
session_start(); // Start the session
include("dataconnection.php");

session_destroy(); // Destroy the session

// Clear any existing session cookies
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Force cache control headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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

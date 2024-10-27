<?php
session_start(); // Start the session
include("dataconnection.php");
// session_unset(); // This line is optional; it clears all session variables
session_destroy(); // Destroy the session
echo "<script type='text/javascript'>
        alert('Successful Log Out');
        window.location.href='homepage.html';
      </script>";
?>

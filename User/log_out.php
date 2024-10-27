<?php
include("dataconnection.php");

//session_unset(); remove the data of all session variables

session_destroy();



echo "<script type='text/javascript'>
                alert('Successful Log Out');
                window.location.href='homepage.html';
              </script>";
?>
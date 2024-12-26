<?php
session_start();

if (isset($_POST['currency'])) {
    $currency = $_POST['currency'];
    $_SESSION['currency'] = $currency; 
    header("Location: " . $_SERVER['HTTP_REFERER']); 
    exit();
}
?>
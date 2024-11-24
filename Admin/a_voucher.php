<?php
include 'dataconnection.php';
session_start();
if(isset($_POST["voucher"]))
{
    $vc = $_POST["voucher_code"];
    $r = $_POST["rate"];
    $rate = $r / 100;
    $st = "1";
    $ul = $_POST["usage_limit"];
    $ma = $_POST["minimum_amount"];
    $d = $_POST["voucher_des"];
    $pic = $_POST["voucher_pic"];

    $insert_v = mysqli_query($connect, "INSERT voucher(voucher_code, discount_rate, voucher_status, usage_limit, minimum_amount, voucher_des, voucher_pic)VALUES('$vc','$r','$st','$ul','$ma','$d','$pic')");
    if($insert_v)
    {
        $_SESSION['title'] = "New Voucher";
        $_SESSION['text'] = "Generated successfully";
        $_SESSION['icon'] = "success";
    }
    else
    {
        $_SESSION['title'] = "Error";
        $_SESSION['text'] = "Error";
        $_SESSION['icon'] = "error";
    }
    header("location:admin_voucher.php");
}
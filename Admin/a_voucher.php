<?php
include 'dataconnection.php';
session_start();
if(isset($_POST["voucher"]))
{
    $vc = $_POST["voucher_code"];
    $r = $_POST["discount_rate"];
    $rate = $r / 100;
    $st = "Active";
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

// Edit Voucher Function
if (isset($_POST["update_voucher"])) {
    $vc = $_POST["voucher_code"];
    $r = $_POST["discount_rate"];
    $ul = $_POST["usage_limit"];
    $ma = $_POST["minimum_amount"];
    $d = $_POST["voucher_des"];
    $pic = $_FILES["voucher_pic"]["name"];

    // Handle picture upload if provided
    if ($pic) {
        $pic_path = "../User/images/" . basename($pic);
        move_uploaded_file($_FILES["voucher_pic"]["tmp_name"], $pic_path);
        $update_query = "UPDATE voucher SET discount_rate = '$r', usage_limit = '$ul', minimum_amount = '$ma', voucher_des = '$d', voucher_pic = '$pic' WHERE voucher_code = '$vc'";
    } else {
        $update_query = "UPDATE voucher SET discount_rate = '$r', usage_limit = '$ul', minimum_amount = '$ma', voucher_des = '$d' WHERE voucher_code = '$vc'";
    }

    $update_result = mysqli_query($connect, $update_query);

    if ($update_result) {
        $_SESSION['title'] = "Voucher Updated";
        $_SESSION['text'] = "Voucher details updated successfully.";
        $_SESSION['icon'] = "success";
    } else {
        $_SESSION['title'] = "Error";
        $_SESSION['text'] = "Failed to update the voucher.";
        $_SESSION['icon'] = "error";
    }
    header("location:admin_voucher.php");
}

// Deactivate Voucher Function
if (isset($_POST["deactivate_voucher"])) {
    $voucher_code = $_POST["deactivate_voucher"];
    $deactivate_query = "UPDATE voucher SET voucher_status = 'Inactive' WHERE voucher_code = '$voucher_code'";
    $deactivate_result = mysqli_query($connect, $deactivate_query);

    if ($deactivate_result) {
        $_SESSION['title'] = "Voucher Deactivated";
        $_SESSION['text'] = "Voucher has been successfully deactivated.";
        $_SESSION['icon'] = "success";
    } else {
        $_SESSION['title'] = "Error";
        $_SESSION['text'] = "Failed to deactivate the voucher.";
        $_SESSION['icon'] = "error";
    }
    header("location:admin_voucher.php");
}

// Activate Voucher Function
if (isset($_POST["activate_voucher"])) {
    $voucher_code = $_POST["activate_voucher"];
    $activate_query = "UPDATE voucher SET voucher_status = 'Active' WHERE voucher_code = '$voucher_code'";
    $activate_result = mysqli_query($connect, $activate_query);

    if ($activate_result) {
        $_SESSION['title'] = "Voucher Activated";
        $_SESSION['text'] = "Voucher has been successfully activated.";
        $_SESSION['icon'] = "success";
    } else {
        $_SESSION['title'] = "Error";
        $_SESSION['text'] = "Failed to activate the voucher.";
        $_SESSION['icon'] = "error";
    }
    header("location:admin_voucher.php");
}
?>
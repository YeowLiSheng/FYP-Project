<?php
include 'dataconnection.php';
session_start();

//add product
if (isset($_POST["save_product"])) {
    $pd = $_POST["product_name"];
    $b = $_POST["brand"];
    $type = $_POST["radio"];
    $c = $_POST["cate"];
    $d = $_POST["desc"];
    $img = $_POST["img"];
    $quick_view1 = $_POST["quick_view1"];
    $quick_view2 = $_POST["quick_view2"];
    $quick_view3 = $_POST["quick_view3"];
    $price = $_POST["price"];
    $qty = $_POST["qty"];
    $color1 = $_POST["color1"];
    $color2 = $_POST["color2"];
    $size1 = $_POST["size1"];
    $size2 = $_POST["size2"];
    $tags = $_POST["tags"];

    $status = "1";
    $insert = "INSERT INTO product (category_id, product_status, product_name, product_des, product_image, Quick_View1, Quick_View2, Quick_View3, product_price, product_stock, color1, color2, size1, size2, tags, product_type) 
                VALUES ('$c', '$status', '$pd', '$d', '$img', '$quick_view1', '$quick_view2', '$quick_view3', '$price', '$qty', '$color1', '$color2', '$size1', '$size2', '$tags', '$type')";
    $run = mysqli_query($connect, $insert);

    if ($run) {
        $_SESSION['img'] = "$img";
        $_SESSION['title'] = "$pd";
        $_SESSION['text'] = "successfully added!";
        $_SESSION['icon'] = "success";
        header("location:admin_product.php");
    } else {
        $_SESSION['img'] = "";
        $_SESSION['title'] = "Error";
        $_SESSION['text'] = "Failed to add product!";
        $_SESSION['icon'] = "error";
        header("location:admin_product.php");
    }
}

//delete product
if (isset($_GET["product_id"])) {
    $p = $_GET["product_id"];

    $query = mysqli_query($connect, "SELECT * FROM product WHERE product_id = '$p'");
    $row = mysqli_fetch_assoc($query);
    $pn = $row['product_name'];
    $check = $row['product_status'];

    if ($check == 1) {
        $st = "UPDATE product SET product_status = 2 WHERE product_id='$p'";
        $text = "is now unavailable";
    } else if ($check == 2) {
        $st = "UPDATE product SET product_status = 1 WHERE product_id='$p'";
        $text = "is now available";
    }

    $query = mysqli_query($connect, $st);

    if ($query) {
        $_SESSION['title'] = "$pn";
        $_SESSION['text'] = "$text";
        $_SESSION['icon'] = "success";
        header("location:admin_product.php");
    } else {
        $_SESSION['title'] = "$pn";
        $_SESSION['text'] = "Failed to update status";
        $_SESSION['icon'] = "error";
        header("location:admin_product.php");
    }
}


//edit product
if (isset($_POST["edit_product"])) {
    $id = $_POST["product_id"];
    $pd = $_POST["product_name"];
    $b = $_POST["brand"];
    $type = $_POST["edit-radio"];
    $c = $_POST["cate"];
    $d = $_POST["desc"];
    $img = empty($_POST["img"]) ? $_POST["old-img"] : $_POST["img"];
    $quick_view1 = $_POST["quick_view1"];
    $quick_view2 = $_POST["quick_view2"];
    $quick_view3 = $_POST["quick_view3"];
    $price = $_POST["price"];
    $qty = $_POST["qty"];
    $color1 = $_POST["color1"];
    $color2 = $_POST["color2"];
    $size1 = $_POST["size1"];
    $size2 = $_POST["size2"];
    $tags = $_POST["tags"];

    $update = "UPDATE product SET 
                category_id='$c',
                product_name='$pd',
                product_des='$d',
                product_image='$img',
                Quick_View1='$quick_view1',
                Quick_View2='$quick_view2',
                Quick_View3='$quick_view3',
                product_price='$price',
                product_stock='$qty',
                color1='$color1',
                color2='$color2',
                size1='$size1',
                size2='$size2',
                tags='$tags',
                product_type='$type' 
                WHERE product_id = '$id'";

    $update_run = mysqli_query($connect, $update);

    if ($update_run) {
        $_SESSION['title'] = "Congrats!";
        $_SESSION['text'] = "Successfully edited.";
        $_SESSION['icon'] = "success";
        header("location:admin_product.php");
    } else {
        $_SESSION['title'] = ":(";
        $_SESSION['text'] = "Failed to edit!";
        $_SESSION['icon'] = "error";
        header("location:admin_product.php");
    }
}

?>
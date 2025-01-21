<?php
include 'dataconnection.php';
session_start();

//add product
if (isset($_POST["save_product"])) {
    $pd = $_POST["product_name"];
    $c = $_POST["cate"];
    $d = mysqli_real_escape_string($connect, $_POST["desc"]);
    $img = $_POST["img"];
    $quick_view1 = $_POST["quick_view1"];
    $quick_view2 = $_POST["quick_view2"];
    $quick_view3 = $_POST["quick_view3"];
    $price = $_POST["price"];
    $qty = $_POST["qty"];
    $color1 = $_POST["color1"];
    $size1 = $_POST["size1"];
    $tags = $_POST["tags"];

    $status = "1";
    $insert_product = "INSERT INTO promotion_product (category_id, promotion_status, promotion_name, promotion_des, promotion_image, promotion_price, tags) 
                VALUES ('$c', '$status', '$pd', '$d', '$img', '$price', '$tags')";
    $run_product = mysqli_query($connect, $insert_product);

    if ($run_product) {

        $promotion_id = mysqli_insert_id($connect);

        $insert_variant = "INSERT INTO product_variant (promotion_id, color, size, stock, Quick_View1, Quick_View2, Quick_View3) 
                           VALUES ('$promotion_id', '$color1', '$size1', '$qty', '$quick_view1', '$quick_view2', '$quick_view3')";
        $run_variant = mysqli_query($connect, $insert_variant);

        if (!empty($_POST["color2"])) {
            $color2 = $_POST["color2"];
            $stock2 = $_POST["stock2"];
            $quick_view4 = $_POST["quick_view4"];
            $quick_view5 = $_POST["quick_view5"];
            $quick_view6 = $_POST["quick_view6"];

            $insert_variant2 = "INSERT INTO product_variant (promotion_id, color, size, stock, Quick_View1, Quick_View2, Quick_View3) 
                                VALUES ('$promotion_id', '$color2', '$size1', '$stock2', '$quick_view4', '$quick_view5', '$quick_view6')";
            $run_variant2 = mysqli_query($connect, $insert_variant2);
        }

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
        header("location:admin_promotion.php");
    }
}

//delete product
if (isset($_GET["promotion_id"])) {
    $p = $_GET["promotion_id"];

    $query = mysqli_query($connect, "SELECT * FROM promotion_product WHERE promotion_id = '$p'");
    $row = mysqli_fetch_assoc($query);
    $pn = $row['promotion_name'];
    $check = $row['promotion_status'];

    if ($check == 1) {
        $st = "UPDATE promotion_product SET promotion_status = 2 WHERE promotion_id='$p'";
        $text = "is now unavailable";
    } else if ($check == 2) {
        $st = "UPDATE promotion_product SET promotion_status = 1 WHERE promotion_id='$p'";
        $text = "is now available";
    }

    $query = mysqli_query($connect, $st);

    if ($query) {
        $_SESSION['title'] = "$pn";
        $_SESSION['text'] = "$text";
        $_SESSION['icon'] = "success";
        header("location:admin_promotion.php");
    } else {
        $_SESSION['title'] = "$pn";
        $_SESSION['text'] = "Failed to update status";
        $_SESSION['icon'] = "error";
        header("location:admin_promotion.php");
    }
}


//edit product
if (isset($_POST["edit_variant"])) {
    $id = $_POST["promotion_id"];
    $variant_id = $_POST["variant_id"];
    $pd = $_POST["product_name"];
    $c = $_POST["cate"];
    $d = $_POST["desc"];
    $price = $_POST["price"];
    $qty = $_POST["qty"];
    $color1 = $_POST["color1"];
    $size1 = $_POST["size1"];
    $tags = $_POST["tags"];

    // Handle product image
    if (!empty($_FILES["img"]["name"])) {
        $img = $_FILES["img"]["name"];
        $img_tmp = $_FILES["img"]["tmp_name"];
        move_uploaded_file($img_tmp, "../User/images/" . $img); // Adjust the "uploads/" path as per your project
    } else {
        $img = $_POST["old-img"];
    }

    // Handle quick view images
    $quick_view1 = !empty($_FILES["quick_view1"]["name"]) ? $_FILES["quick_view1"]["name"] : $_POST["old-quick_view1"];
    if (!empty($_FILES["quick_view1"]["tmp_name"])) {
        move_uploaded_file($_FILES["quick_view1"]["tmp_name"], "../User/images/" . $quick_view1);
    }

    $quick_view2 = !empty($_FILES["quick_view2"]["name"]) ? $_FILES["quick_view2"]["name"] : $_POST["old-quick_view2"];
    if (!empty($_FILES["quick_view2"]["tmp_name"])) {
        move_uploaded_file($_FILES["quick_view2"]["tmp_name"], "../User/images/" . $quick_view2);
    }

    $quick_view3 = !empty($_FILES["quick_view3"]["name"]) ? $_FILES["quick_view3"]["name"] : $_POST["old-quick_view3"];
    if (!empty($_FILES["quick_view3"]["tmp_name"])) {
        move_uploaded_file($_FILES["quick_view3"]["tmp_name"], "../User/images/" . $quick_view3);
    }

    // Update the product table
    $update = "UPDATE promotion_product SET 
                category_id='$c',
                promotion_name='$pd',
                promotion_des='$d',
                promotion_image='$img',
                promotion_price='$price',
                tags='$tags'
                WHERE promotion_id = '$id'";

    $update_run = mysqli_query($connect, $update);

    if ($update_run) {
        // Update the product_variant table
        $update_variant = "UPDATE product_variant SET 
                           color='$color1',
                           size='$size1',
                           stock='$qty',
                           Quick_View1='$quick_view1',
                           Quick_View2='$quick_view2',
                           Quick_View3='$quick_view3'
                           WHERE variant_id='$variant_id'";

        $update_variant_run = mysqli_query($connect, $update_variant);

        $_SESSION['title'] = "Congrats!";
        $_SESSION['text'] = "Successfully edited.";
        $_SESSION['icon'] = "success";
        header("location:admin_promotion.php");
    } else {
        $_SESSION['title'] = ":(";
        $_SESSION['text'] = "Failed to edit!";
        $_SESSION['icon'] = "error";
        header("location:admin_promotion.php");
    }
}


?>
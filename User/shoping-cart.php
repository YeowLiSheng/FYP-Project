<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

// Retrieve the user information
$user_id = $_SESSION['id'];
$result = mysqli_query($conn, "SELECT * FROM user WHERE user_id ='$user_id'");

// Check if the query was successful and fetch user data
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
} else {
    echo "User not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    $new_product_added = false;
    foreach ($_POST['product_qty'] as $product_id => $qty) {
        $qty = intval($qty);
        if ($qty > 0) {
            // Calculate individual total price
            $product_price_query = "SELECT product_price FROM product WHERE product_id = $product_id";
            $product_price_result = $conn->query($product_price_query);
            $product_price = $product_price_result->fetch_assoc()['product_price'];
            $total_price = $qty * $product_price;

            // Update or add the product to the cart
            $update_query = "
                UPDATE shopping_cart 
                SET qty = $qty, total_price = $total_price
                WHERE user_id = $user_id AND product_id = $product_id";
            $conn->query($update_query);

            if ($conn->affected_rows == 0) { // New product added
                $new_product_added = true;
                $conn->query("
                    INSERT INTO shopping_cart (user_id, product_id, qty, total_price) 
                    VALUES ($user_id, $product_id, $qty, $total_price)");
            }
        } else {
            // Remove product if qty is zero
            $delete_query = "DELETE FROM shopping_cart WHERE user_id = $user_id AND product_id = $product_id";
            $conn->query($delete_query);
        }
    }

    // Reapply voucher if a new product was added and voucher was already applied
    if ($new_product_added && isset($_SESSION['voucher_applied']) && $_SESSION['voucher_applied'] === true) {
        reapplyVoucher($conn, $user_id, $final_total_price);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


function reapplyVoucher($conn, $user_id, &$final_total_price) {
    // Check if the voucher is already applied in the session
    if (isset($_SESSION['voucher_applied']) && $_SESSION['voucher_applied'] === true) {
        // Skip reapplication if voucher already applied
        $final_total_price = $_SESSION['final_total_price'];
        return;
    }

    // Fetch voucher information if any was previously applied
    $voucher_usage_query = "
        SELECT v.discount_rate, v.minimum_amount, v.voucher_id 
        FROM voucher_usage vu
        JOIN voucher v ON vu.voucher_id = v.voucher_id
        WHERE vu.user_id = $user_id AND vu.usage_num > 0";
    $voucher_usage_result = $conn->query($voucher_usage_query);

    if ($voucher_usage_result && $voucher = $voucher_usage_result->fetch_assoc()) {
        $discount_rate = $voucher['discount_rate'];
        $minimum_amount = $voucher['minimum_amount'];

        // Recalculate the total price
        $recalc_query = "
            SELECT SUM(sc.qty * p.product_price) AS total_price 
            FROM shopping_cart sc 
            JOIN product p ON sc.product_id = p.product_id 
            WHERE sc.user_id = $user_id";
        $recalc_result = $conn->query($recalc_query);
        $recalc_row = $recalc_result->fetch_assoc();
        $total_price = $recalc_row['total_price'];

        // Apply the voucher if conditions meet
        if ($total_price >= $minimum_amount) {
            $discount_amount = $total_price * ($discount_rate / 100);
            $final_total_price = $total_price - $discount_amount;

            // Persist the final total price and set session variable
            $_SESSION['voucher_applied'] = true;
            $_SESSION['final_total_price'] = $final_total_price;

            // Update shopping cart final price
            $update_final_total_query = "
                UPDATE shopping_cart 
                SET final_total_price = $final_total_price, voucher_applied = 1 
                WHERE user_id = $user_id";
            $conn->query($update_final_total_query);
        }
    }
}

// Function to store the final total price in the cart, regardless of voucher application
function storeFinalTotal($conn, $user_id, $final_total_price) {
    $update_final_total_query = "
        UPDATE shopping_cart 
        SET final_total_price = $final_total_price, voucher_applied = 0 
        WHERE user_id = $user_id";
    $conn->query($update_final_total_query);
}

// Initialize total_price before fetching cart items
$total_price = 0;

// Fetch and combine cart items for the logged-in user where the product_id is the same
$cart_items_query = "
    SELECT sc.product_id, p.product_name, p.product_image, p.product_price, 
           SUM(sc.qty) AS total_qty, 
           SUM(sc.qty * p.product_price) AS total_price, 
           MAX(sc.final_total_price) AS final_total_price, 
           MAX(sc.voucher_applied) AS voucher_applied
    FROM shopping_cart sc 
    JOIN product p ON sc.product_id = p.product_id 
    WHERE sc.user_id = $user_id 
    GROUP BY sc.product_id";
$cart_items_result = $conn->query($cart_items_query);

// Calculate total price and final total price
if ($cart_items_result && $cart_items_result->num_rows > 0) {
    while ($cart_item = $cart_items_result->fetch_assoc()) {
        $total_price += $cart_item['total_price'];
    }
}

// Default final total price without discount
$final_total_price = $total_price;

// Apply discount after verifying voucher code, if applicable
$discount_amount = 0; // Initialize discount amount
$error_message = ""; // Initialize error message

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_voucher']) && !empty($_POST['coupon'])) {
    $voucher_code = mysqli_real_escape_string($conn, $_POST['coupon']);
    $voucher_query = "
        SELECT discount_rate, voucher_status, minimum_amount, usage_limit, voucher_id 
        FROM voucher 
        WHERE voucher_code = '$voucher_code' AND voucher_status = 'active' 
        LIMIT 1";
    $voucher_result = $conn->query($voucher_query);

    if ($voucher_result && $voucher_result->num_rows > 0) {
        $voucher = $voucher_result->fetch_assoc();
        $discount_rate = $voucher['discount_rate'];
        $minimum_amount = $voucher['minimum_amount'];
        $voucher_id = $voucher['voucher_id'];
        $usage_limit = $voucher['usage_limit'];

        // Check the user's current usage of this voucher
        $usage_query = "
            SELECT usage_num 
            FROM voucher_usage 
            WHERE user_id = $user_id AND voucher_id = $voucher_id";
        $usage_result = $conn->query($usage_query);

        if ($usage_result && $usage_row = $usage_result->fetch_assoc()) {
            $current_usage = $usage_row['usage_num'];
        } else {
            $current_usage = 0; // No usage record found
        }

        // Check if usage limit is reached
        if ($current_usage < $usage_limit) {
            // Check if total price meets the minimum amount required
            if ($total_price >= $minimum_amount) {
                $discount_amount = $total_price * ($discount_rate / 100);
                $final_total_price = $total_price - $discount_amount;

                // Store the final total and voucher applied status in shopping_cart permanently
                $update_final_total_query = "
                    UPDATE shopping_cart 
                    SET final_total_price = $final_total_price, voucher_applied = 1 
                    WHERE user_id = $user_id";
                $conn->query($update_final_total_query);

                // Update or insert the voucher usage record
                if ($current_usage > 0) {
                    $conn->query("
                        UPDATE voucher_usage 
                        SET usage_num = usage_num + 1 
                        WHERE user_id = $user_id AND voucher_id = $voucher_id
                    ");
                } else {
                    $conn->query("
                        INSERT INTO voucher_usage (user_id, voucher_id, usage_num) 
                        VALUES ($user_id, $voucher_id, 1)
                    ");
                }
            } else {
                $error_message = "Your cart total must be at least $" . number_format($minimum_amount, 2) . " to use this voucher.";
            }
        } else {
            $error_message = "You have reached the usage limit for this voucher.";
        }
    } else {
        $error_message = "Invalid or inactive voucher code.";
    }
} else {
    // Store final total price in the database if no voucher is applied
    storeFinalTotal($conn, $user_id, $final_total_price);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Shoping Cart</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
<!--===============================================================================================-->	
	<link rel="icon" type="image/png" href="images/icons/favicon.png"/>
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/iconic/css/material-design-iconic-font.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/linearicons-v1.0.0/icon-font.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
<!--===============================================================================================-->	
	<link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/animsition/css/animsition.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/perfect-scrollbar/perfect-scrollbar.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" type="text/css" href="css/main.css">
<!--===============================================================================================-->
</head>
<body class="animsition">
	
	<!-- Header -->
	<header class="header-v4">
		<!-- Header desktop -->
		<div class="container-menu-desktop">
			<!-- Topbar -->
			<div class="top-bar">
				<div class="content-topbar flex-sb-m h-full container">
					<div class="left-top-bar">
						Free shipping for standard order over $100
					</div>

					<div class="right-top-bar flex-w h-full">
						<a href="#" class="flex-c-m trans-04 p-lr-25">
							Help & FAQs
						</a>

						<a href="#" class="flex-c-m trans-04 p-lr-25">
							My Account
						</a>

						<a href="#" class="flex-c-m trans-04 p-lr-25">
							EN
						</a>

						<a href="#" class="flex-c-m trans-04 p-lr-25">
							USD
						</a>
					</div>
				</div>
			</div>

			<div class="wrap-menu-desktop how-shadow1">
				<nav class="limiter-menu-desktop container">
					
					<!-- Logo desktop -->		
					<a href="#" class="logo">
						<img src="images/icons/logo-01.png" alt="IMG-LOGO">
					</a>

					<!-- Menu desktop -->
					<div class="menu-desktop">
						<ul class="main-menu">
							<li>
								<a href="index.html">Home</a>
								<ul class="sub-menu">
									<li><a href="index.html">Homepage 1</a></li>
									<li><a href="home-02.html">Homepage 2</a></li>
									<li><a href="home-03.html">Homepage 3</a></li>
								</ul>
							</li>

							<li>
								<a href="product.php">Shop</a>
							</li>

							<li class="label1" data-label1="hot">
								<a href="voucher_page.php">Voucher/a>
							</li>

							<li>
								<a href="blog.html">Blog</a>
							</li>

							<li>
								<a href="about.html">About</a>
							</li>

							<li>
								<a href="contact.html">Contact</a>
							</li>
						</ul>
					</div>	

					<!-- Icon header -->
					<div class="wrap-icon-header flex-w flex-r-m">
						<div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 js-show-modal-search">
							<i class="zmdi zmdi-search"></i>
						</div>

						<div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti js-show-cart" data-notify="2">
							<i class="zmdi zmdi-shopping-cart"></i>
						</div>

						<a href="#" class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti" data-notify="0">
							<i class="zmdi zmdi-favorite-outline"></i>
						</a>
					</div>
				</nav>
			</div>	
		</div>

		<!-- Header Mobile -->
		<div class="wrap-header-mobile">
			<!-- Logo moblie -->		
			<div class="logo-mobile">
				<a href="index.html"><img src="images/icons/logo-01.png" alt="IMG-LOGO"></a>
			</div>

			<!-- Icon header -->
			<div class="wrap-icon-header flex-w flex-r-m m-r-15">
				<div class="icon-header-item cl2 hov-cl1 trans-04 p-r-11 js-show-modal-search">
					<i class="zmdi zmdi-search"></i>
				</div>

				<div class="icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti js-show-cart" data-notify="2">
					<i class="zmdi zmdi-shopping-cart"></i>
				</div>

				<a href="#" class="dis-block icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti" data-notify="0">
					<i class="zmdi zmdi-favorite-outline"></i>
				</a>
			</div>

			<!-- Button show menu -->
			<div class="btn-show-menu-mobile hamburger hamburger--squeeze">
				<span class="hamburger-box">
					<span class="hamburger-inner"></span>
				</span>
			</div>
		</div>


		<!-- Menu Mobile -->
		<div class="menu-mobile">
			<ul class="topbar-mobile">
				<li>
					<div class="left-top-bar">
						Free shipping for standard order over $100
					</div>
				</li>

				<li>
					<div class="right-top-bar flex-w h-full">
						<a href="#" class="flex-c-m p-lr-10 trans-04">
							Help & FAQs
						</a>

						<a href="#" class="flex-c-m p-lr-10 trans-04">
							My Account
						</a>

						<a href="#" class="flex-c-m p-lr-10 trans-04">
							EN
						</a>

						<a href="#" class="flex-c-m p-lr-10 trans-04">
							USD
						</a>
					</div>
				</li>
			</ul>

			<ul class="main-menu-m">
				<li>
					<a href="index.html">Home</a>
					<ul class="sub-menu-m">
						<li><a href="index.html">Homepage 1</a></li>
						<li><a href="home-02.html">Homepage 2</a></li>
						<li><a href="home-03.html">Homepage 3</a></li>
					</ul>
					<span class="arrow-main-menu-m">
						<i class="fa fa-angle-right" aria-hidden="true"></i>
					</span>
				</li>

				<li>
					<a href="product.html">Shop</a>
				</li>

				<li>
					<a href="shoping-cart.html" class="label1 rs1" data-label1="hot">Features</a>
				</li>

				<li>
					<a href="blog.html">Blog</a>
				</li>

				<li>
					<a href="about.html">About</a>
				</li>

				<li>
					<a href="contact.html">Contact</a>
				</li>
			</ul>
		</div>

		<!-- Modal Search -->
		<div class="modal-search-header flex-c-m trans-04 js-hide-modal-search">
			<div class="container-search-header">
				<button class="flex-c-m btn-hide-modal-search trans-04 js-hide-modal-search">
					<img src="images/icons/icon-close2.png" alt="CLOSE">
				</button>

				<form class="wrap-search-header flex-w p-l-15">
					<button class="flex-c-m trans-04">
						<i class="zmdi zmdi-search"></i>
					</button>
					<input class="plh3" type="text" name="search" placeholder="Search...">
				</form>
			</div>
		</div>
	</header>

	<!-- Cart -->
<div class="wrap-header-cart js-panel-cart">
    <div class="s-full js-hide-cart"></div>

    <div class="header-cart flex-col-l p-l-65 p-r-25">
        <div class="header-cart-title flex-w flex-sb-m p-b-8">
            <span class="mtext-103 cl2">
                Your Cart
            </span>

            <div class="fs-35 lh-10 cl2 p-lr-5 pointer hov-cl1 trans-04 js-hide-cart">
                <i class="zmdi zmdi-close"></i>
            </div>
        </div>
        
        <div class="header-cart-content flex-w js-pscroll">
            <ul class="header-cart-wrapitem w-full" id="cart-items">
                <?php
				$cart_items_result = $conn->query($cart_items_query);
                // Display combined cart items
                $total_price = 0;
                if ($cart_items_result->num_rows > 0) {
                    while($cart_item = $cart_items_result->fetch_assoc()) {
                        $total_price += $cart_item['total_price'];
                        echo '
                        <li class="header-cart-item flex-w flex-t m-b-12">
                            <div class="header-cart-item-img">
                                <img src="images/' . $cart_item['product_image'] . '" alt="IMG">
                            </div>
                            <div class="header-cart-item-txt p-t-8">
                                <a href="#" class="header-cart-item-name m-b-18 hov-cl1 trans-04">
                                    ' . $cart_item['product_name'] . '
                                </a>
                                <span class="header-cart-item-info">
                                    ' . $cart_item['total_qty'] . ' x $' . number_format($cart_item['product_price'], 2) . '
                                </span>
                            </div>
                        </li>';
                    }
                } else {
                    echo '<p>Your cart is empty.</p>';
                }
                ?>
            </ul>
            
            <div class="w-full">
                <div class="header-cart-total w-full p-tb-40">
                    Total: $<span id="cart-total"><?php echo number_format($total_price, 2); ?></span>
                </div>

                <div class="header-cart-buttons flex-w w-full">
                    <a href="shoping-cart.html" class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-r-8 m-b-10">
                        View Cart
                    </a>

                    <a href="shoping-cart.html" class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-b-10">
                        Check Out
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>


	<!-- breadcrumb -->
	<div class="container">
		<div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
			<a href="index.html" class="stext-109 cl8 hov-cl1 trans-04">
				Home
				<i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
			</a>

			<span class="stext-109 cl4">
				Shoping Cart
			</span>
		</div>
	</div>
		

	<!-- Shopping Cart Form -->
<form class="bg0 p-t-75 p-b-85" method="POST" action="">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 col-xl-7 m-lr-auto m-b-50">
                <div class="m-l-25 m-r--38 m-lr-0-xl">
                    <div class="wrap-table-shopping-cart">
                        <table class="table-shopping-cart">
                            <tr class="table_head">
                                <th class="column-1">Product</th>
                                <th class="column-2"></th>
                                <th class="column-3">Price</th>
                                <th class="column-4">Quantity</th>
                                <th class="column-5">Total</th>
                            </tr>
                            <?php
                            if ($cart_items_result->num_rows > 0) {
								$cart_items_result = $conn->query($cart_items_query);
                                while ($cart_item = $cart_items_result->fetch_assoc()) {
                                    echo '
                                    <tr class="table_row">
                                        <td class="column-1">
                                            <div class="how-itemcart1">
                                                <img src="images/' . $cart_item['product_image'] . '" alt="IMG">
                                            </div>
                                        </td>
                                        <td class="column-2">' . $cart_item['product_name'] . '</td>
                                        <td class="column-3">$' . number_format($cart_item['product_price'], 2) . '</td>
                                        <td class="column-4">
                                            <div class="wrap-num-product flex-w m-l-auto m-r-0">
                                                <div class="btn-num-product-down cl8 hov-btn3 trans-04 flex-c-m">
                                                    <i class="fs-16 zmdi zmdi-minus"></i>
                                                </div>
                                                <input type="hidden" name="product_id[]" value="' . $cart_item['product_id'] . '">
                                                <input class="mtext-104 cl3 txt-center num-product" type="number" name="product_qty[' . $cart_item['product_id'] . ']" value="' . $cart_item['total_qty'] . '" readonly>
                                                <div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m">
                                                    <i class="fs-16 zmdi zmdi-plus"></i>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="column-5">$' . number_format($cart_item['total_price'], 2) . '</td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="5">Your cart is empty.</td></tr>';
                            }
                            ?>
                        </table>
                    </div>

                    <!-- Apply Coupon and Update Cart Buttons -->
                    <div class="flex-w flex-sb-m bor15 p-t-18 p-b-15 p-lr-40 p-lr-15-sm">
    					<div class="flex-w flex-m m-r-20 m-tb-5">
        					<input class="stext-104 cl2 plh4 size-117 bor13 p-lr-20 m-r-10 m-tb-5" type="text" name="coupon" placeholder="Voucher Code">
        					<button type="submit" name="apply_voucher" class="flex-c-m stext-101 cl2 size-118 bg8 bor13 hov-btn3 p-lr-15 trans-04 pointer m-tb-5">
            					Apply Voucher
        					</button>
    					</div>
    						<button type="submit" name="update_cart" class="flex-c-m stext-101 cl2 size-119 bg8 bor13 hov-btn3 p-lr-15 trans-04 pointer m-tb-10">
       					 		Update Cart
    						</button>
					</div>

					<?php if (!empty($error_message)): ?>
    					<p class="text-danger"><?php echo $error_message; ?></p>
					<?php endif; ?>

					<!-- Subtotal Section with Discount -->
					<div class="flex-w flex-sb-m bor15 p-t-18 p-b-15 p-lr-40 p-lr-15-sm">
    					<div class="size-208">
        					<span class="stext-110 cl2">Subtotal:</span>
    					</div>
    					<div class="size-209">
    						<span class="mtext-110 cl2">
        						$<?php echo isset($_SESSION['final_total_price']) ? number_format($_SESSION['final_total_price'], 2) : number_format($final_total_price, 2); ?>
    						</span>
						</div>
						 <!-- Hidden field to pass discount amount to checkout.php -->
    					<input type="hidden" name="discount_amount" value="<?php echo $discount_amount; ?>">
    
    					<!-- Check Out Button with form action to checkout.php -->
    					<button type="submit" formaction="checkout.php" class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-r-8 m-b-10">
        					Check Out
    					</button>
					</div>
                </div>
            </div>
        </div>
    </div>
</form>
		
	
		

	<!-- Footer -->
	<footer class="bg3 p-t-75 p-b-32">
		<div class="container">
			<div class="row">
				<div class="col-sm-6 col-lg-3 p-b-50">
					<h4 class="stext-301 cl0 p-b-30">
						Categories
					</h4>

					<ul>
						<li class="p-b-10">
							<a href="#" class="stext-107 cl7 hov-cl1 trans-04">
								Women
							</a>
						</li>

						<li class="p-b-10">
							<a href="#" class="stext-107 cl7 hov-cl1 trans-04">
								Men
							</a>
						</li>

						<li class="p-b-10">
							<a href="#" class="stext-107 cl7 hov-cl1 trans-04">
								Shoes
							</a>
						</li>

						<li class="p-b-10">
							<a href="#" class="stext-107 cl7 hov-cl1 trans-04">
								Watches
							</a>
						</li>
					</ul>
				</div>

				<div class="col-sm-6 col-lg-3 p-b-50">
					<h4 class="stext-301 cl0 p-b-30">
						Help
					</h4>

					<ul>
						<li class="p-b-10">
							<a href="#" class="stext-107 cl7 hov-cl1 trans-04">
								Track Order
							</a>
						</li>

						<li class="p-b-10">
							<a href="#" class="stext-107 cl7 hov-cl1 trans-04">
								Returns 
							</a>
						</li>

						<li class="p-b-10">
							<a href="#" class="stext-107 cl7 hov-cl1 trans-04">
								Shipping
							</a>
						</li>

						<li class="p-b-10">
							<a href="#" class="stext-107 cl7 hov-cl1 trans-04">
								FAQs
							</a>
						</li>
					</ul>
				</div>

				<div class="col-sm-6 col-lg-3 p-b-50">
					<h4 class="stext-301 cl0 p-b-30">
						GET IN TOUCH
					</h4>

					<p class="stext-107 cl7 size-201">
						Any questions? Let us know in store at 8th floor, 379 Hudson St, New York, NY 10018 or call us on (+1) 96 716 6879
					</p>

					<div class="p-t-27">
						<a href="#" class="fs-18 cl7 hov-cl1 trans-04 m-r-16">
							<i class="fa fa-facebook"></i>
						</a>

						<a href="#" class="fs-18 cl7 hov-cl1 trans-04 m-r-16">
							<i class="fa fa-instagram"></i>
						</a>

						<a href="#" class="fs-18 cl7 hov-cl1 trans-04 m-r-16">
							<i class="fa fa-pinterest-p"></i>
						</a>
					</div>
				</div>

				<div class="col-sm-6 col-lg-3 p-b-50">
					<h4 class="stext-301 cl0 p-b-30">
						Newsletter
					</h4>

					<form>
						<div class="wrap-input1 w-full p-b-4">
							<input class="input1 bg-none plh1 stext-107 cl7" type="text" name="email" placeholder="email@example.com">
							<div class="focus-input1 trans-04"></div>
						</div>

						<div class="p-t-18">
							<button class="flex-c-m stext-101 cl0 size-103 bg1 bor1 hov-btn2 p-lr-15 trans-04">
								Subscribe
							</button>
						</div>
					</form>
				</div>
			</div>

			<div class="p-t-40">
				<div class="flex-c-m flex-w p-b-18">
					<a href="#" class="m-all-1">
						<img src="images/icons/icon-pay-01.png" alt="ICON-PAY">
					</a>

					<a href="#" class="m-all-1">
						<img src="images/icons/icon-pay-02.png" alt="ICON-PAY">
					</a>

					<a href="#" class="m-all-1">
						<img src="images/icons/icon-pay-03.png" alt="ICON-PAY">
					</a>

					<a href="#" class="m-all-1">
						<img src="images/icons/icon-pay-04.png" alt="ICON-PAY">
					</a>

					<a href="#" class="m-all-1">
						<img src="images/icons/icon-pay-05.png" alt="ICON-PAY">
					</a>
				</div>

				<p class="stext-107 cl6 txt-center">
					<!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved | Made with <i class="fa fa-heart-o" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Colorlib</a> &amp; distributed by <a href="https://themewagon.com" target="_blank">ThemeWagon</a>
<!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->

				</p>
			</div>
		</div>
	</footer>


	<!-- Back to top -->
	<div class="btn-back-to-top" id="myBtn">
		<span class="symbol-btn-back-to-top">
			<i class="zmdi zmdi-chevron-up"></i>
		</span>
	</div>

	<script src="vendor/jquery/jquery-3.2.1.min.js"></script>
	<script src="vendor/animsition/js/animsition.min.js"></script>
	<script src="vendor/bootstrap/js/popper.js"></script>
	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
	<script src="vendor/select2/select2.min.js"></script>
	<script>
		$(".js-select2").each(function(){
			$(this).select2({
				minimumResultsForSearch: 20,
				dropdownParent: $(this).next('.dropDownSelect2')
			});
		})
	</script>
	<script src="vendor/MagnificPopup/jquery.magnific-popup.min.js"></script>
	<script src="vendor/perfect-scrollbar/perfect-scrollbar.min.js"></script>
	<script>
		$('.js-pscroll').each(function(){
			$(this).css('position','relative');
			$(this).css('overflow','hidden');
			var ps = new PerfectScrollbar(this, {
				wheelSpeed: 1,
				scrollingThreshold: 1000,
				wheelPropagation: false,
			});

			$(window).on('resize', function(){
				ps.update();
			})
		});
	</script>
	<script>
$(document).ready(function() {
    $('.btn-num-product-down').click(function() {
        let input = $(this).siblings('.num-product');
        let currentValue = parseInt(input.val());
        if (currentValue > 1) {
            input.val(currentValue - 1);
        } else {
            input.val(0); // Set to 0 if quantity becomes 0
        }
    });

    $('.btn-num-product-up').click(function() {
        let input = $(this).siblings('.num-product');
        let currentValue = parseInt(input.val());
        input.val(currentValue + 1);
    });
});
</script>
	<script src="js/main.js"></script>

</body>
</html>
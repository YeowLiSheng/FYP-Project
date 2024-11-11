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
$user_result = mysqli_query($conn, "SELECT * FROM user WHERE user_id ='$user_id'");
$address_result = mysqli_query($conn, "SELECT * FROM user_address WHERE user_id ='$user_id'");

// Check if the query was successful and fetch user data
if ($user_result && mysqli_num_rows($user_result) > 0) {
	$user = mysqli_fetch_assoc($user_result);
} else {
	echo "User not found.";
	exit;
}

// Fetch the address information if available
$address = null;
if ($address_result && mysqli_num_rows($address_result) > 0) {
	$address = mysqli_fetch_assoc($address_result);
}

// Retrieve unique products with total quantity and price in the cart for the logged-in user
$cart_query = "
    SELECT 
        p.product_id,
        p.product_name, 
        p.product_price, 
        p.product_image,
        SUM(sc.qty) AS total_qty, 
        (p.product_price * SUM(sc.qty)) AS item_total_price
    FROM 
        shopping_cart AS sc
    JOIN 
        product AS p ON sc.product_id = p.product_id
    WHERE 
        sc.user_id = '$user_id'
    GROUP BY 
        p.product_id
";

$cart_result = mysqli_query($conn, $cart_query);

if ($cart_result && mysqli_num_rows($cart_result) > 0) {



	// Retrieve discount amount for the user from shopping_cart table
	$discount_query = "SELECT discount_amount FROM shopping_cart WHERE user_id = '$user_id' LIMIT 1";
	$discount_result = mysqli_query($conn, $discount_query);

	if ($discount_result && mysqli_num_rows($discount_result) > 0) {
		$discount_row = mysqli_fetch_assoc($discount_result);
		$discount_amount = $discount_row['discount_amount'];
	} else {
		$discount_amount = 0; // Default to 0 if no discount is found
	}
} else {
	echo "<p>Your cart is empty.</p>";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cardHolderName = $_POST['cardHolderName'];
    $cardNum = str_replace(' ', '', $_POST['cardNum']); // Remove spaces
    $expiryDate = $_POST['expiry-date'];
    $cvv = $_POST['cvv'];

    $query = "SELECT * FROM bank_card WHERE card_holder_name = ? AND card_number = ? AND valid_thru = ? AND cvv = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $cardHolderName, $cardNum, $expiryDate, $cvv);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>confirmPayment();</script>";
    } else {
        echo "<script>alert('Invalid card details');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>Home</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--===============================================================================================-->
	<link rel="icon" type="image/png" href="images/icons/favicon.png" />
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
	<link rel="stylesheet" type="text/css" href="vendor/daterangepicker/daterangepicker.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/slick/slick.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/MagnificPopup/magnific-popup.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/perfect-scrollbar/perfect-scrollbar.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" href="css/checkout.css">
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
							EN
						</a>

						<a href="#" class="flex-c-m trans-04 p-lr-25">
							USD
						</a>




						<a href="edit_profile.php?edit_user=<?php echo $user_id; ?>" class="flex-c-m trans-04 p-lr-25">
							<?php
							echo "HI '" . htmlspecialchars($user["user_name"]);
							?>
						</a>


						<a href="log_out.php" class="flex-c-m trans-04 p-lr-25">
							LOG OUT
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
								<a href="dashboard.php">Home</a>
								<ul class="sub-menu">
									<li><a href="index.html">Homepage 1</a></li>
									<li><a href="home-02.html">Homepage 2</a></li>
									<li><a href="home-03.html">Homepage 3</a></li>
								</ul>
							</li>

							<li class="active-menu">
								<a href="product.html">Shop</a>
							</li>

							<li class="label1" data-label1="hot">
								<a href="voucher_page.php">Voucher</a>
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

						<div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti js-show-cart"
							data-notify="2">
							<i class="zmdi zmdi-shopping-cart"></i>
						</div>

						<a href="#" class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti"
							data-notify="0">
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

				<div class="icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti js-show-cart"
					data-notify="2">
					<i class="zmdi zmdi-shopping-cart"></i>
				</div>

				<a href="#" class="dis-block icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti"
					data-notify="0">
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
					<a href="dashboard.php">Home</a>
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
					<a href="product.php">Shop</a>
				</li>

				<li>
					<a href="shoping-cart.php" class="label1 rs1" data-label1="hot">Features</a>
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
					// Display combined cart items
					$total_price = 0;
					if ($cart_items_result->num_rows > 0) {
						while ($cart_item = $cart_items_result->fetch_assoc()) {
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
						<a href="shoping-cart.php"
							class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-r-8 m-b-10">
							View Cart
						</a>

						<a href="shoping-cart.html"
							class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-b-10">
							Check Out
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>



	<body class="checkout-root checkout-reset">

		<div class="checkout-container">
		<form action="checkout.php" method="post" onsubmit="return validateForm(event)">

				<div class="checkout-row">
					<!-- Billing Address Section -->
					<div class="checkout-column">
						<h3 class="checkout-title">Delivery Address</h3>

						<div class="checkout-input-box">
							<span class="required">Full Name :</span>
							<input type="text" value="<?php echo htmlspecialchars($user['user_name']); ?>" required>

						</div>
						<div class="checkout-input-box">
							<span class="required">Email :</span>
							<input type="email" value="<?php echo htmlspecialchars($user['user_email']); ?>" required>
						</div>
						<div class="checkout-input-box">
							<span class="required">Address :</span>
							<input type="text" id="address" value="" required>
						</div>
						<div class="checkout-input-box">
							<span class="required">City :</span>
							<input type="text" id="city" value="" required>
						</div>
						<div class="checkout-flex">
							<div class="checkout-input-box">
								<span class="required">State :</span>
								<input type="text" id="state" value="" required>
							</div>
							<div class="checkout-input-box">
								<span class="required">Postcode :</span>
								<input type="number" id="postcode" value="" required>
							</div>
						</div>

						<!-- Checkbox in a new row -->
						<div class="autofill-checkbox">
							<input type="checkbox" id="autofill-checkbox" onclick="toggleAutofill()">
							<label for="autofill-checkbox">Use saved address information</label>
						</div>
					</div>

					<!-- Payment Section -->
					<div class="checkout-column">
						<h3 class="checkout-title">Payment</h3>
						<div class="checkout-input-box">
							<span>Cards Accepted :</span>
							<img src="images/payment card.png" alt="Cards Accepted">
						</div>
						<div class="checkout-input-box">
							<span>Card Holder Name :</span>
							<input type="text" name="cardHolderName" placeholder="Cheong Wei Kit" autocomplete="off" required>
						</div>
						<div class="checkout-input-box">
							<span> Card Number :</span>
							<input type="text" name="cardNum" placeholder="1111 2222 3333 4444" minlength="16"
								maxlength="19" pattern="\d{4}\s\d{4}\s\d{4}\s\d{4}"
								title="Please enter exactly 16 digits" autocomplete="off" required
								oninput="formatCardNumber(this)">
						</div>
						<div class="checkout-input-box">
							<span>Message for Seller :</span>
							<input type="text" placeholder="leave a message (optional)">
						</div>

						<div class="checkout-flex">
							<div class="checkout-input-box">
								<span>Valid Thru (MM/YY) :</span>
								<input type="text" name="expiry-date" id="expiry-date" placeholder="MM/YY" required>
								<small id="expiry-error" style="color: red; display: none;">Please enter a valid,
									non-expired date.</small>
							</div>
							<div class="checkout-input-box">
								<span>CVV :</span>
								<input type="number" name="cvv" id="cvv" placeholder="123" maxlength="3" oninput="validateCVV()"
									required>
								<small id="cvv-error" style="color: red; display: none;">Please enter a 3-digit CVV
									code.</small>

							</div>
						</div>
					</div>

					<!-- Order Summary Section -->
					<div class="checkout-column checkout-order-summary">
						<h3 class="checkout-title">Your Order</h3>
						<!-- Product List -->
						<?php

						$grand_total = 0;

						while ($row = mysqli_fetch_assoc($cart_result)):
							$product_name = $row['product_name'];
							$product_price = $row['product_price'];
							$product_image = $row['product_image'];
							$total_qty = $row['total_qty'];
							$item_total_price = $row['item_total_price'];


							// Accumulate the grand total
							$grand_total += $item_total_price;

							?>
							<div class="checkout-order-item">
								<img src="images/<?php echo htmlspecialchars($product_image); ?>"
									alt="<?php echo htmlspecialchars($product_name); ?>">
								<div>
									<p><?php echo htmlspecialchars($product_name); ?></p>
									<span>Price: RM<?php echo number_format($product_price, 2); ?></span><br>
									<span>Quantity: <?php echo $total_qty; ?></span><br>
									<span>Subtotal: RM<?php echo number_format($item_total_price, 2); ?></span>
								</div>
							</div>
						<?php endwhile; ?>

						<!-- Order Totals -->
						<div class="checkout-order-totals">
							<?php
							// Assuming $discount is calculated elsewhere or based on some logic
							$delivery_charge = 10;
							$total_payment = $grand_total - $discount_amount + $delivery_charge;
							?>
							<p>Grand total: <span>RM<?php echo number_format($grand_total, 2); ?></span></p>
							<p>Discount: <span>-RM<?php echo number_format($discount_amount, 2); ?></span></p>
							<p>Delivery Charge: <span>RM<?php echo number_format($delivery_charge, 2); ?></span></p>
							<p class="checkout-total">Total Payment:
								<span>RM<?php echo number_format($total_payment, 2); ?></span></p>
						</div>


						<!-- Confirm Payment Button -->
						<button type="submit" class="checkout-btn">Confirm Payment</button>

						<!-- Payment Processing Popup -->
						<div class="overlay" id="paymentOverlay">
							<div class="popup" id="popupContent">
								<div class="spinner"></div>
								<p>Payment Processing...</p>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>

	</body>

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
						Any questions? Let us know in store at 8th floor, 379 Hudson St, New York, NY 10018 or call us
						on (+1) 96 716 6879
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
							<input class="input1 bg-none plh1 stext-107 cl7" type="text" name="email"
								placeholder="email@example.com">
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
					Copyright &copy;
					<script>document.write(new Date().getFullYear());</script> All rights reserved | Made with <i
						class="fa fa-heart-o" aria-hidden="true"></i> by <a href="https://colorlib.com"
						target="_blank">Colorlib</a> &amp; distributed by <a href="https://themewagon.com"
						target="_blank">ThemeWagon</a>
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

	<!-- Modal1 -->
	<div class="wrap-modal1 js-modal1 p-t-60 p-b-20">
		<div class="overlay-modal1 js-hide-modal1"></div>

		<div class="container">
			<div class="bg0 p-t-60 p-b-30 p-lr-15-lg how-pos3-parent">
				<button class="how-pos3 hov3 trans-04 js-hide-modal1">
					<img src="images/icons/icon-close.png" alt="CLOSE">
				</button>

				<div class="row">
					<div class="col-md-6 col-lg-7 p-b-30">
						<div class="p-l-25 p-r-30 p-lr-0-lg">
							<div class="wrap-slick3 flex-sb flex-w">
								<div class="wrap-slick3-dots"></div>
								<div class="wrap-slick3-arrows flex-sb-m flex-w"></div>

								<div class="slick3 gallery-lb">
									<div class="item-slick3" data-thumb="images/product-detail-01.jpg">
										<div class="wrap-pic-w pos-relative">
											<img src="images/product-detail-01.jpg" alt="IMG-PRODUCT">

											<a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04"
												href="images/product-detail-01.jpg">
												<i class="fa fa-expand"></i>
											</a>
										</div>
									</div>

									<div class="item-slick3" data-thumb="images/product-detail-02.jpg">
										<div class="wrap-pic-w pos-relative">
											<img src="images/product-detail-02.jpg" alt="IMG-PRODUCT">

											<a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04"
												href="images/product-detail-02.jpg">
												<i class="fa fa-expand"></i>
											</a>
										</div>
									</div>

									<div class="item-slick3" data-thumb="images/product-detail-03.jpg">
										<div class="wrap-pic-w pos-relative">
											<img src="images/product-detail-03.jpg" alt="IMG-PRODUCT">

											<a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04"
												href="images/product-detail-03.jpg">
												<i class="fa fa-expand"></i>
											</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-6 col-lg-5 p-b-30">
						<div class="p-r-50 p-t-5 p-lr-0-lg">
							<h4 class="mtext-105 cl2 js-name-detail p-b-14">
								Lightweight Jacket
							</h4>

							<span class="mtext-106 cl2">
								$58.79
							</span>

							<p class="stext-102 cl3 p-t-23">
								Nulla eget sem vitae eros pharetra viverra. Nam vitae luctus ligula. Mauris consequat
								ornare feugiat.
							</p>

							<!--  -->
							<div class="p-t-33">
								<div class="flex-w flex-r-m p-b-10">
									<div class="size-203 flex-c-m respon6">
										Size
									</div>

									<div class="size-204 respon6-next">
										<div class="rs1-select2 bor8 bg0">
											<select class="js-select2" name="time">
												<option>Choose an option</option>
												<option>Size S</option>
												<option>Size M</option>
												<option>Size L</option>
												<option>Size XL</option>
											</select>
											<div class="dropDownSelect2"></div>
										</div>
									</div>
								</div>

								<div class="flex-w flex-r-m p-b-10">
									<div class="size-203 flex-c-m respon6">
										Color
									</div>

									<div class="size-204 respon6-next">
										<div class="rs1-select2 bor8 bg0">
											<select class="js-select2" name="time">
												<option>Choose an option</option>
												<option>Red</option>
												<option>Blue</option>
												<option>White</option>
												<option>Grey</option>
											</select>
											<div class="dropDownSelect2"></div>
										</div>
									</div>
								</div>

								<div class="flex-w flex-r-m p-b-10">
									<div class="size-204 flex-w flex-m respon6-next">
										<div class="wrap-num-product flex-w m-r-20 m-tb-10">
											<div class="btn-num-product-down cl8 hov-btn3 trans-04 flex-c-m">
												<i class="fs-16 zmdi zmdi-minus"></i>
											</div>

											<input class="mtext-104 cl3 txt-center num-product" type="number"
												name="num-product" value="1">

											<div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m">
												<i class="fs-16 zmdi zmdi-plus"></i>
											</div>
										</div>

										<button
											class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 trans-04 js-addcart-detail">
											Add to cart
										</button>
									</div>
								</div>
							</div>

							<!--  -->
							<div class="flex-w flex-m p-l-100 p-t-40 respon7">
								<div class="flex-m bor9 p-r-10 m-r-11">
									<a href="#"
										class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 js-addwish-detail tooltip100"
										data-tooltip="Add to Wishlist">
										<i class="zmdi zmdi-favorite"></i>
									</a>
								</div>

								<a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 m-r-8 tooltip100"
									data-tooltip="Facebook">
									<i class="fa fa-facebook"></i>
								</a>

								<a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 m-r-8 tooltip100"
									data-tooltip="Twitter">
									<i class="fa fa-twitter"></i>
								</a>

								<a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 m-r-8 tooltip100"
									data-tooltip="Google Plus">
									<i class="fa fa-google-plus"></i>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!--===============================================================================================-->
	<script src="vendor/jquery/jquery-3.2.1.min.js"></script>
	<!--===============================================================================================-->
	<script src="vendor/animsition/js/animsition.min.js"></script>
	<!--===============================================================================================-->
	<script src="vendor/bootstrap/js/popper.js"></script>
	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
	<!--===============================================================================================-->
	<script src="vendor/select2/select2.min.js"></script>
	<script>
		$(".js-select2").each(function () {
			$(this).select2({
				minimumResultsForSearch: 20,
				dropdownParent: $(this).next('.dropDownSelect2')
			});
		})
	</script>
	<!--===============================================================================================-->
	<script src="vendor/daterangepicker/moment.min.js"></script>
	<script src="vendor/daterangepicker/daterangepicker.js"></script>
	<!--===============================================================================================-->
	<script src="vendor/slick/slick.min.js"></script>
	<script src="js/slick-custom.js"></script>
	<!--===============================================================================================-->
	<script src="vendor/parallax100/parallax100.js"></script>
	<script>
		$('.parallax100').parallax100();
	</script>
	<!--===============================================================================================-->
	<script src="vendor/MagnificPopup/jquery.magnific-popup.min.js"></script>
	<script>
		$('.gallery-lb').each(function () { // the containers for all your galleries
			$(this).magnificPopup({
				delegate: 'a', // the selector for gallery item
				type: 'image',
				gallery: {
					enabled: true
				},
				mainClass: 'mfp-fade'
			});
		});
	</script>
	<!--===============================================================================================-->
	<script src="vendor/isotope/isotope.pkgd.min.js"></script>
	<!--===============================================================================================-->
	<script src="vendor/sweetalert/sweetalert.min.js"></script>
	<script>
		$('.js-addwish-b2').on('click', function (e) {
			e.preventDefault();
		});

		$('.js-addwish-b2').each(function () {
			var nameProduct = $(this).parent().parent().find('.js-name-b2').html();
			$(this).on('click', function () {
				swal(nameProduct, "is added to wishlist !", "success");

				$(this).addClass('js-addedwish-b2');
				$(this).off('click');
			});
		});

		$('.js-addwish-detail').each(function () {
			var nameProduct = $(this).parent().parent().parent().find('.js-name-detail').html();

			$(this).on('click', function () {
				swal(nameProduct, "is added to wishlist !", "success");

				$(this).addClass('js-addedwish-detail');
				$(this).off('click');
			});
		});

		/*---------------------------------------------*/

		$('.js-addcart-detail').each(function () {
			var nameProduct = $(this).parent().parent().parent().parent().find('.js-name-detail').html();
			$(this).on('click', function () {
				swal(nameProduct, "is added to cart !", "success");
			});
		});

	</script>
	<!--===============================================================================================-->
	<script src="vendor/perfect-scrollbar/perfect-scrollbar.min.js"></script>
	<script>
		$('.js-pscroll').each(function () {
			$(this).css('position', 'relative');
			$(this).css('overflow', 'hidden');
			var ps = new PerfectScrollbar(this, {
				wheelSpeed: 1,
				scrollingThreshold: 1000,
				wheelPropagation: false,
			});

			$(window).on('resize', function () {
				ps.update();
			})
		});
	</script>
	<!--===============================================================================================-->
	<script src="js/main.js"></script>

	<script>

		function toggleAutofill() {
			const autofillCheckbox = document.getElementById('autofill-checkbox');
			const address = document.getElementById('address');
			const city = document.getElementById('city');
			const state = document.getElementById('state');
			const postcode = document.getElementById('postcode');

			if (autofillCheckbox.checked) {
				// Fill with saved data if checkbox is checked
				address.value = "<?php echo htmlspecialchars($address['address'] ?? ''); ?>";
				city.value = "<?php echo htmlspecialchars($address['city'] ?? ''); ?>";
				state.value = "<?php echo htmlspecialchars($address['state'] ?? ''); ?>";
				postcode.value = "<?php echo htmlspecialchars($address['postcode'] ?? ''); ?>";
			} else {
				// Clear fields for manual input if checkbox is unchecked
				address.value = "";
				city.value = "";
				state.value = "";
				postcode.value = "";
			}
		}



		document.getElementById('expiry-date').addEventListener('input', function () {
			const input = this.value;
			const error = document.getElementById('expiry-error');

			// Check if the input matches MM/YY format using regex
			const datePattern = /^(0[1-9]|1[0-2])\/\d{2}$/;
			if (!datePattern.test(input)) {
				error.style.display = 'none';
				return;
			}

			// Parse month and year from input
			const [month, year] = input.split('/').map(Number);
			const currentYear = new Date().getFullYear() % 100; // last two digits of current year
			const currentMonth = new Date().getMonth() + 1; // months are zero-indexed

			// Check if the entered date is valid (current month/year or later)
			if (year > currentYear || (year === currentYear && month >= currentMonth)) {
				error.style.display = 'none'; // hide error message if valid
			} else {
				error.style.display = 'block'; // show error message if expired
				this.value = ''; // clear input field
			}
		});

		function formatCardNumber(input) {
			// Remove all spaces and get only digits
			let cardNum = input.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');

			// Split into groups of 4 digits
			let formattedCardNum = cardNum.match(/.{1,4}/g);

			// Join groups with a space
			if (formattedCardNum) {
				input.value = formattedCardNum.join(' ');
			}
		}

		document.getElementById("expiry-date").addEventListener("input", function (e) {
    const input = e.target;
    let value = input.value.replace(/\D/g, ""); // Remove non-digit characters

    // Insert '/' after the month if exactly two digits are entered
    if (value.length > 2) {
        value = value.slice(0, 2) + '/' + value.slice(2, 4);
    }

    // Limit to 5 characters (MM/YY)
    if (value.length > 5) {
        value = value.slice(0, 5);
    }

    input.value = value;
});
		function validateCVV() {
			const cvvInput = document.getElementById("cvv");
			const cvvError = document.getElementById("cvv-error");


			if (cvvInput.value.length > 3) {
				cvvInput.value = cvvInput.value.slice(0, 3);
			}


			if (cvvInput.value.length < 3) {
				cvvInput.setCustomValidity("Please enter a 3-digit CVV code.");
				cvvError.style.display = "inline";
			} else {
				cvvInput.setCustomValidity("");
				cvvError.style.display = "none";
			}
		}

		function validateForm(event) {
    const fullName = document.querySelector('input[name="cardHolderName"]');
    const cardNum = document.querySelector('input[name="cardNum"]');
    const expiryDate = document.getElementById('expiry-date');
    const cvv = document.getElementById('cvv');
    const address = document.getElementById('address');
    const city = document.getElementById('city');
    const state = document.getElementById('state');
    const postcode = document.getElementById('postcode');

    if (
        !fullName.value.trim() ||
        !cardNum.value.trim() ||
        !expiryDate.value.trim() ||
        !cvv.value.trim() ||
        !address.value.trim() ||
        !city.value.trim() ||
        !state.value.trim() ||
        !postcode.value.trim()
    ) {
        alert('Please fill in all required fields.');
        event.preventDefault();
        return false;
    }

    const cardNumberPattern = /^\d{4}\s\d{4}\s\d{4}\s\d{4}$/;
    if (!cardNumberPattern.test(cardNum.value)) {
        alert('Please enter a valid 16-digit card number (format: 1111 2222 3333 4444).');
        event.preventDefault();
        return false;
    }

    if (cvv.value.length !== 3) {
        alert('Please enter a 3-digit CVV code.');
        event.preventDefault();
        return false;
    }

    const datePattern = /^(0[1-9]|1[0-2])\/\d{2}$/;
    if (!datePattern.test(expiryDate.value)) {
        alert('Please enter a valid expiration date (format: MM/YY).');
        event.preventDefault();
        return false;
    } else {
        const [month, year] = expiryDate.value.split('/').map(Number);
        const currentYear = new Date().getFullYear() % 100;
        const currentMonth = new Date().getMonth() + 1;
        if (year < currentYear || (year === currentYear && month < currentMonth)) {
            alert('Please enter a valid, non-expired expiration date.');
            event.preventDefault();
            return false;
        }
    }

    return true; // Allow form submission if all validations pass
}


function handleSubmit(event) {
    // 阻止表单的默认提交行为
    event.preventDefault();

    // 验证表单字段
    if (validateForm()) {
        confirmPayment(); // 验证通过后显示付款处理状态
    }
}

function confirmPayment() {
    const overlay = document.getElementById('paymentOverlay');
    const popupContent = document.getElementById('popupContent');
    overlay.classList.add('show');

    setTimeout(() => {
        popupContent.innerHTML = `
            <div class="success-icon">✓</div>
            <h2 class="success-title">Payment Successful</h2>
            <button class="ok-btn" onclick="goToDashboard()">OK</button>
        `;
    }, 2000); 
}

function goToDashboard() {
		
		window.location.href = 'dashboard.php';
	}





	</script>
</body>

</html>
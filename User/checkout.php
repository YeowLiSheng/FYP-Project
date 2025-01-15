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
		pv.variant_id,
        pv.color, 
        pv.size, 
        pv.Quick_View1 AS product_image,
        SUM(sc.qty) AS total_qty, 
        (p.product_price * SUM(sc.qty)) AS item_total_price
    FROM 
        shopping_cart AS sc
    JOIN 
        product_variant AS pv ON sc.variant_id = pv.variant_id
    JOIN 
        product AS p ON pv.product_id = p.product_id
    WHERE 
        sc.user_id = '$user_id'
    GROUP BY 
        pv.variant_id
";

$cart_result = mysqli_query($conn, $cart_query);


if (mysqli_num_rows($cart_result) === 0) {

    echo "<script>
        alert('Your Shopping Cart is Empty. Please add product first.');
        window.location.href = 'product.php'; 
    </script>";
    exit; 
}



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
$paymentSuccess = false; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $cardHolderName = isset($_POST['cardHolderName']) ? $_POST['cardHolderName'] : '';
    $cardNum = isset($_POST['cardNum']) ? $_POST['cardNum'] : '';
    $expiryDate = isset($_POST['expiry-date']) ? $_POST['expiry-date'] : '';
    $cvv = isset($_POST['cvv']) ? $_POST['cvv'] : '';
    $errorMessages = [];

	if (!empty($cardHolderName) && !empty($cardNum) && !empty($expiryDate) && !empty($cvv)) {

        // Validate card details
        $query = "SELECT * FROM bank_card WHERE card_holder_name = ? AND card_number = ? AND valid_thru = ? AND cvv = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $cardHolderName, $cardNum, $expiryDate, $cvv);
        $stmt->execute();
        $result = $stmt->get_result();

		if ($result->num_rows > 0) {
            $paymentSuccess = true;
            
            // Check stock for each product in the cart
            $cart_result = mysqli_query($conn, $cart_query);
            while ($row = mysqli_fetch_assoc($cart_result)) {
                $variant_id = $row['variant_id'];
                $product_name = $row['product_name'];
                $total_qty = $row['total_qty'];

                // Get current stock
                $stock_query = "SELECT stock FROM product_variant WHERE variant_id = ?";
                $stock_stmt = $conn->prepare($stock_query);
                $stock_stmt->bind_param("i", $variant_id);
                $stock_stmt->execute();
                $stock_result = $stock_stmt->get_result();

                if ($stock_row = $stock_result->fetch_assoc()) {
                    $current_stock = $stock_row['stock'];

                    if ($current_stock <= 0) {
                        $errorMessages[] = "$product_name is out of stock. Please select again product in your shopping cart.";
                        // Remove out-of-stock product from cart
                        $delete_query = "DELETE FROM shopping_cart WHERE variant_id = ? AND user_id = ?";
                        $delete_stmt = $conn->prepare($delete_query);
                        $delete_stmt->bind_param("ii", $variant_id, $user_id);
                        $delete_stmt->execute();
                        $delete_stmt->close();
                    } elseif ($total_qty > $current_stock) {
                        $errorMessages[] = "$product_name only has $current_stock items left, cannot fulfill requested quantity of $total_qty. Please select again product in your shopping cart.";
                        // Adjust the quantity in the cart to match available stock
                        $update_query = "UPDATE shopping_cart SET qty = ? WHERE variant_id = ? AND user_id = ?";
                        $update_stmt = $conn->prepare($update_query);
                        $update_stmt->bind_param("iii", $current_stock, $variant_id, $user_id);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }
                }
            }

            // If there are error messages, display them and prevent payment processing
            if (!empty($errorMessages)) {
                foreach ($errorMessages as $message) {
                    echo "<script>alert('$message');window.location.href = 'dashboard.php';</script>";
                }
                $paymentSuccess = false; // Prevent further processing if there are stock issues
            }
        } else {
            echo "<script>alert('Invalid card details');</script>";
        }

        $stmt->close();
	}


    // 保存状态到会话
    $_SESSION['paymentSuccess'] = $paymentSuccess;
    $_SESSION['errorMessages'] = $errorMessages;

    // 重定向到当前页面
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// 页面加载时检查会话信息
$paymentSuccess = $_SESSION['paymentSuccess'] ?? null;
$errorMessages = $_SESSION['errorMessages'] ?? [];

// 清除会话数据
unset($_SESSION['paymentSuccess']);
unset($_SESSION['errorMessages']);
	


?>

<?php if ($paymentSuccess): 
	
	if ($paymentSuccess) {
		foreach ($cart_result as $item) {
			$variant_id = $item['variant_id'];
			$total_qty = $item['total_qty']; 
	

			$update_stock_query = "UPDATE product_variant SET stock = stock - ? WHERE variant_id = ?";
			$update_stock_stmt = $conn->prepare($update_stock_query);
			$update_stock_stmt->bind_param("ii", $total_qty, $variant_id);
	
			if (!$update_stock_stmt->execute()) {
				die("Error updating stock for variant_id $variant_id: " . $update_stock_stmt->error);
			}
		}
	
	
	}?>
	<script>
	window.onload = function() {
	confirmPayment();
	}
	</script>
	<?php endif; ?>			


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

<style>
        

    .checkout-input-box select {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 15px;
        font-family: 'Poppins', sans-serif;
        color: #555;
        background-color: #fff;
        transition: border-color 0.3s, box-shadow 0.3s;
        appearance: none; /* Hides default arrow for consistent styling */
        background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" width="10" height="5" viewBox="0 0 10 5"><path fill="%23555" d="M0 0l5 5 5-5z"/></svg>');
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 12px;
    }

    .checkout-input-box select:focus,
    .checkout-input-box select:hover {
        border-color: #8175d3;
        box-shadow: 0 0 5px rgba(129, 117, 211, 0.5);
        outline: none;
    }

    .checkout-input-box select option:disabled {
        color: #aaa;
    }

    /* Add scrolling and set max visible items */
    .checkout-input-box select {
        overflow-y: auto; /* Enable vertical scrolling */
        max-height: 150px; /* Limit height to show 3 items */
    }
	/* 调整 state 和 postcode 的 flex 属性 */
.checkout-flex .checkout-input-box:nth-child(1) {
    flex: 2; /* 增大 postcode 输入框的大小 */
}

.checkout-flex .checkout-input-box:nth-child(2) {
    flex: 1; /* 缩小 state 输入框的大小 */
}
    </style>

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
			<form action="checkout.php" method="post" onsubmit="return validateForm()">

				<div class="checkout-row">
					<!-- Billing Address Section -->
					<div class="checkout-column">
						<h3 class="checkout-title">Delivery Address</h3>

						<div class="checkout-input-box">
							<span class="required">Full Name :</span>
							<input type="text" value="<?php echo htmlspecialchars($user['user_name']); ?>"  readonly>

						</div>
						<div class="checkout-input-box">
							<span class="required">Email :</span>
							<input type="email" value="<?php echo htmlspecialchars($user['user_email']); ?>" readonly>
						</div>
						<div class="checkout-input-box">
							<span class="required">Address :</span>
							<input type="text" name="address" id="address" value="" required>
						</div>
						<div class="checkout-input-box">
							<span class="required">City :</span>
							<input type="text" name="city" id="city" value="" required>
						</div>
						<div class="checkout-flex">
							<div class="checkout-input-box">
							<span class="required">State :</span>
							<select name="state" id="state" required>
    							<option value="" disabled selected>Select a state</option>
    							<option value="Johor">Johor</option>
								<option value="Kelantan">Kelantan</option>
								<option value="Kedah">Kedah</option>
								<option value="Malacca">Malacca</option>
            					<option value="Negeri Sembilan" >Negeri Sembilan</option>
            					<option value="Pahang" >Pahang</option>
            					<option value="Penang" >Penang</option>
            					<option value="Perak" >Perak</option>
            					<option value="Perlis" >Perlis</option>
            					<option value="Selangor">Selangor</option>
            					<option value="Terengganu" >Terengganu</option>
            					<option value="Kuala Lumpur" >Kuala Lumpur</option>
            					<option value="Labuan" >Labuan</option>
            					<option value="Putrajaya" >Putrajaya</option>
            					<option value="Sabah" >Sabah</option>
            					<option value="Sarawak" >Sarawak</option>
							</select>
							</div>
							<div class="checkout-input-box">
    <span class="required">Postcode :</span>
    <input type="text" name="postcode" id="postcode" placeholder="12345" minlength="5" maxlength="5" 
        pattern="\d{5}" title="Please enter exactly 5 digits number" autocomplete="off" required>
</div>
						</div>

						<?php if (!empty($address)): ?>
<div class="autofill-checkbox">
    <input type="checkbox" id="autofill-checkbox" name="autofill-checkbox" onclick="toggleAutofill()">
    <label for="autofill-checkbox">Use saved address information</label>
</div>
<?php endif; ?>
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
							<input type="text" name="cardHolderName" placeholder="Cheong Wei Kit" autocomplete="off"
								required>
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
							<input type="text" name="user_message" placeholder="leave a message (optional)">
						</div>

						<div class="checkout-flex">
							<div class="checkout-input-box">
								<span>Valid Thru (MM/YY) :</span>
								<input type="text" name="expiry-date" id="expiry-date" placeholder="MM/YY" required minlength="5" maxlength="5" pattern="(0[1-9]|1[0-2])\/\d{2}" title="Please enter a valid MM/YY format" autocomplete="off" oninput="formatExpiryDate(this)">
								<small id="expiry-error" style="color: red; display: none;">Please enter a valid,
									non-expired date.</small>
							</div>
							<div class="checkout-input-box">
								<span>CVV :</span>
								<input type="number" name="cvv" id="cvv" placeholder="123" maxlength="3"
									oninput="validateCVV()" required>
								<small id="cvv-error" style="color: red; display: none;">Please enter a 3-digit CVV
									code.</small>

							</div>
						</div>
					</div>

					<!-- Order Summary Section -->
					<div class="checkout-column checkout-order-summary">
						<h3 class="checkout-title">Your Order</h3>
						<!-- Product List -->
						<div id="product-container">

						<?php

						$grand_total = 0;
						$products = [];

						while ($row = mysqli_fetch_assoc($cart_result)):
							$product_name = $row['product_name'];
							$product_price = $row['product_price'];
							$product_image = $row['product_image'];
							$color=$row['color'];
							$total_qty = $row['total_qty'];
							$item_total_price = $row['item_total_price'];

							$products[] = $row;

							// Accumulate the grand total
							$grand_total += $item_total_price;

							?>

							<div class="checkout-order-item">
								<img src="images/<?php echo htmlspecialchars($product_image); ?>"
									alt="<?php echo htmlspecialchars($product_name); ?>">
								<div>
								<p><?php echo htmlspecialchars($product_name); ?> (<?php echo htmlspecialchars($color); ?>)</p>
									<span>Price: RM<?php echo number_format($product_price, 2); ?></span><br>
									<span>Quantity: <?php echo $total_qty; ?></span><br>
									<span>Subtotal: RM<?php echo number_format($item_total_price, 2); ?></span>
								</div>
							</div>
						<?php endwhile; ?>
						<script>
            // 模拟 PHP 数据为 JS 数组
            const products = <?php echo json_encode($products); ?>;
            const productsPerPage = 3;
            let currentPage = 1;

            // 渲染产品函数
            function renderProducts(page) {
                const container = document.getElementById('product-container');
                container.innerHTML = ''; // 清空之前的内容

                // 计算当前页的起始和结束索引
                const startIndex = (page - 1) * productsPerPage;
                const endIndex = Math.min(startIndex + productsPerPage, products.length);

                // 生成当前页的产品 HTML
                for (let i = startIndex; i < endIndex; i++) {
                    const product = products[i];
                    container.innerHTML += `
                        <div class="checkout-order-item">
                            <img src="images/${product.product_image}" alt="${product.product_name}">
                            <div>
                                <p>${product.product_name} (${product.color})</p>
                                <span>Price: RM${product.product_price}</span><br>
                                <span>Quantity: ${product.total_qty}</span><br>
                                <span>Subtotal: RM${product.item_total_price}</span>
                            </div>
                        </div>
                    `;
                }

                // 显示分页按钮
                renderPagination();
            }

            // 渲染分页按钮函数
            function renderPagination() {
                const paginationContainer = document.getElementById('pagination-container');
                paginationContainer.innerHTML = '';

                if (products.length > productsPerPage) {
                    if (currentPage > 1) {
                        paginationContainer.innerHTML += `<button onclick="changePage(currentPage - 1)">Previous Page</button>`;
                    }
                    if (currentPage < Math.ceil(products.length / productsPerPage)) {
                        paginationContainer.innerHTML += `<button onclick="changePage(currentPage + 1)">Next Page</button>`;
                    }
                }
            }

            // 切换页面函数
            function changePage(page) {
                currentPage = page;
                renderProducts(currentPage);
            }

            // 初始化渲染
            renderProducts(currentPage);
        </script>
    </div>

    <!-- 分页按钮容器 -->
    <div id="pagination-container" style="margin-top: 20px; text-align: center;"></div>

						<!-- Order Totals -->
						<div class="checkout-order-totals">
							<?php
							// Assuming $discount is calculated elsewhere or based on some logic
							$total_payment = $grand_total - $discount_amount ;
							?>
							<p>Grand total: <span>RM<?php echo number_format($grand_total, 2); ?></span></p>
							<p>Discount: <span>-RM<?php echo number_format($discount_amount, 2); ?></span></p>
							<p class="checkout-total">Total Payment:
								<span>RM<?php echo number_format($total_payment, 2); ?></span>
							</p>
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
	<?php
if ($paymentSuccess) {
    // 计算 Grand Total 和 Final Amount
    $grand_total = 0;
    foreach ($cart_result as $item) {
        $grand_total += $item['item_total_price']; // 累加每个商品的总价
    }

    // 计算 Final Amount
    $final_amount = $grand_total - $discount_amount; // 扣除折扣后的最终支付金额

    // 确认变量值
    if ($grand_total <= 0 || $final_amount <= 0) {
        die("Error: Invalid grand total or final amount!");
    }

   // 判断用户是否使用了自动填充的地址
$use_autofill = isset($_POST['autofill-checkbox']) && $_POST['autofill-checkbox'] === 'on';

if ($use_autofill && $address) {
    // 如果勾选了自动填充，使用保存的地址
    $shipping_address = $address['address'] . ', ' . $address['postcode'] . ', ' . $address['city'] . ', ' . $address['state'];
} else {
    // 否则使用用户手动输入的地址
    $shipping_address = $_POST['address'] . ', ' . $_POST['postcode'] . ', ' . $_POST['city'] . ', ' . $_POST['state'];
}
    $user_message = isset($_POST['user_message']) ? $_POST['user_message'] : ''; // 用户留言

    // 插入 `orders` 表
    $order_query = "INSERT INTO orders (user_id, order_date, Grand_total, discount_amount, final_amount, shipping_address, user_message) VALUES (?, NOW(), ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("idddss", $user_id, $grand_total, $discount_amount, $final_amount, $shipping_address, $user_message);
    if (!$stmt->execute()) {
        die("Error inserting into orders table: " . $stmt->error);
    }

    // 获取插入订单的ID
    $order_id = $stmt->insert_id;

    // 插入 `order_details` 表
    foreach ($cart_result as $item) {
        $variant_id = $item['variant_id']; // 从购物车中获取 variant_id
        $quantity = $item['total_qty']; // 商品数量
        $unit_price = $item['product_price']; // 单价
        $total_price = $item['item_total_price']; // 总价

        $detail_query = "INSERT INTO order_details (order_id, variant_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)";
        $detail_stmt = $conn->prepare($detail_query);
        $detail_stmt->bind_param("iiidd", $order_id, $variant_id, $quantity, $unit_price, $total_price);
        if (!$detail_stmt->execute()) {
            die("Error inserting into order_details table: " . $detail_stmt->error);
        }
    }

    // 清空购物车
    $clear_cart_query = "DELETE FROM shopping_cart WHERE user_id = ?";
    $clear_cart_stmt = $conn->prepare($clear_cart_query);
    $clear_cart_stmt->bind_param("i", $user_id);
    if (!$clear_cart_stmt->execute()) {
        die("Error clearing shopping cart: " . $clear_cart_stmt->error);
    }

    // 插入 `payment` 表
    $payment_query = "INSERT INTO payment (user_id, order_id, payment_amount, payment_status) VALUES (?, ?, ?, ?)";
    $payment_status = 'Completed'; 
    $payment_stmt = $conn->prepare($payment_query);
    $payment_stmt->bind_param("iids", $user_id, $order_id, $final_amount, $payment_status);
    if (!$payment_stmt->execute()) {
        die("Error inserting into payment table: " . $payment_stmt->error);
    }

    // 确认订单成功
    echo "Order placed successfully!";
}
?>

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
        postcode.value = "<?php echo htmlspecialchars($address['postcode'] ?? ''); ?>";

        // Set the correct state in the dropdown
        const savedState = "<?php echo htmlspecialchars($address['state'] ?? ''); ?>";
        if (savedState) {
            const options = Array.from(state.options);
            const matchingOption = options.find(option => option.value === savedState);
            if (matchingOption) {
                state.value = savedState;
            }
        }
		 // Set fields to readonly
		address.readOnly = true;
        city.readOnly = true;
        postcode.readOnly = true;
        state.disabled = true;
    } else {
        // Clear fields for manual input if checkbox is unchecked
        address.value = "";
        city.value = "";
        state.value = "";
        postcode.value = "";

		 // Remove readonly attribute
		 address.readOnly = false;
        city.readOnly = false;
        postcode.readOnly = false;
        state.disabled = false;
    }
}



		function formatExpiryDate(input) {
    let value = input.value.replace(/\D/g, ""); // 移除非数字字符

    // 插入 '/' 在两位数字之后
    if (value.length > 2) {
        value = value.slice(0, 2) + '/' + value.slice(2, 4);
    }

    // 限制输入长度为5个字符（MM/YY）
    input.value = value.slice(0, 5);
}

document.getElementById('expiry-date').addEventListener('input', function () {
    const input = this.value;
    const error = document.getElementById('expiry-error');

    // 检查输入是否匹配 MM/YY 格式
    const datePattern = /^(0[1-9]|1[0-2])\/\d{2}$/;
    if (!datePattern.test(input)) {
        error.style.display = 'none';
        return;
    }

    // 解析输入的月份和年份
    const [month, year] = input.split('/').map(Number);
    const currentYear = new Date().getFullYear() % 100; // 取当前年份的后两位数字
    const currentMonth = new Date().getMonth() + 1; // 月份是从0开始的

    // 检查输入的日期是否有效且未过期
    if (year > currentYear || (year === currentYear && month >= currentMonth)) {
        error.style.display = 'none'; // 如果有效则隐藏错误信息
    } else {
        error.style.display = 'block'; // 显示错误信息
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

		function validateForm() {
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
				return false;
			}

			const cardNumberPattern = /^\d{4}\s\d{4}\s\d{4}\s\d{4}$/;
			if (!cardNumberPattern.test(cardNum.value)) {
				alert('Please enter a valid 16-digit card number (format: 1111 2222 3333 4444).');
				return false;
			}

			if (cvv.value.length !== 3) {
				alert('Please enter a 3-digit CVV code.');
				return false;
			}

			const datePattern = /^(0[1-9]|1[0-2])\/\d{2}$/;
			if (!datePattern.test(expiryDate.value)) {
        alert('Please enter a valid expiration date (format: MM/YY).');
        return false;
    } else {
        const [month, year] = expiryDate.value.split('/').map(Number);
        const currentYear = new Date().getFullYear() % 100;
        const currentMonth = new Date().getMonth() + 1;
        if (year < currentYear || (year === currentYear && month < currentMonth)) {
            alert('Please enter a valid, non-expired expiration date.');
            return false;
        }
    }

			return true;
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
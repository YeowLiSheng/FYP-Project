<?php
session_start(); // Start the session

// Include the database connection file
include("dataconnection.php"); 

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

// Check if the database connection exists
if (!isset($connect) || !$connect) { // Changed $connect to $conn
    die("Database connection failed.");
}

// Retrieve the user information
$user_id = $_SESSION['id'];
$result = mysqli_query($connect, "SELECT * FROM user WHERE user_id ='$user_id'"); // Changed $connect to $conn

// Check if the query was successful and fetch user data
if ($result && mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_assoc($result);
} else {
    echo "User not found.";
    exit;
}
// Check if the user is updating the cart
// Update product quantities and handle promotions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (isset($_POST['product_qty']) && isset($_POST['variant_id'])) {
        foreach ($_POST['product_qty'] as $index => $new_qty) {
            $new_qty = intval($new_qty);
            $variant_id = intval($_POST['variant_id'][$index]);

            // Fetch current details including promotion
            $current_query = "
                SELECT sc.qty, 
                       sc.total_price, 
                       pv.promotion_id, 
                       COALESCE(pm.promotion_price, p.product_price) AS applicable_price
                FROM shopping_cart sc
                LEFT JOIN product_variant pv ON sc.variant_id = pv.variant_id
                LEFT JOIN product p ON pv.product_id = p.product_id
                LEFT JOIN promotion_product pm ON pv.promotion_id = pm.promotion_id
                WHERE sc.user_id = $user_id AND sc.variant_id = $variant_id";
            $current_result = $connect->query($current_query);

            if ($current_result && $current_result->num_rows > 0) {
                $current_row = $current_result->fetch_assoc();
                $applicable_price = floatval($current_row['applicable_price']);

                // Calculate new total price
                $new_total_price = $new_qty * $applicable_price;

                if ($new_qty > 0) {
                    // Update cart item
                    $update_query = "
                        UPDATE shopping_cart 
                        SET qty = $new_qty, 
                            total_price = $new_total_price 
                        WHERE user_id = $user_id AND variant_id = $variant_id";
                    $connect->query($update_query);
                } else {
                    // Remove item if quantity is zero
                    $delete_query = "
                        DELETE FROM shopping_cart 
                        WHERE user_id = $user_id AND variant_id = $variant_id";
                    $connect->query($delete_query);
                }
            }
        }
    }

    recalculateFinalTotalAndVoucher($connect, $user_id);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Recalculate total prices and apply vouchers considering promotions
function recalculateFinalTotalAndVoucher($connect, $user_id) {
    $recalc_query = "
        SELECT 
            sc.variant_id,
            sc.qty,
            pv.promotion_id,
            COALESCE(pm.promotion_price, p.product_price) AS applicable_price,
            sc.qty * COALESCE(pm.promotion_price, p.product_price) AS product_total_price
        FROM shopping_cart sc
        LEFT JOIN product_variant pv ON sc.variant_id = pv.variant_id
        LEFT JOIN product p ON pv.product_id = p.product_id
        LEFT JOIN promotion_product pm ON pv.promotion_id = pm.promotion_id
        WHERE sc.user_id = $user_id";
    $recalc_result = $connect->query($recalc_query);

    $total_price = 0;

    if ($recalc_result) {
        while ($row = $recalc_result->fetch_assoc()) {
            $variant_id = intval($row['variant_id']);
            $product_total_price = floatval($row['product_total_price']);
            $total_price += $product_total_price;

            // Update individual cart items
            $update_query = "
                UPDATE shopping_cart
                SET total_price = $product_total_price
                WHERE user_id = $user_id AND variant_id = $variant_id";
            $connect->query($update_query);
        }
    }

    // Handle voucher logic as before
    $voucher_query = "
        SELECT v.discount_rate, v.minimum_amount, vu.voucher_id 
        FROM voucher_usage vu
        JOIN voucher v ON vu.voucher_id = v.voucher_id
        WHERE vu.user_id = $user_id AND vu.usage_num > 0";
    $voucher_result = $connect->query($voucher_query);

    if ($voucher_result && $voucher = $voucher_result->fetch_assoc()) {
        $discount_rate = $voucher['discount_rate'];
        $minimum_amount = $voucher['minimum_amount'];
        $voucher_id = $voucher['voucher_id'];

        if ($total_price >= $minimum_amount) {
            $discount_amount = $total_price * ($discount_rate / 100);
            $final_total_price = $total_price - $discount_amount;

            $update_cart_query = "
                UPDATE shopping_cart 
                SET final_total_price = $final_total_price, 
                    discount_amount = $discount_amount, 
                    voucher_applied = $voucher_id
                WHERE user_id = $user_id";
            $connect->query($update_cart_query);
        } else {
            $connect->query("
                UPDATE shopping_cart 
                SET final_total_price = total_price, 
                    discount_amount = 0, 
                    voucher_applied = 0 
                WHERE user_id = $user_id");
        }
    } else {
        $connect->query("
            UPDATE shopping_cart 
            SET final_total_price = total_price, 
                discount_amount = 0, 
                voucher_applied = 0 
            WHERE user_id = $user_id");
    }
}






// Initialize total_price before fetching cart items
$total_price = 0;

// Fetch and combine cart items for the logged-in user where the product_id is the sam
// Fetch and combine cart items with stock information
$cart_items_query = "
    SELECT 
        sc.variant_id,
		pv.product_id,
        pv.promotion_id, 
        pv.color, 
        pv.size, 
        p.product_name, 
        p.product_price,
		p.product_status,
        pm.promotion_name,
        pm.promotion_price,
        pm.promotion_status,
		pv.stock AS product_stock,
        SUM(sc.qty) AS total_qty, 
        SUM(sc.total_price) AS total_price,
		MAX(sc.final_total_price) AS final_total_price, 
		MAX(sc.voucher_applied) AS voucher_applied
    FROM shopping_cart sc
    LEFT JOIN product_variant pv ON sc.variant_id = pv.variant_id
	LEFT JOIN product p ON pv.product_id = p.product_id
    LEFT JOIN promotion_product pm ON pv.promotion_id = pm.promotion_id
    WHERE sc.user_id = $user_id
    GROUP BY 
        sc.variant_id";
$cart_items_result = $connect->query($cart_items_query);

$checkout_locked = false; // Flag to disable checkout button
$error_messages = []; // Array to store error messages for each product
$cart_items = [];

if ($cart_items_result && $cart_items_result->num_rows > 0) {
    while ($cart_item = $cart_items_result->fetch_assoc()) {
        $cart_items[] = $cart_item;
        $total_price += $cart_item['total_price'];

            // Check product status for unavailability
            if ($cart_item['product_status'] == 2 || $cart_item['product_stock'] <= 0) {
                $cart_item['unavailable'] = true;
                $checkout_locked = true;
            }else if($cart_item['promotion_status'] == 2 || $cart_item['total_qty'] > $cart_item['product_stock']) {
                $cart_item['unavailable'] = true;
                $checkout_locked = true;
            }
    }
}

// Apply discount after verifying voucher code, if applicable
$discount_amount = 0; // Initialize discount amount
$error_message = ""; // Initialize error message
$final_total_price = $total_price;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_voucher']) && !empty($_POST['coupon'])) {
    $voucher_code = mysqli_real_escape_string($connect, $_POST['coupon']);
    $voucher_query = "
        SELECT discount_rate, voucher_status, minimum_amount, usage_limit, voucher_id 
        FROM voucher 
        WHERE voucher_code = '$voucher_code' AND voucher_status = 'active' 
        LIMIT 1";
    $voucher_result = $connect->query($voucher_query);

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
		$usage_result = $connect->query($usage_query);
	
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
	
				// Update shopping_cart with the final total, discount_amount, and voucher_applied
				$update_final_total_query = "
					UPDATE shopping_cart 
					SET final_total_price = $final_total_price, 
                        discount_amount = $discount_amount, 
                        voucher_applied = $voucher_id 
					WHERE user_id = $user_id";
				$connect->query($update_final_total_query);
	
				// Update or insert the voucher usage record
				if ($current_usage > 0) {
					$connect->query("
						UPDATE voucher_usage 
						SET usage_num = usage_num + 1 
						WHERE user_id = $user_id AND voucher_id = $voucher_id
					");
				} else {
					$connect->query("
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
}

// Retrieve final_total_price from database if voucher was previously applied
$cart_total_query = "
    SELECT MAX(final_total_price) AS final_total_price, MAX(voucher_applied) AS voucher_applied 
    FROM shopping_cart 
    WHERE user_id = $user_id";
$cart_total_result = $connect->query($cart_total_query);
if ($cart_total_result && $cart_total_row = $cart_total_result->fetch_assoc()) {
    if ($cart_total_row['voucher_applied']) {
        $final_total_price = $cart_total_row['final_total_price']; // Use stored final total if voucher applied
    }
}

$_SESSION['discount_amount'] = $discount_amount;

// Handle voucher removal
if (isset($_POST['remove_voucher'])) {
    $voucher_id = $_POST['voucher_id'];

    // Reset voucher values in shopping_cart
    $connect->query("
        UPDATE shopping_cart 
        SET voucher_applied = 0, 
            discount_amount = 0, 
            final_total_price = total_price 
        WHERE user_id = $user_id");

    // Decrement usage in voucher_usage
    $connect->query("UPDATE voucher_usage SET usage_num = usage_num - 1 WHERE user_id = $user_id AND voucher_id = $voucher_id");

    // Check if usage_num is 0, then delete the record
    $usage_check_query = "SELECT usage_num FROM voucher_usage WHERE user_id = $user_id AND voucher_id = $voucher_id";
    $usage_check_result = $connect->query($usage_check_query);
    
    if ($usage_check_result && $usage_row = $usage_check_result->fetch_assoc()) {
        if ($usage_row['usage_num'] <= 0) {
            $connect->query("DELETE FROM voucher_usage WHERE user_id = $user_id AND voucher_id = $voucher_id");
        }
    }

    // Reload page to reflect changes
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Display applied voucher
$voucher_applied_query = "
    SELECT v.voucher_code, sc.discount_amount, v.voucher_id 
    FROM shopping_cart sc
    JOIN voucher v ON v.voucher_id = sc.voucher_applied
    WHERE sc.user_id = $user_id AND sc.voucher_applied IS NOT NULL";
$voucher_applied_result = $connect->query($voucher_applied_query);
$applied_voucher = $voucher_applied_result ? $voucher_applied_result->fetch_assoc() : null;

// Count distinct product IDs in the shopping cart for the logged-in user
$distinct_products_query = "SELECT COUNT(DISTINCT variant_id) AS distinct_count FROM shopping_cart WHERE user_id = $user_id";
$distinct_products_result = $connect->query($distinct_products_query);
$distinct_count = 0;

if ($distinct_products_result) {
    $row = $distinct_products_result->fetch_assoc();
    $distinct_count = $row['distinct_count'] ?? 0;
}
$query = "SELECT * FROM product_variant";
$result = mysqli_query($connect, $query);
$product_variants = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
<style>
/* Chat box styling */
.chat-box {
    position: relative;
    max-width: 250px;
    padding: 15px;
    border-radius: 12px;
    background-color: #f1f1f1;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    font-family: Arial, sans-serif;
    margin-left: 20px;
    transition: transform 0.3s ease;
}

/* Speech bubble effect */
.chat-box::after {
    content: "";
    position: absolute;
    bottom: -10px;
    left: 15px;
    width: 0;
    height: 0;
    border-width: 10px;
    border-style: solid;
    border-color: #f1f1f1 transparent transparent transparent;
}

/* Text inside the chat box */
.chat-box p {
    margin: 5px 0;
    font-size: 14px;
    color: #333;
}

/* Remove (close) button styling */
.chat-box .remove-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background-color: transparent;
    border: none;
    font-size: 16px;
    color: #888;
    cursor: pointer;
    transition: color 0.3s ease;
}

.chat-box .remove-btn:hover {
    color: #ff4d4d;
}

/* Button styling specific to chat box */
.chat-box .apply-voucher-btn {
    padding: 8px 16px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    margin-top: 10px;
    transition: background-color 0.3s ease;
}

.chat-box .apply-voucher-btn:hover {
    background-color: #0056b3;
}

/* Error message styling specific to chat box */
.chat-box .text-danger {
    color: #ff4d4d;
    font-size: 14px;
    margin-top: 10px;
}
</style>
</head>
<body class="animsition">
	
	<!-- Header -->
	<header class="header-v4">
		<!-- Header desktop -->
		<div class="container-menu-desktop">
			<!-- Topbar -->
			<div class="top-bar">
				<div class="content-topbar flex-sb-m h-full container">
					<div class="left-top-bar" style="white-space: nowrap; overflow: hidden; display: block; flex: 1; max-width: calc(100% - 300px);">
						<span style="display: inline-block; animation: marquee 20s linear infinite;">
							Free shipping for standard order over $10000 <span style="padding-left: 300px;"></span> 
							New user will get 10% discount!!!<span style="padding-left: 300px;"></span>
							Get 5% discount for any purchasement above $5000 (code: DIS4FIVE)
							<span style="padding-left: 300px;"></span> Free shipping for standard order over $10000 
							<span style="padding-left: 300px;"></span> New user will get 10% discount!!! 
							<span style="padding-left: 300px;"></span> Get 5% discount for any purchasement above $5000 (code: DIS4FIVE)
						</span>
						<style>
							@keyframes marquee {
								0% {
									transform: translateX(0);
								}
								100% {
									transform: translateX(-55%);
								}
							}
						</style>
					</div>

					<div class="right-top-bar flex-w h-full">
				
						<a href="#" class="flex-c-m trans-04 p-lr-25">
							EN
						</a>

						<a href="#" class="flex-c-m trans-04 p-lr-25">
							USD
						</a>




                        <a href="Order.php?user=<?php echo $user_id; ?>" class="flex-c-m trans-04 p-lr-25">
                            <?php
								echo "HI '" . htmlspecialchars($user_data['user_name']);
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
					<a href="dashboard.php" class="logo">
						<img src="images/YLS2.jpg" alt="IMG-LOGO">
					</a>

					<!-- Menu desktop -->
					<div class="menu-desktop">
						<ul class="main-menu">
							<li>
								<a href="dashboard.php">Home</a>
							</li>

							<li class="active-menu">
								<a href="product.php">Shop</a>
							</li>

                            <li>
								<a href="promotion.php">Promotion</a>
							</li>

							<li class="label1" data-label1="hot">
								<a href="voucher_page.php">Voucher</a>
							</li>

							<li>
								<a href="blog.php">Blog</a>
							</li>

							<li>
								<a href="about.php">About</a>
							</li>

							<li>
								<a href="contact.php">Contact</a>
							</li>
						</ul>
					</div>	

					<!-- Icon header -->
					<div class="wrap-icon-header flex-w flex-r-m">

						<div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti js-show-cart" data-notify="<?php echo $distinct_count; ?>">
							<i class="zmdi zmdi-shopping-cart"></i>
						</div>

					</div>
				</nav>
			</div>	
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
                $total_price = 0;
				$cart_items_result = $connect->query($cart_items_query);
                if ($cart_items_result->num_rows > 0) {
                    while ($cart_item = $cart_items_result->fetch_assoc()) {
                        $total_price += $cart_item['total_price'];
                        $quick_view_image = '';
                        
                        // Find the appropriate image based on the product or promotion
                        foreach ($product_variants as $variant) {
                            // Check if the item is a promotion
                            if (!empty($cart_item['promotion_id'])) {
                                if ($variant['promotion_id'] == $cart_item['promotion_id'] && $variant['color'] == $cart_item['color']) {
                                    $quick_view_image = $variant['Quick_View1'];
                                    break;
                                }
                            } else {
                                // Check if the item is a regular product
                                if ($variant['product_id'] == $cart_item['product_id'] && $variant['color'] == $cart_item['color']) {
                                    $quick_view_image = $variant['Quick_View1'];
                                    break;
                                }
                            }
                        }                        

                        // Check if the item is a promotion
                        if (!empty($cart_item['promotion_id'])) {
                            // Render promotion details
                            echo '
                            <li class="header-cart-item flex-w flex-t m-b-12">
                                <div class="header-cart-item-img">
                                    <img src="images/' . $quick_view_image . '" alt="IMG">
                                </div>
                                <div class="header-cart-item-txt p-t-8">
                                    <a href="promotion-detail.php?id=' . $cart_item['promotion_id'] . '" class="header-cart-item-name m-b-18 hov-cl1 trans-04">
                                        ' . $cart_item['promotion_name'] . '
                                    </a>
                                    <span class="header-cart-item-info">
                                        ' . $cart_item['total_qty'] . ' x $' . number_format($cart_item['promotion_price'], 2) . '
                                    </span>
                                    <span class="header-cart-item-info">
                                        Color: ' . $cart_item['color'] . ' | Size: ' . $cart_item['size'] . '
                                    </span>
                                </div>
                            </li>';
                        } else {
                            // Render product details
                            echo '
                            <li class="header-cart-item flex-w flex-t m-b-12">
                                <div class="header-cart-item-img">
                                    <img src="images/' . $quick_view_image . '" alt="IMG">
                                </div>
                                <div class="header-cart-item-txt p-t-8">
                                    <a href="product-detail.php?id=' . $cart_item['product_id'] . '" class="header-cart-item-name m-b-18 hov-cl1 trans-04">
                                        ' . $cart_item['product_name'] . '
                                    </a>
                                    <span class="header-cart-item-info">
                                        ' . $cart_item['total_qty'] . ' x $' . number_format($cart_item['product_price'], 2) . '
                                    </span>
                                    <span class="header-cart-item-info">
                                        Color: ' . $cart_item['color'] . ' | Size: ' . $cart_item['size'] . '
                                    </span>
                                </div>
                            </li>';
                        }
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
                    <a href="shoping-cart.php" class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-r-8 m-b-10">
                        View Cart
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
					
						if (!empty($cart_items)) {
							foreach ($cart_items as $cart_item) {
								// Assume $product_variants contains data from the product_variant table.
								$product_color = htmlspecialchars($cart_item['color']); // Ensure color is safe for use
								$quick_view_image = ''; // Default to empty

								// Fetch the Quick_View1 image for the specific color
								foreach ($product_variants as $variant) {
									// Check if the item is a promotion
									if (!empty($cart_item['promotion_id'])) {
										if ($variant['promotion_id'] == $cart_item['promotion_id'] && $variant['color'] == $cart_item['color']) {
											$quick_view_image = $variant['Quick_View1'];
											break;
										}
									} else {
										// Check if the item is a regular product
										if ($variant['product_id'] == $cart_item['product_id'] && $variant['color'] == $cart_item['color']) {
											$quick_view_image = $variant['Quick_View1'];
											break;
										}
									}
								}
									$message = '';
									if ($cart_item['product_status']==2 || $cart_item['promotion_status']==2 ) {
										$message = '<p class="text-danger">This product is currently unavailable</p>';
									} elseif ($cart_item['total_qty'] > $cart_item['product_stock']) {
										$message = '<p class="text-danger">Stock exceeded! Max: ' . $cart_item['product_stock'] . '</p>';
									}
									if (!empty($cart_item['promotion_id'])) {
										echo '
										<tr class="table_row">
											<td class="column-1">
												<div class="how-itemcart1">
													<img src="images/' . $quick_view_image . '" alt="IMG">
												</div>
											</td>
											<td class="column-2">' . $cart_item['promotion_name'] . ' <br> Color: ' . $cart_item['color'] . ' | Size: ' . $cart_item['size'] . '</td>
											<td class="column-3">$' . number_format($cart_item['promotion_price'], 2) . '</td>
											<td class="column-4">
												<div class="wrap-num-product flex-w m-l-auto m-r-0">
													<div class="btn-num-product-down cl8 hov-btn3 trans-04 flex-c-m" data-stock="' . $cart_item['product_stock'] . '" data-product-id="' . $cart_item['promotion_id'] . '">
														<i class="fs-16 zmdi zmdi-minus"></i>
													</div>
													<input type="hidden" name="variant_id[]" value="' . $cart_item['variant_id'] . '">
													<input class="mtext-104 cl3 txt-center num-product" type="number" name="product_qty[]" value="' . $cart_item['total_qty'] . '" readonly>
													<div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m" data-stock="' . $cart_item['product_stock'] . '" data-variant-id="' . $cart_item['variant_id'] . '">
														<i class="fs-16 zmdi zmdi-plus"></i>
													</div>
													
												</div>
												' . $message . '
											</td>
											<td class="column-5">$' . number_format($cart_item['total_price'], 2) . '</td>
										</tr>';
									} else {
										echo '
										<tr class="table_row">
											<td class="column-1">
												<div class="how-itemcart1">
													<img src="images/' . $quick_view_image . '" alt="IMG">
												</div>
											</td>
											<td class="column-2">' . $cart_item['product_name'] . ' <br> Color: ' . $cart_item['color'] . ' | Size: ' . $cart_item['size'] . '</td>
											<td class="column-3">$' . number_format($cart_item['product_price'], 2) . '</td>
											<td class="column-4">
												<div class="wrap-num-product flex-w m-l-auto m-r-0">
													<div class="btn-num-product-down cl8 hov-btn3 trans-04 flex-c-m" data-stock="' . $cart_item['product_stock'] . '" data-product-id="' . $cart_item['product_id'] . '">
														<i class="fs-16 zmdi zmdi-minus"></i>
													</div>
													<input type="hidden" name="variant_id[]" value="' . $cart_item['variant_id'] . '">
													<input class="mtext-104 cl3 txt-center num-product" type="number" name="product_qty[]" value="' . $cart_item['total_qty'] . '" readonly>
													<div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m" data-stock="' . $cart_item['product_stock'] . '" data-variant-id="' . $cart_item['variant_id'] . '">
														<i class="fs-16 zmdi zmdi-plus"></i>
													</div>
													
												</div>
												' . $message . '
											</td>
											<td class="column-5">$' . number_format($cart_item['total_price'], 2) . '</td>
										</tr>';
									}
							}
						} else {
							echo '<tr><td colspan="5">&emsp;&emsp;&emsp;&emsp;&emsp;Your cart is empty.</td></tr>';
						}
						?>
					</table>

                    </div>

                    <!-- Apply Coupon and Update Cart Buttons -->
                    <div class="flex-w flex-sb-m bor15 p-t-18 p-b-15 p-lr-40 p-lr-15-sm">
    					<div class="flex-w flex-m m-r-20 m-tb-5">
						<?php if (empty($applied_voucher)): ?>
            				<input class="stext-104 cl2 plh4 size-117 bor13 p-lr-20 m-r-10 m-tb-5" type="text" name="coupon" placeholder="Voucher Code">
            				<button type="submit" name="apply_voucher" class="flex-c-m stext-101 cl2 size-118 bg8 bor13 hov-btn3 p-lr-15 trans-04 pointer m-tb-5">
                				Apply Voucher
            				</button>
        					<?php else: ?>
            					<p class="stext-104 cl2 plh4 size-117 p-lr-20 m-r-10 m-tb-5">Voucher applied: <?php echo $applied_voucher['voucher_code']; ?></p>
        					<?php endif; ?>
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
            					$<?php echo number_format($final_total_price, 2); ?>
        					</span>
							<?php if (!empty($applied_voucher)): ?>
       							<div class="chat-box">
           	 						<form method="POST" action="">
                						<p>Total Discount: $<?php echo number_format($applied_voucher['discount_amount'], 2); ?></p>
                						<input type="hidden" name="voucher_id" value="<?php echo $applied_voucher['voucher_id']; ?>">
                						<button type="submit" name="remove_voucher" class="remove-btn">✖</button>
           							</form>
        						</div>
    						<?php endif; ?>
    					</div>
						 <!-- Hidden field to pass discount amount to checkout.php -->
    					<input type="hidden" name="discount_amount" value="<?php echo $discount_amount; ?>">
    
						<button type="submit" formaction="checkout.php?discount_amount=<?php echo $discount_amount; ?>" class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-r-8 m-b-10" <?php echo $checkout_locked ? 'disabled' : ''; ?>>
							Check Out
						</button>
						<?php if ($checkout_locked): ?>
							<p class="text-danger">You cannot proceed to checkout. Please adjust the items in your cart.</p>
						<?php endif; ?>
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
							<a href="product.php?category_id=1" class="stext-107 cl7 hov-cl1 trans-04">
								Women's Bag
							</a>
						</li>

						<li class="p-b-10">
							<a href="product.php?category_id=2" class="stext-107 cl7 hov-cl1 trans-04">
								Men's Bag
							</a>
						</li>

						<li class="p-b-10">
							<a href="product.php?category_id=3" class="stext-107 cl7 hov-cl1 trans-04">
								Accessories
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
							<a href="Order.php?user=<?php echo $user_id; ?>" class="stext-107 cl7 hov-cl1 trans-04">
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
							<a href="faq.php" class="stext-107 cl7 hov-cl1 trans-04">
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
    // Decrease quantity
    $('.btn-num-product-down').off('click').click(function() {
        let input = $(this).siblings('.num-product');
        let currentValue = parseInt(input.val());
        if (currentValue > 0) {
            input.val(currentValue - 1);
        } else {
            input.val(0); // Prevent quantity from dropping below 1
        }
    });

    // Increase quantity
    $('.btn-num-product-up').off('click').click(function() {
        let input = $(this).siblings('.num-product');
        let currentValue = parseInt(input.val());
        let maxStock = parseInt($(this).data('stock'));

        if (currentValue < maxStock) {
            input.val(currentValue + 1);
        } else {
            // Display error message for exceeding stock
            if (!$(this).next('.stock-error').length) {
                $(this).after('<p class="stock-error text-danger">Product already reached the maximum quantity!</p>');
            }
        }
    });

    // Clear error messages on quantity change
    $('.num-product').on('input', function() {
        $(this).siblings('.stock-error').remove();
    });
});

</script>
	<script src="js/main.js"></script>

</body>
</html>
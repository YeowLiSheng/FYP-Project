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
if (!isset($connect) || !$connect) {
    die("Database connection failed.");
}

// Retrieve the user information
$user_id = $_SESSION['id'];
$result = mysqli_query($connect, "SELECT * FROM user WHERE user_id ='$user_id'");

// Check if the query was successful and fetch user data
if ($result && mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_assoc($result);
} else {
    echo "User not found.";
    exit;
}


// Fetch and combine cart items for the logged-in user where the product_id is the same
$cart_items_query = "
    SELECT 
        sc.product_id, 
        p.product_name, 
        p.product_image, 
        p.product_price,
        sc.color, 
        sc.size, 
        SUM(sc.qty) AS total_qty, 
        SUM(sc.total_price) AS total_price,
        sc.package_id,
        sc.product1_color, sc.product1_size,
        sc.product2_color, sc.product2_size,
        sc.product3_color, sc.product3_size,
        pkg.package_name, 
        pkg.package_image
    FROM shopping_cart sc
    LEFT JOIN product p ON sc.product_id = p.product_id
    LEFT JOIN product_package pkg ON sc.package_id = pkg.package_id
    WHERE sc.user_id = $user_id
    GROUP BY 
        sc.product_id, 
        sc.color, 
        sc.size, 
        sc.package_id,
        sc.product1_color, sc.product1_size,
        sc.product2_color, sc.product2_size,
        sc.product3_color, sc.product3_size";
$cart_items_result = $connect->query($cart_items_query);

// Handle AJAX request to delete item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $id = intval($_POST['id']); // Ensure ID is an integer
    $type = $_POST['type'];     // Either 'product' or 'package'

    $response = ['success' => false];

    // Debug: Log incoming POST data
    file_put_contents('debug.log', print_r($_POST, true), FILE_APPEND);

    if ($type === 'product') {
        $color = $_POST['color'];
        $size = $_POST['size'];

        // Delete the specific product with matching attributes
        $stmt = $connect->prepare("
            DELETE FROM shopping_cart 
            WHERE product_id = ? AND color = ? AND size = ? AND user_id = ?
        ");
        $stmt->bind_param('issi', $id, $color, $size, $user_id);
    } elseif ($type === 'package') {
        $product1_color = $_POST['product1_color'];
        $product1_size = $_POST['product1_size'];
        $product2_color = $_POST['product2_color'];
        $product2_size = $_POST['product2_size'];
        $product3_color = $_POST['product3_color'];
        $product3_size = $_POST['product3_size'];

        // Delete the specific package with matching attributes
        $stmt = $connect->prepare("
            DELETE FROM shopping_cart 
            WHERE package_id = ? 
              AND product1_color = ? AND product1_size = ?
              AND product2_color = ? AND product2_size = ?
              AND product3_color = ? AND product3_size = ?
              AND user_id = ?
        ");
        $stmt->bind_param(
            'ississsi',
            $id,
            $product1_color, $product1_size,
            $product2_color, $product2_size,
            $product3_color, $product3_size,
            $user_id
        );
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid type']);
        exit;
    }

    // Execute the query and check if it was successful
    if ($stmt->execute()) {
        // Check affected rows to confirm deletion
        if ($stmt->affected_rows > 0) {
            // Recalculate the new total price
            $result = $connect->query("SELECT SUM(total_price) AS new_total FROM shopping_cart WHERE user_id = $user_id");
            $row = $result->fetch_assoc();
            $response['new_total'] = $row['new_total'] ?? 0;
            $response['success'] = true;
        } else {
            $response['message'] = 'No matching row found for deletion.';
        }
    } else {
        // Debug: Log SQL errors
        $response['message'] = 'Query failed: ' . $connect->error;
        file_put_contents('debug.log', "SQL Error: " . $connect->error . "\n", FILE_APPEND);
    }

    $stmt->close();
    echo json_encode($response);
    exit;
}

// Updated query to count distinct items based on product_id, package_id, and associated attributes
$distinct_items_query = "
    SELECT COUNT(*) AS distinct_count
    FROM (
        SELECT 
            sc.product_id, 
            sc.package_id,
            sc.color, 
            sc.size,
            sc.product1_color, sc.product1_size,
            sc.product2_color, sc.product2_size,
            sc.product3_color, sc.product3_size
        FROM shopping_cart sc
        WHERE sc.user_id = $user_id
        GROUP BY 
            sc.product_id, 
            sc.package_id, 
            sc.color, 
            sc.size,
            sc.product1_color, sc.product1_size,
            sc.product2_color, sc.product2_size,
            sc.product3_color, sc.product3_size
    ) AS distinct_items";

$distinct_items_result = $connect->query($distinct_items_query);
$distinct_count = 0;

if ($distinct_items_result) {
    $row = $distinct_items_result->fetch_assoc();
	$distinct_count = $row['distinct_count'] ?? 0;
}

// Handle AJAX request to fetch product details
if (isset($_GET['fetch_product']) && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $query = "SELECT * FROM product WHERE product_id = $product_id";
    $result = $connect->query($query);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        echo json_encode($product);
    } else {
        echo json_encode(null);
    }
    exit; // Stop further script execution
}

// Handle AJAX request to add product to shopping cart
if (isset($_POST['add_to_cart']) && isset($_POST['product_id']) && isset($_POST['qty']) && isset($_POST['total_price'])) {
    $product_id = intval($_POST['product_id']);
    $qty = intval($_POST['qty']);
    $total_price = doubleval($_POST['total_price']);
	$color = $connect->real_escape_string($_POST['color']);
    $size = $connect->real_escape_string($_POST['size']);
    $user_id = $_SESSION['id']; // Get the logged-in user ID

    // Insert data into shopping_cart table, including the user_id
    $cart_query = "INSERT INTO shopping_cart (user_id, product_id, qty, total_price, color, size) 
                   VALUES ($user_id, $product_id, $qty, $total_price, '$color', '$size')";
    if ($connect->query($cart_query) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $connect->error]);
    }
    exit;
}
// Fetch products
$product_query = "SELECT * FROM product ORDER BY product_id DESC LIMIT 4";
$product_result = $connect->query($product_query);

// Get the category_id from the URL
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
?>




<!DOCTYPE html>
<html lang="en">
<head>
	<title>Home</title>
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
	<link rel="stylesheet" type="text/css" href="vendor/daterangepicker/daterangepicker.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/slick/slick.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/MagnificPopup/magnific-popup.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/perfect-scrollbar/perfect-scrollbar.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" type="text/css" href="css/main.css">
<!--===============================================================================================-->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
.swal2-container {
    z-index: 99999 !important; /* Ensure it appears above all other elements */
}
.unavailable-product{
    background-color: lightgrey; /* Soft grey background */
    border: 1px solid #d9d9d9; /* Light border for separation */
    border-radius: 8px; /* Rounded corners */
    padding: 10px;
    transition: all 0.3s ease; /* Smooth hover effect */
    opacity: 0.8; /* Slight transparency */
}
.unavailable-product:hover {
    opacity: 1; /* Bring back full opacity on hover */
}
.unavailable-product .block2-pic img {
    filter: grayscale(30%);
    opacity: 0.7; /* Slightly dim the image */
    transition: all 0.3s ease; /* Smooth transition for hover */
}
.unavailable-product:hover .block2-pic img {
    filter: grayscale(30%); /* Lessen greyscale on hover */
    opacity: 1; /* Full visibility on hover */
}

/* Message Styling */
.unavailable-message {
    color: #d9534f; /* Bright red */
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    margin-top: 5px;
}
</style>

</head>
<body class="animsition">
	
	<!-- Header -->
	<header>
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
					<div class="right-top-bar flex-w h-full" style="z-index: 1;">
						<a href="faq.php" class="flex-c-m trans-04 p-lr-25">
							Help & FAQs
						</a>

						

						<a href="#" class="flex-c-m trans-04 p-lr-25">
							EN
						</a>

						<a href="#" class="flex-c-m trans-04 p-lr-25">
							USD
						</a>




                        <a href="Order.php?user=<?php echo $user_id; ?>" class="flex-c-m trans-04 p-lr-25">
                            <?php
                                echo "HI '" . htmlspecialchars($user_data["user_name"]) ;
                            ?>
                        </a>


                        <a href="log_out.php" class="flex-c-m trans-04 p-lr-25">
							LOG OUT
						</a>



					</div>
				</div>
			</div>

			<div class="wrap-menu-desktop">
				<nav class="limiter-menu-desktop container">
					
					<!-- Logo desktop -->		
					<a href="dashboard.php" class="logo">
						<img src="images/YLS2.jpg" alt="IMG-LOGO">
					</a>

					<!-- Menu desktop -->
					<div class="menu-desktop">
						<ul class="main-menu">
							<li class="active-menu">
								<a href="dashboard.php">Home</a>
								
							</li>

							<li>
								<a href="product.php">Shop</a>
							</li>

							<li class="label1" data-label1="hot">
								<a href="shopping-cart.php">Features</a>
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

						<a href="#" class="dis-block icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti" data-notify="0">
							<i class="zmdi zmdi-favorite-outline"></i>
						</a>
					</div>
				</nav>
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
								
								if (!empty($cart_item['package_id'])) {
									// Render package details
									echo '
									<li class="header-cart-item flex-w flex-t m-b-12">
										<div class="header-cart-item-img delete-item" data-id="' . $cart_item['package_id'] . '" data-type="package"data-product1-color="' . $cart_item['product1_color'] . '" 
										data-product1-size="' . $cart_item['product1_size'] . '" 
										data-product2-color="' . $cart_item['product2_color'] . '" 
										data-product2-size="' . $cart_item['product2_size'] . '" 
										data-product3-color="' . $cart_item['product3_color'] . '" 
										data-product3-size="' . $cart_item['product3_size'] . '">
											<img src="images/' . $cart_item['package_image'] . '" alt="IMG">
										</div>
										<div class="header-cart-item-txt p-t-8">
											<a href="package-detail.php?id=' . $cart_item['package_id'] . '" class="header-cart-item-name m-b-18 hov-cl1 trans-04">
												' . $cart_item['package_name'] . '
											</a>
											<span class="header-cart-item-info">
												' . $cart_item['total_qty'] . ' x $' . number_format($cart_item['total_price'], 2) . '
											</span>
											<span class="header-cart-item-info">
												Product 1: Color ' . $cart_item['product1_color'] . ', Size ' . $cart_item['product1_size'] . '<br>
												Product 2: Color ' . $cart_item['product2_color'] . ', Size ' . $cart_item['product2_size'] . '<br>
												Product 3: Color ' . $cart_item['product3_color'] . ', Size ' . $cart_item['product3_size'] . '
											</span>
										</div>
									</li>';
								} else {
									// Render individual product details
									echo '
									<li class="header-cart-item flex-w flex-t m-b-12">
										<div class="header-cart-item-img delete-item" data-id="' . $cart_item['product_id'] . '" data-type="product"  data-color="' . $cart_item['color'] . '" data-size="' . $cart_item['size'] . '">
											<img src="images/' . $cart_item['product_image'] . '" alt="IMG">
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

						<a href="shoping-cart.html" class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-b-10">
							Check Out
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>

		

	<!-- Slider -->
	<section class="section-slide">
		<div class="wrap-slick1">
			<div class="slick1">
				<div class="item-slick1" style="background-image: url(images/LV_Banner.avif);">
					<div class="container h-full">
						<div class="flex-col-l-m h-full p-t-100 p-b-30 respon5">
							<div class="layer-slick1 animated visible-false" data-appear="fadeInDown" data-delay="0">
								<span class="ltext-101 cl2 respon2" style="color: white; text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);">
									Women Collection 2024
								</span>
							</div>
								
							<div class="layer-slick1 animated visible-false" data-appear="fadeInUp" data-delay="800">
								<h2 class="ltext-201 cl2 p-t-19 p-b-43 respon1" style="color: white; text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);">
									NEW SEASON
								</h2>
							</div>
								
							<div class="layer-slick1 animated visible-false" data-appear="zoomIn" data-delay="1600">
								<a href="product.php" class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 trans-04">
									Shop Now
								</a>
							</div>
						</div>
					</div>
				</div>

				<div class="item-slick1" style="background-image: url(images/LV_Men.webp);">
					<div class="container h-full">
						<div class="flex-col-l-m h-full p-t-100 p-b-30 respon5">
							<div class="layer-slick1 animated visible-false" data-appear="rollIn" data-delay="0">
								<span class="ltext-101 cl2 respon2" style="color: white; text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);">
									Men New-Season
								</span>
							</div>
								
							<div class="layer-slick1 animated visible-false" data-appear="lightSpeedIn" data-delay="800">
								<h2 class="ltext-201 cl2 p-t-19 p-b-43 respon1" style="color: white; text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);">
									Bags & Accessories
								</h2>
							</div>
								
							<div class="layer-slick1 animated visible-false" data-appear="slideInUp" data-delay="1600">
								<a href="product.php" class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 trans-04">
									Shop Now
								</a>
							</div>
						</div>
					</div>
				</div>

				<div class="item-slick1" style="background-image: url(images/LV_New.jpg);">
					<div class="container h-full">
						<div class="flex-col-l-m h-full p-t-100 p-b-30 respon5">
							<div class="layer-slick1 animated visible-false" data-appear="rotateInDownLeft" data-delay="0">
								<span class="ltext-101 cl2 respon2" style="color: white; text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);" >
									New Collection 2024
								</span>
							</div>
								
							<div class="layer-slick1 animated visible-false" data-appear="rotateInUpRight" data-delay="800">
								<h2 class="ltext-201 cl2 p-t-19 p-b-43 respon1" style="color: white; text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);">
									New arrivals
								</h2>
							</div>
								
							<div class="layer-slick1 animated visible-false" data-appear="rotateIn" data-delay="1600">
								<a href="product.php" class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 trans-04">
									Shop Now
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>


	<!-- Banner -->
	<div class="sec-banner bg0 p-t-80 p-b-50">
		<div class="container">
			<div class="row">
				<div class="col-md-6 col-xl-4 p-b-30 m-lr-auto">
					<!-- Block1 -->
					<div class="block1 wrap-pic-w">
						<img src="images/banner-01.jpg" alt="IMG-BANNER">

						<a href="product.php?category_id=1" class="block1-txt ab-t-l s-full flex-col-l-sb p-lr-38 p-tb-34 trans-03 respon3">
							<div class="block1-txt-child1 flex-col-l">
								<span class="block1-name ltext-102 trans-04 p-b-8">
									Women's Bag
								</span>

								<span class="block1-info stext-102 trans-04">
									New 2024
								</span>
							</div>

							<div class="block1-txt-child2 p-b-4 trans-05">
								<div class="block1-link stext-101 cl0 trans-09">
									Shop Now
								</div>
							</div>
						</a>
					</div>
				</div>

				<div class="col-md-6 col-xl-4 p-b-30 m-lr-auto">
					<!-- Block1 -->
					<div class="block1 wrap-pic-w">
						<img src="images/banner-02.jpg" alt="IMG-BANNER">

						<a href="product.php?category_id=2" class="block1-txt ab-t-l s-full flex-col-l-sb p-lr-38 p-tb-34 trans-03 respon3">
							<div class="block1-txt-child1 flex-col-l">
								<span class="block1-name ltext-102 trans-04 p-b-8">
									Men's Bag
								</span>

								<span class="block1-info stext-102 trans-04">
									New 2024
								</span>
							</div>

							<div class="block1-txt-child2 p-b-4 trans-05">
								<div class="block1-link stext-101 cl0 trans-09">
									Shop Now
								</div>
							</div>
						</a>
					</div>
				</div>

				<div class="col-md-6 col-xl-4 p-b-30 m-lr-auto">
					<!-- Block1 -->
					<div class="block1 wrap-pic-w">
						<img src="images/banner-03.jpg" alt="IMG-BANNER">

						<a href="product.php?category_id=3" class="block1-txt ab-t-l s-full flex-col-l-sb p-lr-38 p-tb-34 trans-03 respon3">
							<div class="block1-txt-child1 flex-col-l">
								<span class="block1-name ltext-102 trans-04 p-b-8">
									Accessories
								</span>

								<span class="block1-info stext-102 trans-04">
									New Trend
								</span>
							</div>

							<div class="block1-txt-child2 p-b-4 trans-05">
								<div class="block1-link stext-101 cl0 trans-09">
									Shop Now
								</div>
							</div>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>


	<!-- Product -->
	<section class="bg0 p-t-23 p-b-140">
		<div class="container">
			<div class="p-b-10">
				<h3 class="ltext-103 cl5">
					Newest Product
				</h3>
			</div>

			<div class="row isotope-grid">
            <?php
				// Display products dynamically
				if ($product_result->num_rows > 0) {
					while($product = $product_result->fetch_assoc()) {

						// Determine product availability and stock status
						$isUnavailable = $product['product_status'] == 2;
						$isOutOfStock = $product['product_stock'] == 0;

						// Apply light grey color if unavailable or out of stock
						$productStyle = $isUnavailable || $isOutOfStock ? 'unavailable-product' : '';

						// Determine the message to show
						$message = '';
						if ($isUnavailable) {
							$message = '<p style="color: red; font-weight: bold;">Product is unavailable</p>';
						} elseif ($isOutOfStock) {
							$message = '<p style="color: red; font-weight: bold;">Product is out of stock</p>';
						}


						// Assign a class to each product based on its category_id
						echo '<div class="col-sm-6 col-md-4 col-lg-3 p-b-35 isotope-item category-' . $product['category_id'] . '">
								<div class="block2 ' . $productStyle . '">
									<div class="block2-pic hov-img0">
										<img src="images/' . $product['product_image'] . '" alt="IMG-PRODUCT">
										<a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1" 
											data-id="' . $product['product_id'] . '"' . ($isUnavailable || $isOutOfStock ? 'style="pointer-events: none; opacity: 0.5;"' : '') . '>
											Quick View
										</a>
									</div>
									<div class="block2-txt flex-w flex-t p-t-14">
										<div class="block2-txt-child1 flex-col-l ">
											<a href="product-detail.php?id=' . $product['product_id'] . '" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6"' . ($isUnavailable || $isOutOfStock ? 'style="pointer-events: none; opacity: 0.5;"' : '') . '>'
											. $product['product_name'] . 
											'</a>
											<span class="stext-105 cl3">$' . $product['product_price'] . '</span>
											' . $message . '
										</div>
										<div class="block2-txt-child2 flex-r p-t-3">
											<a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2"' . ($isUnavailable || $isOutOfStock ? 'style="pointer-events: none; opacity: 0.5;"' : '') . '>
												<img class="icon-heart1 dis-block trans-04" src="images/icons/icon-heart-01.png" alt="ICON">
												<img class="icon-heart2 dis-block trans-04 ab-t-l" src="images/icons/icon-heart-02.png" alt="ICON">
											</a>
										</div>
									</div>
								</div>
							</div>';
					}
				} else {
					echo "<p>No products found.</p>";
				}
				?>
        	</div>
		</div>
	</section>


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
								<div class="item-slick3" data-thumb="">
									<div class="wrap-pic-w pos-relative">
										<img src="images/<?php echo $product['Quick_View1']; ?>" alt="IMG-PRODUCT">
										<a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04" href="images/<?php echo $product['Quick_View1']; ?>">
											<i class="fa fa-expand"></i>
										</a>
									</div>
								</div>

								<div class="item-slick3" data-thumb="">
									<div class="wrap-pic-w pos-relative">
										<img src="images/<?php echo $product['Quick_View2']; ?>" alt="IMG-PRODUCT">
										<a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04" href="images/<?php echo $product['Quick_View2']; ?>">
											<i class="fa fa-expand"></i>
										</a>
									</div>
								</div>

								<div class="item-slick3" data-thumb="">
									<div class="wrap-pic-w pos-relative">
										<img src="images/<?php echo $product['Quick_View3']; ?>" alt="IMG-PRODUCT">
										<a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04" href="images/<?php echo $product['Quick_View3']; ?>">
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
							<?php echo $product['product_name']; ?>
						</h4>

						<span class="mtext-106 cl2">
							$<?php echo $product['product_price']; ?>
						</span>

						<p class="stext-102 cl3 p-t-23">
							<?php echo $product['product_des']; ?>
						</p>
						
						<!--  -->
						<div class="p-t-33">
							<div class="flex-w flex-r-m p-b-10">
								<div class="size-203 flex-c-m respon6">
									Size
								</div>

								<div class="size-204 respon6-next">
									<div class="rs1-select2 bor8 bg0">
										<select class="js-select2" name="size">
											<option>Choose an option</option>
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
										<select class="js-select2" name="color">
											<option>Choose an option</option>
										</select>
										<div class="dropDownSelect2"></div>
									</div>
								</div>
							</div>

							<div id="packageBox" style="margin-top: 20px;">
								
							</div>

							<div id="packageFormPopup" class="popup-overlay">
								<div class="popup-content">
									<span class="close-popup">&times;</span>
									<div id="packageFormContainer">
										<!-- Dynamic form content will be injected here -->
									</div>
								</div>
							</div>
							<div class="flex-w flex-r-m p-b-10">
								<div class="size-204 flex-w flex-m respon6-next">
									<div class="wrap-num-product flex-w m-r-20 m-tb-10">
										<div class="btn-num-product-down cl8 hov-btn3 trans-04 flex-c-m">
											<i class="fs-16 zmdi zmdi-minus"></i>
										</div>

										<input class="mtext-104 cl3 txt-center num-product" type="number" name="num-product" value="1" min="1">

										<div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m">
											<i class="fs-16 zmdi zmdi-plus"></i>
										</div>
									</div>

									<button class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 trans-04 js-addcart-detail">
										Add to cart
									</button>
								</div>
								<p class="stock-warning" style="color: red; display: none;">Quantity exceeds available stock.</p>
							</div>	
						</div>

						<div class="flex-w flex-m p-l-100 p-t-40 respon7">
							<div class="flex-m bor9 p-r-10 m-r-11">
								<a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 js-addwish-detail tooltip100" data-tooltip="Add to Wishlist">
									<i class="zmdi zmdi-favorite"></i>
								</a>
							</div>

							<a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 m-r-8 tooltip100" data-tooltip="Facebook">
								<i class="fa fa-facebook"></i>
							</a>

							<a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 m-r-8 tooltip100" data-tooltip="Twitter">
								<i class="fa fa-twitter"></i>
							</a>

							<a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 m-r-8 tooltip100" data-tooltip="Google Plus">
								<i class="fa fa-google-plus"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="vendor/jquery/jquery-3.2.1.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/animsition/js/animsition.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/bootstrap/js/popper.js"></script>
	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/select2/select2.min.js"></script>
	<script>
		$(".js-select2").each(function(){
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
		$('.gallery-lb').each(function() { // the containers for all your galleries
			$(this).magnificPopup({
		        delegate: 'a', // the selector for gallery item
		        type: 'image',
		        gallery: {
		        	enabled:true
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
		$('.js-addwish-b2').on('click', function(e){
			e.preventDefault();
		});

		$('.js-addwish-b2').each(function(){
			var nameProduct = $(this).parent().parent().find('.js-name-b2').html();
			$(this).on('click', function(){
				swal(nameProduct, "is added to wishlist !", "success");

				$(this).addClass('js-addedwish-b2');
				$(this).off('click');
			});
		});

		$('.js-addwish-detail').each(function(){
			var nameProduct = $(this).parent().parent().parent().find('.js-name-detail').html();

			$(this).on('click', function(){
				swal(nameProduct, "is added to wishlist !", "success");

				$(this).addClass('js-addedwish-detail');
				$(this).off('click');
			});
		});

		/*---------------------------------------------*/
	
	</script>
<!--===============================================================================================-->
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
		// Add click event listener to cart item images
		document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.delete-item').forEach(function (item) {
			item.addEventListener('click', function () {
				const id = this.dataset.id;
				const type = this.dataset.type;

				let body = `delete_item=1&id=${id}&type=${type}`;

				// Append additional data based on the type
				if (type === 'product') {
					const color = this.dataset.color;
					const size = this.dataset.size;
					body += `&color=${color}&size=${size}`;
					console.log('Deleting product:', { id, color, size });
				} else if (type === 'package') {
					const product1_color = this.dataset.product1Color;
					const product1_size = this.dataset.product1Size;
					const product2_color = this.dataset.product2Color;
					const product2_size = this.dataset.product2Size;
					const product3_color = this.dataset.product3Color;
					const product3_size = this.dataset.product3Size;

					body += `&product1_color=${product1_color}&product1_size=${product1_size}`;
					body += `&product2_color=${product2_color}&product2_size=${product2_size}`;
					body += `&product3_color=${product3_color}&product3_size=${product3_size}`;
					console.log('Deleting package:', { id, product1_color, product1_size, product2_color, product2_size, product3_color, product3_size });
				}

				// Confirm deletion
				Swal.fire({
					title: 'Are you sure?',
					text: 'Do you want to delete this item from your cart?',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Yes, delete it!',
					cancelButtonText: 'No, keep it',
				}).then((result) => {
					if (result.isConfirmed) {
						// Send AJAX request to delete the item
						fetch(location.href, {
							method: 'POST',
							headers: {
								'Content-Type': 'application/x-www-form-urlencoded',
							},
							body: body,
						})
						.then(response => response.json())
						.then(data => {
							console.log('Response:', data); // Log response for debugging
							if (data.success) {
								// Remove the item from the DOM
								document.querySelector('.header-cart-item').remove();
								// Update the total price
								document.getElementById('cart-total').textContent = data.new_total.toFixed(2);
								Swal.fire({
									title: 'Item removed!',
									text: 'The item has been removed from your cart.',
									icon: 'success',
									confirmButtonText: 'OK',
								}).then((result) => {
									if (result.isConfirmed) {
										location.reload();
									}
								});
							} else {
								Swal.fire({
									title: 'Error!',
									text: data.message || 'Failed to remove the item. Please try again.',
									icon: 'error',
									confirmButtonText: 'OK',
								});
							}
						})
						.catch(error => {
							console.error('Error:', error);
							Swal.fire({
								title: 'Error!',
								text: 'Something went wrong. Please try again later.',
								icon: 'error',
								confirmButtonText: 'OK',
							});
						});
					}
				});

			});
		});
	});



	</script>
	<script>
    $(document).on('click', '.js-show-modal1', function(event) {
        event.preventDefault();
        var productId = $(this).data('id');
        
        // Make an AJAX call to fetch product details
        $.ajax({
            url: '', // The same PHP file
            type: 'GET',
            data: { fetch_product: true, id: productId },
            dataType: 'json',
            success: function(response) {
                if (response) {
                    // Populate the modal with product data
                    $('.js-name-detail').text(response.product_name);
                    $('.mtext-106').text('$' + response.product_price);
                    $('.stext-102').text(response.product_des);

                    // Store the product ID and stock for later access
                    $('.js-addcart-detail').data('id', productId);
                    $('.js-addcart-detail').data('stock', response.product_stock);

                    // Update Quick View images
                    $('.gallery-lb .item-slick3').each(function(index) {
                        var imagePath = 'images/' + response['Quick_View' + (index + 1)];
                        $(this).find('.wrap-pic-w img').attr('src', imagePath);
                        $(this).find('.wrap-pic-w a').attr('href', imagePath);
                        $(this).attr('data-thumb', imagePath);
                    });
					// Update size options
                    // Update size options
					var sizeSelect = $('select[name="size"]');
					sizeSelect.empty(); // Clear existing options
					sizeSelect.append('<option value="">Choose an option</option>'); // Default option
					if (response.size1) sizeSelect.append('<option value="' + response.size1 + '">' + response.size1 + '</option>');
					if (response.size2) sizeSelect.append('<option value="' + response.size2 + '">' + response.size2 + '</option>');

					// Update color options
					var colorSelect = $('select[name="color"]');
					colorSelect.empty(); // Clear existing options
					colorSelect.append('<option value="">Choose an option</option>'); // Default option
					if (response.color1) colorSelect.append('<option value="' + response.color1 + '">' + response.color1 + '</option>');
					if (response.color2) colorSelect.append('<option value="' + response.color2 + '">' + response.color2 + '</option>');
					
                    // Show the modal
                    $('.js-modal1').addClass('show-modal1');
                } else {
                    alert('Product details not found.');
                }
            },
            error: function() {
                alert('An error occurred while fetching product details.');
            }
        });
    });

    $(document).ready(function () {
        // Update product quantity and enforce stock rules
        $(document).on('click', '.btn-num-product-up', function () {
			const $input = $(this).siblings('.num-product');
			const productStock = parseInt($('.js-addcart-detail').data('stock')) || 0; // Ensure `productStock` is an integer
			let currentVal = parseInt($input.val()) || 0; // Ensure `currentVal` is an integer
			
			if (currentVal < productStock) {
				$input.val(currentVal ++);
				$('.stock-warning').hide();
			} else {
				$('.stock-warning').text(`Only ${productStock} items are available in stock.`).show();
				$input.val(productStock); // Prevent further increment
			}
		});

		$(document).on('click', '.btn-num-product-down', function () {
			const $input = $(this).siblings('.num-product');
			let currentVal = parseInt($input.val()) || 0; // Ensure `currentVal` is an integer
			
			if (currentVal > 1) {
				$input.val(currentVal - 1);
				$('.stock-warning').hide();
			}
		});

        // Add to cart functionality
        $(document).on('click', '.js-addcart-detail', function (event) {
            event.preventDefault();

            const productId = $(this).data('id');
            const productName = $('.js-name-detail').text();
            const productPrice = parseFloat($('.mtext-106').text().replace('$', ''));
            const productQuantity = parseInt($('.num-product').val());
            const productStock = $(this).data('stock') || 0;
			const selectedColor = $('select[name="color"]').val();
   			const selectedSize = $('select[name="size"]').val();

			if (!selectedColor || !selectedSize) {
				Swal.fire({
                    title: 'Color and Size required!',
                    text: 'Please select a color or size.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
				return;
			}

            if (productQuantity > productStock) {
                $('.stock-warning').text(`Cannot add more than ${productStock} items.`).show();
                return;
            } else if (productQuantity === 0) {
                $('.stock-warning').text('Quantity cannot be zero.').show();
                return;
            }

            const totalPrice = productPrice * productQuantity;

            $.ajax({
                url: '', // Use the same PHP file
                type: 'POST',
                data: {
                    add_to_cart: true,
                    product_id: productId,
                    qty: productQuantity,
                    total_price: totalPrice,
					color: selectedColor, 
            		size: selectedSize
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Product has been added to your cart!',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    } else {
                        alert('Failed to add product to cart: ' + (response.error || 'unknown error'));
                    }
                    updateCart();
                    $('.js-modal1').removeClass('show-modal1');
                },
                error: function () {
                    alert('An error occurred while adding to the cart.');
                }
            });
        });
    });
	// Clear input data when the modal is closed
	$(document).on('click', '.js-hide-modal1', function() {
		$('.js-modal1').removeClass('show-modal1');

		// Reset input fields and warnings
		$('.num-product').val('1'); // Reset quantity to 1
		$('select[name="time"]').empty().append('<option>Choose an option</option>'); // Reset size
		$('select[name="color"]').empty().append('<option>Choose an option</option>'); // Reset color
		$('.stock-warning').hide(); // Hide stock warning
		$('.js-name-detail').text(''); // Clear product name
		$('.mtext-106').text(''); // Clear product price
		$('.stext-102').text(''); // Clear product description

		// Reset gallery images
		$('.gallery-lb .item-slick3').each(function() {
			$(this).find('.wrap-pic-w img').attr('src', '');
			$(this).find('.wrap-pic-w a').attr('href', '');
			$(this).attr('data-thumb', '');
		});
	});
</script>
	<script src="js/main.js"></script>

</body>
</html>
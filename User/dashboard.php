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
        SUM(sc.total_price) AS total_price
    FROM shopping_cart sc
    LEFT JOIN product_variant pv ON sc.variant_id = pv.variant_id
	LEFT JOIN product p ON pv.product_id = p.product_id
    LEFT JOIN promotion_product pm ON pv.promotion_id = pm.promotion_id
    WHERE sc.user_id = $user_id
    GROUP BY 
        sc.variant_id";
$cart_items_result = $connect->query($cart_items_query);
// Handle AJAX request to delete item

$query = "SELECT * FROM product_variant";
$result = mysqli_query($connect, $query);
$product_variants = mysqli_fetch_all($result, MYSQLI_ASSOC);

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
            sc.variant_id
        FROM shopping_cart sc
        WHERE sc.user_id = $user_id
        GROUP BY 
            sc.variant_id
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

    // Query to get all variants of the product
    $variant_query = "SELECT * FROM product_variant WHERE product_id = $product_id";
    $variant_result = $connect->query($variant_query);

    if ($variant_result->num_rows > 0) {
        $variants = [];
        $total_stock = 0;

        while ($variant = $variant_result->fetch_assoc()) {
            $variants[] = $variant;
            $total_stock += intval($variant['stock']);
        }

        // Fetch product details from the product table
        $product_query = "SELECT * FROM product WHERE product_id = $product_id";
        $product_result = $connect->query($product_query);
        $product = $product_result->fetch_assoc();

        // Combine product and variants data
        $product['variants'] = $variants;
        $product['total_stock'] = $total_stock;

        echo json_encode($product);
    } else {
        echo json_encode(null);
    }
    exit; // Stop further script execution
}

if (isset($_GET['fetch_variants']) && $_GET['fetch_variants'] === 'true') {
    // Fetch product ID from request
    $product_id = intval($_GET['product_id']);
    
    // Query to fetch product variants
    $query = "SELECT variant_id, product_id, color, size, stock, Quick_View1, Quick_View2, Quick_View3 
              FROM product_variant 
              WHERE product_id = $product_id";

    $result = mysqli_query($connect, $query);

    if ($result) {
        $variants = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $variants[] = [
                'variant_id' => $row['variant_id'],
                'product_id' => $row['product_id'],
                'color' => $row['color'],
                'size' => $row['size'],
                'stock' => $row['stock'],
                'Quick_View1' => $row['Quick_View1'],
                'Quick_View2' => $row['Quick_View2'],
                'Quick_View3' => $row['Quick_View3'],
            ];
        }

        // Return the variants as JSON
        echo json_encode($variants);
    } else {
        // If no results or query error, return error message
        echo json_encode(['error' => 'No variants found.']);
    }

    exit;
}
if (isset($_GET['fetch_promotions']) && isset($_GET['category_id'])) {
    $categoryId = intval($_GET['category_id']);
    error_log("Fetching promotions for category ID: $categoryId"); // Log category ID

    // Fetch promotions from the promotion_product table
    $query = $connect->prepare("SELECT * FROM promotion_product WHERE category_id = ? AND promotion_status = 1");
    $query->bind_param('i', $categoryId);
    $query->execute();
    $result = $query->get_result();

    $promotions = [];
    while ($row = $result->fetch_assoc()) {
        $promotions[] = $row;
    }

    error_log("Promotions fetched: " . json_encode($promotions)); // Log fetched promotions
    echo json_encode($promotions);
    exit();
}
// Fetch promotion details
if (isset($_GET['fetch_promotion_details']) && isset($_GET['promotion_id'])) {
    $promotionId = intval($_GET['promotion_id']);

    $sql = "SELECT promotion_name, promotion_price, promotion_des 
            FROM promotion_product 
            WHERE promotion_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $promotionId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $promotion = $result->fetch_assoc();
        echo json_encode($promotion);
    } else {
        echo json_encode(null);
    }

    $stmt->close();
    exit;
}

// Fetch promotion variants
if (isset($_GET['fetch_promotion_variants']) && isset($_GET['promotion_id'])) {
    $promotionId = intval($_GET['promotion_id']);

    $sql = "SELECT variant_id, color, size, Quick_View1, Quick_View2, Quick_View3, stock 
            FROM product_variant 
            WHERE promotion_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $promotionId);
    $stmt->execute();
    $result = $stmt->get_result();

    $variants = [];
    while ($row = $result->fetch_assoc()) {
        $variants[] = $row;
    }

    echo json_encode($variants);
    $stmt->close();
    exit;
}


// Handle AJAX request to add product to shopping cart
if (isset($_POST['add_to_cart']) && isset($_POST['variant_id']) && isset($_POST['qty']) && isset($_POST['total_price'])) {
    // Retrieve POST data
    $variant_id = intval($_POST['variant_id']); // Use variant_id directly from POST
    $qty = intval($_POST['qty']);
    $total_price = doubleval($_POST['total_price']);

    // Insert data into the shopping_cart table, including the user_id and variant_id
    $cart_query = "INSERT INTO shopping_cart (user_id, variant_id, qty, total_price) 
                   VALUES ($user_id, $variant_id, $qty, $total_price)";

    // Execute the query and return the response
    if ($connect->query($cart_query) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $connect->error]);
    }
    exit;
}

if (isset($_POST['add_promo_to_cart']) && isset($_POST['promotion_variant_id']) && isset($_POST['qty']) && isset($_POST['total_price'])) {
    // Retrieve POST data
    $variant_id = intval($_POST['promotion_variant_id']); // Use variant_id directly from POST
    $qty = intval($_POST['qty']);
    $total_price = doubleval($_POST['total_price']);

    // Insert data into the shopping_cart table, including the user_id and variant_id
    $cart_query = "INSERT INTO shopping_cart (user_id, variant_id, qty, total_price) 
                   VALUES ($user_id, $variant_id, $qty, $total_price)";

    // Execute the query and return the response
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
/* Overlay */
.wrap-promo-modal {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 9999;
    width: 100%;
    height: 100%;
    overflow: auto;
    display: none;
    background-color: rgba(0, 0, 0, 0.6);
}

.wrap-promo-modal.show {
    display: block;
}

.overlay-promo-modal {
    position: absolute;
    width: 100%;
    height: 100%;
    background: transparent;
    cursor: pointer;
}

/* Modal Content */
.bg-promo-modal {
    position: relative;
    margin: 5% auto;
    background-color: #fff;
    border-radius: 10px;
    padding: 20px;
    max-width: 600px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Close Button */
.close-promo-modal {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    cursor: pointer;
}

/* Title and Text */
.promo-title {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.promo-price {
    font-size: 20px;
    color: #e74c3c;
}

.promo-description {
    margin-top: 15px;
    font-size: 16px;
    color: #555;
}

/* Color Selection */
.promo-color-selection {
    margin-top: 15px;
}

.promo-select {
    padding: 10px;
    font-size: 14px;
    border: 1px solid #ddd;
    border-radius: 5px;
    outline: none;
}

/* Gallery */
.promo-gallery {
    margin-top: 20px;
}

.promo-gallery img {
    width: 200px;
    height: 200px;
    margin: 5px;
    border-radius: 5px;
    object-fit: cover;
    cursor: pointer;
    border: 2px solid transparent;
}

.promo-gallery img:hover {
    border-color: #007bff;
}
.slick-prev-p, .slick-next-p {
    position: absolute;
    top: 50%; /* Center vertically */
    transform: translateY(-50%);
    z-index: 1000;
    background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
    border-radius: 50%; /* Make them circular */
    color: white;
    font-size: 18px;
    width: 40px;
    height: 40px;
    line-height: 40px;
    text-align: center;
    cursor: pointer;
}

.slick-prev-p {
    left: -10px; /* Position to the left of the slider */
}

.slick-next-p {
    right: 340px; /* Position to the right of the slider */
}

/* Hover effects */
.slick-prev-p:hover, .slick-next-p:hover {
    background-color: rgba(0, 0, 0, 0.8);
}

.slick-prev, .slick-next {
    position: absolute;
    top: 50%; /* Center vertically */
    transform: translateY(-50%);
    z-index: 1000;
    background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
    border-radius: 50%; /* Make them circular */
    color: white;
    font-size: 18px;
    width: 40px;
    height: 40px;
    line-height: 40px;
    text-align: center;
    cursor: pointer;
}

.slick-prev {
    left: -50px; /* Position to the left of the slider */
}

.slick-next {
    right: -50px; /* Position to the right of the slider */
}

/* Hover effects */
.slick-prev:hover, .slick-next:hover {
    background-color: rgba(0, 0, 0, 0.8);
}

/* Optional: Remove default next/prev text */
.slick-prev:before, .slick-next:before {
    content: ''; /* Remove default arrows */
}

.selected {
    color: blue !important;
    font-weight: bold;
}
.isotope-grid {
    position: relative;
    overflow: hidden; /* Prevent overflow issues */
}

.footer {
    position: relative;
    z-index: 10; /* Ensure footer stays below content */
    margin-top: 10px; /* Ensure spacing between product container and footer */
}
body {
    overflow-y: auto; /* Allow smooth scrolling on larger content */
}
.isotope-grid {
    min-height: 50vh; /* Ensures content area fills the screen */
}


/* Promotion Box with Fixed Dimensions and Scrolling */
/* Promotion Head with Glowing Effect */
.promotion-head {
    font-size: 20px;
    font-weight: bold;
    color: #ff3b3b;
    text-align: center;
    background: transparent;
    width: 200px;
    padding: 10px;
    animation: softGlow 1s infinite alternate;
    border-radius: 5px;
    transition: transform 0.4s ease-in-out; /* Smooth scaling effect */
    margin: 0 auto;
}

@keyframes softGlow {
    from {
        text-shadow: 0 0 5px #ff6b6b, 0 0 8px #ff9999;
        transform: scale(1); /* Normal size */
    }
    to {
 
        text-shadow: 0 0 3px #ff3b3b, 0 0 5px #ff6b6b;
        transform: scale(1.1); /* Slightly larger size */
    }
}

/* Promotion Box with Fixed Dimensions and Scrolling */
.promotion-box {
    margin-top: 20px;
    padding: 10px;
    border-top: 1px solid #ddd;
    width: 400px;
    height: 300px;
    overflow-y: auto;
    background: #f9f9f9;
}

#promotionContent {
    margin-top: 10px;
}

/* Individual Promotion Items with Fixed Dimensions */
.promotion-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    padding: 10px;
    background: #fff;
    border-radius: 5px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    height: 100px; /* Set height */
    width: 100%;  /* Ensure full width of the box */
}

/* Product Image Styling */
.promotion-item img {
    width: 60px;
    height: 60px; /* Fixed height for uniformity */
    border-radius: 5px;
}

/* Product Details Section */
.promo-details {
    flex-grow: 1;
    margin-left: 10px;
}

.promotion-item h5 {
    font-size: 16px;
    margin: 0 0 5px;
}

.promo-price {
    color: #ff5722;
    font-weight: bold;
    font-size: 16px; /* Increase font size */
}

/* Separator Line Between Products */
.promotion-divider {
    border: 0;
    border-top: 1px solid #ddd;
    margin: 10px 0;
}

/* View Product Button with Fixed Dimensions */
.view-product-btn {
    background: #007bff;
    color: #fff;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    border-radius: 5px;
    transition: background 0.3s;
    height: 40px; /* Set fixed height */
    width: 100px; /* Set fixed width */
    text-align: center; /* Ensure button text is centered */
    display: flex;
    align-items: center;
    justify-content: center;
}


.promotion-modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    padding: 20px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    z-index: 1000;
}

.promotion-modal-content {
    max-width: 500px;
    margin: auto;
}

.close-promotion-modal {
    float: right;
    cursor: pointer;
}

.view-product-btn:hover {
    background: #0056b3;
}

.quick-view-images img {
    width: 100%;
    margin: 10px 0;
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
							<li class="active-menu">
								<a href="dashboard.php">Home</a>
							</li>

							<li>
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

                    <a href="checkout.php" class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-b-10">
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
									New 2025
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
									New 2025
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
						$product_id = $product['product_id'];
	
						// Determine product availability and stock status
						// Get total stock for the product from product_variant table
						$variant_query = "SELECT * FROM product_variant WHERE product_id = $product_id";
						$variant_result = $connect->query($variant_query);
	
						$total_stock = 0;
						$isOutOfStock = true;
						$colors = []; // Store available colors and their corresponding images
	
						while ($variant = $variant_result->fetch_assoc()) {
							$total_stock += intval($variant['stock']);
							if (intval($variant['stock']) > 0) {
								$isOutOfStock = false;
							}
							$colors[] = [
								'color' => $variant['color'],
								'image' => $variant['Quick_View1'], // Assuming there's a column 'variant_image' for each color
							];
						}
	
						$isUnavailable = $product['product_status'] == 2;
						$productStyle = $isUnavailable || $isOutOfStock ? 'unavailable-product' : '';
	
						$message = '';
						if ($isUnavailable) {
							$message = '<p style="color: red; font-weight: bold;">Product is unavailable</p>';
						} elseif ($isOutOfStock) {
							$message = '<p style="color: red; font-weight: bold;">Product is out of stock</p>';
						}
	
	
						// Assign a class to each product based on its category_id
						echo '<div class="col-sm-6 col-md-4 col-lg-3 p-b-35 isotope-item category-' . $product['category_id'] . '"style="margin-right: -30px;">
								<div class="block2 ' . $productStyle . '">
									<div class="block2-pic hov-img0">
										<img src="images/' . $product['product_image'] . '" alt="IMG-PRODUCT" id="product-image-' . $product_id . '">
										<a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1" 
											data-id="' . $product['product_id'] . '"' . ($isUnavailable || $isOutOfStock ? 'style="pointer-events: none; opacity: 0.5;"' : '') . '>
											Quick View
										 </a>
									</div>
									<div class="block2-txt flex-w flex-t p-t-14">
										<div class="block2-txt-child1 flex-col-l ">
											<a href="product-detail.php?id=' . $product['product_id'] . '&type=product" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6"' . ($isUnavailable || $isOutOfStock ? 'style="pointer-events: none; opacity: 0.5;"' : '') . '>'
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
									<div class="block2-txt-child2 flex-r p-t-3">';
						
										// Display color circles
										foreach ($colors as $index => $color) {
											$iconClass = strtolower($color['color']) === 'white' ? 'zmdi-circle-o' : 'zmdi-circle';
											$styleColor = strtolower($color['color']) === 'white' ? '#aaa' : $color['color'];
											echo '<span class="fs-15 lh-12 m-r-6 color-circle" style="color: ' . $styleColor . '; cursor: pointer;" 
													data-image="images/' . $color['image'] . '" data-product-id="' . $product_id . '">
													<i class="zmdi ' . $iconClass . '"></i>
												</span>';
										}
								
	
					echo '      </div>
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
                            <div class="size-display"></div>

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

							<div id="promotionBox" class="promotion-box">
                                <div class="promotion-head"> New Promotion!!!</div>
                                <div id="promotionContent">
                                    <!-- Promotional products will be dynamically injected here -->
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
<div id="promotionModal" class="wrap-promo-modal p-t-60 p-b-20">
    <div class="overlay-promo-modal js-hide-promo-modal"></div>

    <div class="container">
        <button class="close-promo-modal how-pos3 hov3 trans-04 js-hide-promo-modal">
            <img src="images/icons/icon-close.png" alt="CLOSE">
        </button>
        <div class="bg-promo-modal p-t-40 p-b-30 p-lr-20-lg how-pos3-parent">

            <div class="promo-gallery flex-w flex-sb p-t-30"></div>

            <h2 class="promo-title mtext-105 cl2 p-b-10"></h2>
            <span class="promo-price mtext-106 cl2"></span>
            <p class="promo-description cl3 p-t-20"></p>

            <div class="promo-size-display cl3 p-t-10"></div>

            <div class="promo-color-selection p-t-20">
                <label for="promo-color" class="cl3 p-r-10">Color:</label>
                <select id="promo-color" class="promo-select"></select>
            </div>

            <div class="promotion-details">
                <div class="quantity">
                    <button class="btn-num-promo-up">+</button>
                    <input type="number" class="num-promo" value="1" min="1" />
                    <button class="btn-num-promo-down">-</button>
                </div>
                <div class="promo-stock-warning" style="display:none;"></div>
            </div>

            <button class="js-add-promo-cart">Add to Cart</button>
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

    // Fetch product and variant details
    $.ajax({
        url: '', // The same PHP file
        type: 'GET',
        data: { fetch_product: true, id: productId },
        dataType: 'json',
        success: function(response) {
            if (response) {
                // Populate modal details
                $('.js-name-detail').data('id', productId);
                $('.js-name-detail').text(response.product_name);
                $('.mtext-106').text('$' + response.product_price);
                $('.stext-102').text(response.product_des);

                // Fetch variants for the product
                fetchVariants(productId, response);
                
                // Show modal
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

function fetchVariants(productId, productResponse) {
    var categoryId = productResponse.category_id;
    console.log("Product category ID:", categoryId); // Ensure the category ID is included in the response
    fetchPromotions(categoryId);
    console.log("Fetching variants for product ID:", productId); // Log product ID
    console.log("Product Response:", productResponse);
    console.log("Fetching variants for product ID:", productId); // Log product ID
    console.log("Product Response:", productResponse); // Log product data
    $.ajax({
        url: '', // Replace with the correct PHP endpoint or file URL
        type: 'GET',
        data: { fetch_variants: true, product_id: productId },
        dataType: 'json',
        success: function(variants) {
            console.log("Variants fetched successfully:", variants);
            window.productVariants = variants;
            if (variants && variants.length > 0) {
                // Find the variant with the lowest ID
                var defaultVariant = variants.reduce((lowest, current) =>
                    current.variant_id < lowest.variant_id ? current : lowest
                );

                // Display default Quick View images
                updateQuickViewImages(defaultVariant);

                // Populate color options
                var colorSelect = $('select[name="color"]');
                colorSelect.empty();
                colorSelect.append('<option value="">Choose an option</option>');
                var uniqueColors = [...new Set(variants.map(v => v.color))];
                uniqueColors.forEach(color => {
                    colorSelect.append('<option value="' + color + '">' + color + '</option>');
                });

                // Handle color change
                colorSelect.on('change', function() {
                    var selectedColor = $(this).val();
                    if (selectedColor) {
                        colorSelect.val(selectedColor);
                        var variant = variants.find(v => v.color === selectedColor);
                        if (variant) {
                            updateQuickViewImages(variant);
                            $('.size-display').text('Size: ' + variant.size);
                        }
                    }
                });

                // Display size for the default variant
                $('.size-display').text('Size: ' + defaultVariant.size);
            } else {
                console.error("No variants found for this product.");
                alert('No variants found for this product.');
            }
        },
        error: function(xhr, status, error) {
            console.error("Error fetching variants:", xhr.responseText);
            alert('An error occurred while fetching product variants.');
        }
    });
}
function fetchPromotions(categoryId) {
    console.log("Fetching promotions for category ID:", categoryId); // Log the category ID
    $.ajax({
        url: '', // PHP endpoint URL
        type: 'GET',
        data: { fetch_promotions: true, category_id: categoryId },
        dataType: 'json',
        success: function(promotions) {
            console.log("Promotions fetched successfully:", promotions); // Log the fetched promotions
            var promotionContent = $('#promotionContent');
            promotionContent.empty(); // Clear previous promotions
            
            if (promotions && promotions.length > 0) {
                promotions.forEach(promotion => {
                    console.log("Adding promotion to the box:", promotion); // Log each promotion being added
                    var promoHTML = `
                        <div class="promotion-item">
                            <img src="images/${promotion.promotion_image}" alt="${promotion.promotion_name}" class="promo-image">
                            <h5>${promotion.promotion_name}</h5>
                            <span class="promo-price">$${promotion.promotion_price}</span>
                            <button class="view-product-btn" data-promotion-id="${promotion.promotion_id}">
                                View Product
                            </button>
                        </div>
                        <hr class="promotion-divider">
                    `;

                    promotionContent.append(promoHTML);
                });
            } else {
                console.warn("No promotions found for this category."); // Warn if no promotions are found
                promotionContent.append('<p>No promotions available for this category.</p>');
            }
        },
        error: function(xhr, status, error) {
            console.error("Error fetching promotions:", xhr.responseText); // Log error details
            alert('An error occurred while fetching promotional products.');
        }
    });
}
$(document).on('click', '.view-product-btn', function () {
    var promotionId = $(this).data('promotion-id');
    console.log("View Product button clicked. Promotion ID:", promotionId);

    // Fetch promotion details
    $.ajax({
        url: '', // Replace with your PHP file path
        type: 'GET',
        data: { fetch_promotion_details: true, promotion_id: promotionId },
        dataType: 'json',
        success: function (promotion) {
            console.log("Promotion details fetched:", promotion);
            if (promotion) {
                // Populate modal with promotion details
                $('#promotionModal .promo-title').text(promotion.promotion_name).data('promotion-id', promotionId);
                $('#promotionModal .promo-price').text('$' + promotion.promotion_price);
                $('#promotionModal .promo-description').text(promotion.promotion_des);
                
                // Fetch variants for the promotion
                fetchPromotionVariants(promotionId);

                $('#promotionModal').addClass('show');
            } else {
                console.error("Promotion details not found for ID:", promotionId);
                alert('Promotion details not found.');
            }
        },
        error: function (xhr, status, error) {
        if (xhr && xhr.responseText) {
            console.error("Error fetching promotion details:", xhr.responseText);
        } else {
            console.error("An error occurred while making the AJAX request:", error);
        }
        alert('An error occurred while fetching promotion details.');
    }
    });
});

function fetchPromotionVariants(promotionId) {
    console.log("Fetching variants for Promotion ID:", promotionId);
    $.ajax({
        url: '', // Replace with your PHP file path
        type: 'GET',
        data: { fetch_promotion_variants: true, promotion_id: promotionId },
        dataType: 'json',
        success: function (variants) {
            console.log("Variants fetched successfully:", variants);
            window.promotionVariants = variants;
            if (variants && variants.length > 0) {
                // Populate color options
                var colorSelect = $('#promo-color');
                colorSelect.empty();
                colorSelect.append('<option value="">Select a color</option>');

                var uniqueColors = [...new Set(variants.map(v => v.color))];
                uniqueColors.forEach(color => {
                    colorSelect.append('<option value="' + color + '">' + color + '</option>');
                });

                // Handle color selection
                colorSelect.off('change').on('change', function () {
                    var selectedColor = $(this).val();
                    if (selectedColor) {
                        colorSelect.val(selectedColor);
                        var variant = variants.find(v => v.color === selectedColor);
                        if (variant) {
                            updatePromotionImages(variant);
                            $('.promo-size-display').text('Size: ' + variant.size);
                        }
                    }
                });

                // Default: Display first variant
                var defaultVariant = variants[0];
                updatePromotionImages(defaultVariant);
                $('#promotionModal .promo-size-display').text('Size: ' + defaultVariant.size);
            } else {
                alert('No variants found for this promotion.');
            }
        },
        error: function (xhr, status, error) {
        if (xhr && xhr.responseText) {
            console.error("Error fetching promotion variants:", xhr.responseText);
        } else {
            console.error("An error occurred while making the AJAX request:", error);
        }
        alert('An error occurred while fetching promotion variants.');
    }
    });
}

function updatePromotionImages(variant) {
    var galleryContainer = $('#promotionModal .promo-gallery');
    
    // Destroy existing Slick instance if initialized
    if (galleryContainer.hasClass('slick-initialized')) {
        galleryContainer.slick('unslick');
        console.log("Slick carousel destroyed.");
    }

    galleryContainer.empty();

    for (var i = 1; i <= 3; i++) {
        var imageKey = 'Quick_View' + i;
        if (variant[imageKey]) {
            console.log(`Adding image: ${variant[imageKey]}`);
            var imagePath = 'images/' + variant[imageKey];
            galleryContainer.append(`
                <div class="quick_view" data-thumb="${imagePath}">
                    <div class="wrap-pic-w pos-relative">
                        <img src="${imagePath}" alt="IMG-PRODUCT">
                    </div>
                </div>
            `);
        }
    }

    // Reinitialize Slick slider if necessary
    galleryContainer.slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        fade: true,
        dots: true,
        prevArrow: '<button type="button" class="slick-prev-p"><i class="fa fa-chevron-left"></i></button>',
        nextArrow: '<button type="button" class="slick-next-p"><i class="fa fa-chevron-right"></i></button>',
        customPaging: function (slider, i) {
                var thumb = $(slider.$slides[i]).data('thumb');
        }
    });

    console.log("Slick carousel reinitialized with new images.");
}


// Close the modal
$(document).on('click', '.js-hide-promo-modal', function () {
    $('#promotionModal').removeClass('show');
});


function updateQuickViewImages(variant) {
    console.log("Updating Quick View Images for variant:", variant);
    var galleryContainer = $('.gallery-lb');

    // Destroy existing Slick instance if initialized
    if (galleryContainer.hasClass('slick-initialized')) {
        galleryContainer.slick('unslick');
        console.log("Slick carousel destroyed.");
    }

    galleryContainer.empty(); // Clear existing images

    // Append images
    for (var i = 1; i <= 3; i++) {
        var imageKey = 'Quick_View' + i;
        if (variant[imageKey]) {
            console.log(`Adding image: ${variant[imageKey]}`);
            var imagePath = 'images/' + variant[imageKey];
            galleryContainer.append(`
                <div class="item-slick3" data-thumb="${imagePath}">
                    <div class="wrap-pic-w pos-relative">
                        <img src="${imagePath}" alt="IMG-PRODUCT">
                        <a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04" href="${imagePath}">
                            <i class="fa fa-expand"></i>
                        </a>
                    </div>
                </div>
            `);
        }
        else {
            console.warn(`Image key ${imageKey} is missing or empty for this variant.`); // Log missing images
        }
    }

    // Reinitialize Slick slider
    galleryContainer.slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        fade: true,
        dots: true,
        prevArrow: '<button type="button" class="slick-prev"><i class="fa fa-chevron-left"></i></button>',
        nextArrow: '<button type="button" class="slick-next"><i class="fa fa-chevron-right"></i></button>',
        customPaging: function (slider, i) {
                var thumb = $(slider.$slides[i]).data('thumb');
        }
    });
}
function getStockBasedOnSelection(selectedColor) {
    if (!window.productVariants || !selectedColor) return 0;

    // Find the variant matching the selected color
    const matchingVariant = window.productVariants.find(variant => variant.color === selectedColor);

    if (matchingVariant) {
        return parseInt(matchingVariant.stock || 0); // Return the stock for the matching variant
    }

    return 0; // Return 0 if no matching variant is found
}

// Update button up/down
$(document).on('click', '.btn-num-product-up, .btn-num-product-down', function (e) {
    e.preventDefault();

    const $input = $(this).siblings('.num-product');
    const selectedColor = $('select[name="color"]').val();
    const productStock = getStockBasedOnSelection(selectedColor); // Get stock based on selected color
    let currentVal = parseInt($input.val()) || 0;

    if (!selectedColor) {
        $('.stock-warning').text('Please choose a color!').css('color', 'red').show();
        $input.val('0'); // Reset quantity to 0
        return;
    }

    if ($(this).hasClass('btn-num-product-up')) {
        if (currentVal < productStock) {
            $input.val(currentVal ++);
            $('.stock-warning').hide();
        } else {
            $('.stock-warning').text(`Only ${productStock} items are available in stock.`).show();
            $input.val(productStock); // Prevent further increment
        }
    } else if ($(this).hasClass('btn-num-product-down')) {
        if (currentVal > 1) {
            $input.val(currentVal - 1);
            $('.stock-warning').hide();
        }
    }
});

// Update the color change logic
$(document).on('change', 'select[name="color"]', function () {
    $('.stock-warning').hide(); // Hide any previous warnings
    const selectedColor = $(this).val();

    if (!selectedColor) {
        $('.stock-warning').text('Please choose a color!').css('color', 'red').show();
        $('.num-product').val('0'); // Reset quantity to 0
        return;
    }

    // Update stock display or other UI elements if needed
    const productStock = getStockBasedOnSelection(selectedColor);
    if (productStock > 0) {
        $('.stock-warning').hide();
        $('.num-product').val('1'); // Reset to a valid starting quantity
    } else {
        $('.stock-warning').text('Selected color is out of stock!').css('color', 'red').show();
        $('.num-product').val('0'); // Reset quantity to 0 if out of stock
    }
});
    

$(document).ready(function () {
    // Add to cart functionality
    $(document).on('click', '.js-addcart-detail', function (event) {
        event.preventDefault();

        console.log("Add to Cart button clicked."); // Debug

        // Retrieve product ID directly from the modal element
        const productId = $('.js-name-detail').data('id'); // Assuming the product ID is stored in the modal
        console.log("Product ID retrieved from modal:", productId); // Debug

        const productName = $('.js-name-detail').text(); // Product Name
        console.log("Product Name:", productName); // Debug

        const productPrice = parseFloat($('.mtext-106').text().replace('$', '')); // Product Price
        console.log("Product Price:", productPrice); // Debug

        const productQuantity = parseInt($('.num-product').val()); // Quantity selected by user
        console.log("Product Quantity:", productQuantity); // Debug

        const selectedColor = $('select[name="color"]').val(); // Selected Color
        console.log("Selected Color:", selectedColor); // Debug

        const response = window.productVariants; // All product variants loaded in the modal
        console.log("Loaded Variants:", response); // Debug

        // Validate color selection
        if (!selectedColor) {
            console.warn("Color not selected!"); // Debug
            Swal.fire({
                title: 'Color Required!',
                text: 'Please select a color for the product.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Find the matching variant based on the selected color
        const matchingVariant = response.find(variant => variant.color === selectedColor);

        console.log("Matching Variant for Selected Color:", matchingVariant); // Debug

        if (!matchingVariant) {
            console.error("No matching variant found for the selected color."); // Debug
            Swal.fire({
                title: 'Invalid Variant!',
                text: 'No size or variant found for the selected color.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        const variantId = matchingVariant.variant_id; // Variant ID from the product_variant table
        console.log("Variant ID:", variantId); // Debug

        const productStock = parseInt(matchingVariant.stock || 0); // Stock of the matching variant
        console.log("Available Stock for Selected Variant:", productStock); // Debug

        // Validate stock
        if (productQuantity > productStock) {
            console.warn("Quantity exceeds available stock."); // Debug
            Swal.fire({
                title: 'Stock Limit Exceeded!',
                text: `Only ${productStock} items are available for the selected color.`,
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (productQuantity === 0) {
            console.warn("Quantity is zero."); // Debug
            $('.stock-warning').text('Quantity cannot be zero.').show();
            return;
        }

        // Calculate total price
        const totalPrice = productPrice * productQuantity;
        console.log("Total Price:", totalPrice); // Debug

        // Send Add to Cart request
        $.ajax({
            url: '', // Use the same PHP file
            type: 'POST',
            data: {
                add_to_cart: true,
                variant_id: variantId, // Variant ID from the product_variant table
                qty: productQuantity, // Quantity
                total_price: totalPrice, // Total Price
            },
            dataType: 'json',
            success: function (response) {
                console.log("Add to Cart Response:", response); // Debug
                if (response.success) {
                    Swal.fire({
                        title: 'Product has been added to your cart!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload(); // Reload page to update cart
                        }
                    });
                } else {
                    console.error("Add to Cart failed:", response.error); // Debug
                    alert('Failed to add product to cart: ' + (response.error || 'unknown error'));
                }
                updateCart();
                $('.js-modal1').removeClass('show-modal1');
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error); // Debug
                console.error("Server Response:", xhr.responseText); // Debug
                alert('An error occurred while adding to the cart.');
            }
        });
    });
});
function getPromotionStockBasedOnSelection(selectedColor) {
    if (!window.promotionVariants || !selectedColor) return 0;

    // Find the variant matching the selected color for the promotion
    const matchingVariant = window.promotionVariants.find(variant => variant.color === selectedColor);

    if (matchingVariant) {
        return parseInt(matchingVariant.stock || 0); // Return the stock for the matching variant
    }

    return 0; // Return 0 if no matching variant is found
}

// Update button up/down for promotion
$(document).on('click', '.btn-num-promo-up, .btn-num-promo-down', function (e) {
    e.preventDefault();

    const $input = $(this).siblings('.num-promo');
    const selectedColor = $('#promo-color').val(); // Promotion color select input
    const promotionStock = getPromotionStockBasedOnSelection(selectedColor); // Get stock for the selected color
    let currentVal = parseInt($input.val()) || 0;

    if (!selectedColor) {
        $('.promo-stock-warning').text('Please choose a color!').css('color', 'red').show();
        $input.val('0'); // Reset quantity to 0
        return;
    }

    if ($(this).hasClass('btn-num-promo-up')) {
        if (currentVal < promotionStock) {
            $input.val(currentVal + 1);
            $('.promo-stock-warning').hide();
        } else {
            $('.promo-stock-warning').text(`Only ${promotionStock} items are available in stock.`).show();
            $input.val(promotionStock); // Prevent further increment
        }
    } else if ($(this).hasClass('btn-num-promo-down')) {
        if (currentVal > 1) {
            $input.val(currentVal - 1);
            $('.promo-stock-warning').hide();
        }
    }
});

// Update the color change logic for promotion
$(document).on('change', '#promo-color', function () {
    $('.promo-stock-warning').hide(); // Hide any previous warnings
    const selectedColor = $(this).val();

    if (!selectedColor) {
        $('.promo-stock-warning').text('Please choose a color!').css('color', 'red').show();
        $('.num-promo').val('0'); // Reset quantity to 0
        return;
    }

    // Update stock display or other UI elements if needed
    const promotionStock = getPromotionStockBasedOnSelection(selectedColor);
    if (promotionStock > 0) {
        $('.promo-stock-warning').hide();
        $('.num-promo').val('1'); // Reset to a valid starting quantity
    } else {
        $('.promo-stock-warning').text('Selected color is out of stock!').css('color', 'red').show();
        $('.num-promo').val('0'); // Reset quantity to 0 if out of stock
    }
});

// Add promotion to cart functionality
$(document).ready(function () {
    $(document).on('click', '.js-add-promo-cart', function (event) {
        event.preventDefault();

        console.log("Add to Cart button clicked for promotion.");

        // Retrieve promotion ID directly from the modal element
        const promotionId = $('#promotionModal .promo-title').data('promotion-id'); // Assuming the promotion ID is stored in the modal
        console.log("Promotion ID retrieved from modal:", promotionId);

        const promotionName = $('#promotionModal .promo-title').text(); // Promotion Name
        console.log("Promotion Name:", promotionName);

        const promotionPrice = parseFloat($('#promotionModal .promo-price').text().replace('$', '')); // Promotion Price
        console.log("Promotion Price:", promotionPrice);

        const promotionQuantity = parseInt($('.num-promo').val()); // Quantity selected by user
        console.log("Promotion Quantity:", promotionQuantity);

        const selectedColor = $('#promo-color').val(); // Selected Color for promotion
        console.log("Selected Color for Promotion:", selectedColor);

        const response = window.promotionVariants; // All promotion variants loaded in the modal
        console.log("Loaded Promotion Variants:", response);

        // Validate color selection
        if (!selectedColor) {
            console.warn("Color not selected for promotion!");
            Swal.fire({
                title: 'Color Required!',
                text: 'Please select a color for the promotion product.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Find the matching variant based on the selected color
        const matchingVariant = response.find(variant => variant.color === selectedColor);

        console.log("Matching Variant for Selected Color:", matchingVariant);

        if (!matchingVariant) {
            console.error("No matching variant found for the selected color.");
            Swal.fire({
                title: 'Invalid Variant!',
                text: 'No variant found for the selected color.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        const variantId = matchingVariant.variant_id; // Variant ID from the promotion_variant table
        console.log("Promotion Variant ID:", variantId);

        const promotionStock = parseInt(matchingVariant.stock || 0); // Stock of the matching variant
        console.log("Available Stock for Selected Variant:", promotionStock);

        // Validate stock
        if (promotionQuantity > promotionStock) {
            console.warn("Quantity exceeds available stock for promotion.");
            Swal.fire({
                title: 'Stock Limit Exceeded!',
                text: `Only ${promotionStock} items are available for the selected color.`,
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (promotionQuantity === 0) {
            console.warn("Quantity is zero.");
            $('.promo-stock-warning').text('Quantity cannot be zero.').show();
            return;
        }

        // Calculate total price for promotion
        const totalPrice = promotionPrice * promotionQuantity;
        console.log("Total Price for Promotion:", totalPrice);

        // Send Add to Cart request for promotion
        $.ajax({
            url: '', // Use the same PHP file for promotions
            type: 'POST',
            data: {
                add_promo_to_cart: true,
                promotion_variant_id: variantId, // Variant ID from the promotion_variant table
                qty: promotionQuantity, // Quantity for promotion
                total_price: totalPrice, // Total Price for promotion
            },
            dataType: 'json',
            success: function (response) {
                console.log("Add to Cart Response for Promotion:", response);
                if (response.success) {
                    Swal.fire({
                        title: 'Promotion product has been added to your cart!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload(); // Reload page to update cart
                        }
                    });
                } else {
                    console.error("Add to Cart failed for promotion:", response.error);
                    alert('Failed to add promotion product to cart: ' + (response.error || 'unknown error'));
                }
                updateCart();
                $('#promotionModal').removeClass('show');
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error for promotion:", status, error);
                console.error("Server Response for promotion:", xhr.responseText);
                alert('An error occurred while adding the promotion product to the cart.');
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
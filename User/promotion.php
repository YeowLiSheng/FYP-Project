<?php
session_start(); // Start the session

// Include the database connection file
include("dataconnection.php"); 

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

// Check if the database connection exist
if (!isset($connect) || !$connect) { 
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
// Handle AJAX request to fetch product details
if (isset($_GET['fetch_promotion']) && isset($_GET['id'])) {
    $promotion_id = intval($_GET['id']);

    // Fetch promotion details
    $promotion_query = "SELECT * FROM promotion_product WHERE promotion_id = $promotion_id";
    $promotion_result = $connect->query($promotion_query);

    if ($promotion_result && $promotion_result->num_rows > 0) {
        $promotion = $promotion_result->fetch_assoc();

        // Fetch variants
        $variant_query = "SELECT * FROM product_variant WHERE promotion_id = $promotion_id";
        $variant_result = $connect->query($variant_query);
        $variants = [];
        if ($variant_result) {
            while ($variant = $variant_result->fetch_assoc()) {
                $variants[] = $variant;
            }
        }

        $promotion['variants'] = $variants;
        echo json_encode($promotion);
    } else {
        echo json_encode(['error' => 'Promotion not found.']);
    }
    exit;
}

if (isset($_GET['fetch_variants']) && isset($_GET['promotion_id'])) {
    $promotion_id = intval($_GET['promotion_id']);

    $query = "SELECT * FROM product_variant WHERE promotion_id = $promotion_id";
    $result = $connect->query($query);

    $variants = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $variants[] = $row;
        }
    }

    echo json_encode($variants);
    exit;
}

$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$promotion_query = "SELECT DISTINCT p.* FROM promotion_product p
                  JOIN product_variant pv ON p.promotion_id = pv.promotion_id
                  WHERE p.promotion_name LIKE '%$search_query%'";
$promotion_query .= " GROUP BY p.promotion_id";

$promotion_result = $connect->query($promotion_query);
if (isset($_GET['fetch_variants']) && $_GET['fetch_variants'] === 'true') {
    // Fetch product ID from request
    $promotion_id = intval($_GET['promotion_id']);
    
    // Query to fetch product variants
    $query = "SELECT variant_id, promotion_id, color, size, stock, Quick_View1, Quick_View2, Quick_View3 
              FROM product_variant 
              WHERE promotion_id = $promotion_id";

    $result = mysqli_query($connect, $query);

    if ($result) {
        $variants = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $variants[] = [
                'variant_id' => $row['variant_id'],
                'promotion_id' => $row['promotion_id'],
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

// Fetch categories
$category_query = "SELECT * FROM category";
$category_result = $connect->query($category_query);

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
$query = "SELECT * FROM product_variant";
$result = mysqli_query($connect, $query);
$product_variants = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Product</title>
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
    left: -30px; /* Position to the left of the slider */
}

.slick-next {
    right: -10px; /* Position to the right of the slider */
}

/* Hover effects */
.slick-prev:hover, .slick-next:hover {
    background-color: rgba(0, 0, 0, 0.8);
}

/* Optional: Remove default next/prev text */
.slick-prev:before, .slick-next:before {
    content: ''; /* Remove default arrows */
}
.block2-btn {
    font-size: 16px; /* Increase the font size */
    padding: 20px 20px; /* Adjust padding for a larger button */
    border-radius: 50px; /* Add rounded corners (optional) */
    width: auto; /* Ensure it adapts dynamically */
    height: auto; /* Ensure it adapts dynamically */
    display: inline-block; /* Make it larger without affecting layout */
}
.block2-txt {
    position: relative;
    padding: 10px 20px 10px 20px; /* Add padding to the left and right */
}

.block2-txt-child1 {
    font-size: 18px;
    margin-bottom: 10px;
    padding-left: 10px; /* Add extra space to the left of the product name */
}

.block2-txt-child1 a {
    font-weight: bold;
    color: #333;
    text-decoration: none;
}

.block2-txt-child1 span {
    font-size: 16px;
    color: #555;
    display: block;
    margin-top: 5px;
    padding-left: 3px; /* Add extra space to the left of the price */
}
.category-container {
    display: none;
}
.category-container.active {
    display: block;
}
/* Center the container */
.filter-container {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 50px;
}

/* Center align each item */
.filter-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
}

.filter-name {
  margin-bottom: 10px;
  font-size: 16px;
  font-weight: bold;
  text-align: center;
  transition: color 0.3s, text-decoration 0.3s;
}

.filter-img {
  width: 150px;
  height: 150px;
  transition: transform 0.3s;
}

/* Hover effects for name and image */
.filter-item:hover .filter-name {
  text-decoration: underline;
}

.filter-item:hover .filter-img {
  transform: scale(1.05);
}
.stock-warning{
    text-align: center;
    display: block;
}
.size-display{
    font-size: 1.5rem; /* Larger font size */
    font-family: 'Arial Black', sans-serif; /* Different font */
    color: #333; /* Slightly darker color for emphasis */
    margin-top: 20px;
    margin-bottom: 30px;
    justify-content: center;
    align-items: center;
    display: flex;

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
.swal2-container {
    z-index: 99999 !important; /* Ensure it appears above all other elements */
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

							<li>
								<a href="product.php">Shop</a>
							</li>

                            <li class="active-menu">
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

        <div class="filter-container">
        <div class="filter-item" data-category="all">
            <img src="images/All.png" alt="All Categories" class="filter-img">
            <div class="filter-name">All</div>
        </div>
        <div class="filter-item" data-category="1">
            <img src="images/M13085.avif" alt="Monogram Multicolore" class="filter-img">
            <div class="filter-name">Women's Bag</div>
        </div>
        <div class="filter-item" data-category="2">
            <img src="images/M46271.avif" alt="Superflat" class="filter-img">
            <div class="filter-name">Man's Bag</div>
        </div>
        <div class="filter-item" data-category="3">
            <img src="images/louis-vuitton-le-damier.avif" alt="Accessories" class="filter-img">
            <div class="filter-name">Accessories</div>
        </div>
        </div>

	        <!-- Product -->
            <!-- Women Bag Category -->
            <div class="category-container" data-category="1">
                <section class="section-slide">
                    <div class="wrap-slick1">
                        <div class="slick1">
                            <div class="item-slick1" style="background-image: url(images/LV_Women.jpg);">
                                <div class="container h-full">
                                    <div class="flex-col-l-m h-full p-t-100 p-b-30 respon5">
                                        <div class="layer-slick1 animated visible-false" data-appear="fadeInDown" data-delay="0">
                                            <span class="ltext-101 cl2 respon2" style="color: white; text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);">
                                                Women Collection 2025
                                            </span>
                                        </div>
                                            
                                        <div class="layer-slick1 animated visible-false" data-appear="fadeInUp" data-delay="800">
                                            <h2 class="ltext-201 cl2 p-t-19 p-b-43 respon1" style="color: white; text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);">
                                                NEW SEASON SALES
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>    
                <div class="row isotope-grid">
                    <?php
                    // Display products dynamically
                    $promotion_result_women = $connect->query("SELECT * FROM promotion_product WHERE category_id = 1");
                    if ($promotion_result_women->num_rows > 0) {
                        while ($promotion = $promotion_result_women->fetch_assoc()) {
                            $promotion_id = $promotion['promotion_id'];

                            // Get total stock for the product from product_variant table
                            $variant_query = "SELECT * FROM product_variant WHERE promotion_id = $promotion_id";
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

                            $isUnavailable = $promotion['promotion_status'] == 2;
                            $productStyle = $isUnavailable || $isOutOfStock ? 'unavailable-product' : '';

                            $message = '';
                            if ($isUnavailable) {
                                $message = '<p style="color: red; font-weight: bold;">Product is unavailable</p>';
                            } elseif ($isOutOfStock) {
                                $message = '<p style="color: red; font-weight: bold;">Product is out of stock</p>';
                            }

                            echo '<div class="col-sm-6 col-md-4 col-lg-3 p-b-35 isotope-item category-' . $promotion['category_id'] . '"style="margin-right: -30px;">
                                    <div class="block2 ' . $productStyle . '">
                                        <div class="block2-pic hov-img0" >
                                            <img src="images/' . $promotion['promotion_image'] . '" alt="IMG-PRODUCT" id="product-image-' . $promotion_id . '">
                                            <a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1" 
                                                data-id="' . $promotion['promotion_id'] . '"' . ($isUnavailable || $isOutOfStock ? 'style="pointer-events: none; opacity: 0.5;"' : '') . '>Quick View</a>
                                            <!-- Heart icon moved to top-right of the image -->
                                            <a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2" style="position: absolute; top: 10px; right: 10px;">
                                                <img class="icon-heart1 dis-block trans-04" src="images/icons/icon-heart-01.png" alt="ICON">
                                                <img class="icon-heart2 dis-block trans-04 ab-t-l" src="images/icons/icon-heart-02.png" alt="ICON">
                                            </a>
                                        </div>
                                        <div class="block2-txt flex-w flex-t p-t-14">
                                            <div class="block2-txt-child1 flex-col-l ">
                                                <a href="product-detail.php?id=' . $promotion['promotion_id'] . '&type=promotion" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6"' . ($isUnavailable || $isOutOfStock ? 'style="pointer-events: none; opacity: 0.5;"' : '') . '>'
                                                . $promotion['promotion_name'] . 
                                                '</a>
                                                <span class="stext-105 cl3">$' . $promotion['promotion_price'] . '</span>
                                                ' . $message . '
                                            </div>  
                                            <!-- Color circles placed here -->
                                            <div class="block2-txt-child2 flex-r p-t-3">';
                                            foreach ($colors as $index => $color) {
                                                $iconClass = strtolower($color['color']) === 'white' ? 'zmdi-circle-o' : 'zmdi-circle';
                                                $styleColor = strtolower($color['color']) === 'white' ? '#aaa' : $color['color'];
                                                echo '<span class="fs-15 lh-12 m-r-6 color-circle" style="color: ' . $styleColor . '; cursor: pointer;" 
                                                        data-image="images/' . $color['image'] . '" data-product-id="' . $promotion_id . '">
                                                        <i class="zmdi ' . $iconClass . '"></i>
                                                    </span>';
                                            }
                                echo '</div>
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

            <!-- Men Bag Category -->
            <div class="category-container" data-category="2">
                <section class="section-slide">
                    <div class="wrap-slick1">
                        <div class="slick1">
                            <div class="item-slick1" style="background-image: url(images/LV_PMen.webp);">
                                <div class="container h-full">
                                    <div class="flex-col-l-m h-full p-t-100 p-b-30 respon5">
                                        <div class="layer-slick1 animated visible-false" data-appear="fadeInDown" data-delay="0">
                                            <span class="ltext-101 cl2 respon2" style="color: white; text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);">
                                                Men Fashion Bags 2025
                                            </span>
                                        </div>
                                            
                                        <div class="layer-slick1 animated visible-false" data-appear="fadeInUp" data-delay="800">
                                            <h2 class="ltext-201 cl2 p-t-19 p-b-43 respon1" style="color: white; text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);">
                                                New HANDBAGS
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>   
                <div class="row isotope-grid">
                <?php
                // Display products dynamically
                    $promotion_result_men = $connect->query("SELECT * FROM promotion_product WHERE category_id = 2");
                    if ($promotion_result_men->num_rows > 0) {
                        while ($promotion = $promotion_result_men->fetch_assoc()) {
                            $promotion_id = $promotion['promotion_id'];
                
                            // Get total stock for the product from product_variant table
                            $variant_query = "SELECT * FROM product_variant WHERE promotion_id = $promotion_id";
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
                
                            $isUnavailable = $promotion['promotion_status'] == 2;
                            $productStyle = $isUnavailable || $isOutOfStock ? 'unavailable-product' : '';
                
                            $message = '';
                            if ($isUnavailable) {
                                $message = '<p style="color: red; font-weight: bold;">Product is unavailable</p>';
                            } elseif ($isOutOfStock) {
                                $message = '<p style="color: red; font-weight: bold;">Product is out of stock</p>';
                            }
                
                            echo '<div class="col-sm-6 col-md-4 col-lg-3 p-b-35 isotope-item category-' . $promotion['category_id'] . '"style="margin-right: -30px;">
                                    <div class="block2 ' . $productStyle . '">
                                        <div class="block2-pic hov-img0" >
                                            <img src="images/' . $promotion['promotion_image'] . '" alt="IMG-PRODUCT" id="product-image-' . $promotion_id . '">
                                            <a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1" 
                                                data-id="' . $promotion['promotion_id'] . '"' . ($isUnavailable || $isOutOfStock ? 'style="pointer-events: none; opacity: 0.5;"' : '') . '>Quick View
                                            </a>
                                            <!-- Heart icon moved to top-right of the image -->
                                            <a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2" style="position: absolute; top: 10px; right: 10px;">
                                                <img class="icon-heart1 dis-block trans-04" src="images/icons/icon-heart-01.png" alt="ICON">
                                                <img class="icon-heart2 dis-block trans-04 ab-t-l" src="images/icons/icon-heart-02.png" alt="ICON">
                                            </a>
                                        </div>
                                        <div class="block2-txt flex-w flex-t p-t-14">
                                            <div class="block2-txt-child1 flex-col-l ">
                                                <a href="product-detail.php?id=' . $promotion['promotion_id'] . '&type=promotion" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6"' . ($isUnavailable || $isOutOfStock ? 'style="pointer-events: none; opacity: 0.5;"' : '') . '>'
                                                . $promotion['promotion_name'] . 
                                                '</a>
                                                <span class="stext-105 cl3">$' . $promotion['promotion_price'] . '</span>
                                                ' . $message . '
                                            </div>
                                            <div class="block2-txt-child2 flex-r p-t-3">';
                                    
                            // Display color circles
                            foreach ($colors as $index => $color) {
                                $iconClass = strtolower($color['color']) === 'white' ? 'zmdi-circle-o' : 'zmdi-circle';
                                $styleColor = strtolower($color['color']) === 'white' ? '#aaa' : $color['color'];
                                echo '<span class="fs-15 lh-12 m-r-6 color-circle" style="color: ' . $styleColor . '; cursor: pointer;" 
                                        data-image="images/' . $color['image'] . '" data-product-id="' . $promotion_id . '">
                                        <i class="zmdi ' . $iconClass . '"></i>
                                    </span>';
                            }

                            echo '</div>
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

            <!-- Accessories Category -->
            <div class="category-container" data-category="3">
                <section class="section-slide">
                    <div class="wrap-slick1">
                        <div class="slick1">
                            <div class="item-slick1" style="background-image: url(images/LV_AC.webp);">
                                <div class="container h-full">
                                    <div class="flex-col-l-m h-full p-t-100 p-b-30 respon5">
                                        <div class="layer-slick1 animated visible-false" data-appear="fadeInDown" data-delay="0">
                                            <span class="ltext-101 cl2 respon2" style="color: white; text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);">
                                                Accessories Collection 2025
                                            </span>
                                        </div>
                                            
                                        <div class="layer-slick1 animated visible-false" data-appear="fadeInUp" data-delay="800">
                                            <h2 class="ltext-201 cl2 p-t-19 p-b-43 respon1" style="color: white; text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);">
                                                NEW Offers!!
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>   
                <div class="row isotope-grid">
                    <?php
                    // Display products dynamically
                        $promotion_result_ac = $connect->query("SELECT * FROM promotion_product WHERE category_id = 3");
                        if ($promotion_result_ac->num_rows > 0) {
                            while ($promotion = $promotion_result_ac->fetch_assoc()) {
                                $promotion_id = $promotion['promotion_id'];
                    
                                // Get total stock for the product from product_variant table
                                $variant_query = "SELECT * FROM product_variant WHERE promotion_id = $promotion_id";
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
                    
                                $isUnavailable = $promotion['promotion_status'] == 2;
                                $productStyle = $isUnavailable || $isOutOfStock ? 'unavailable-product' : '';
                    
                                $message = '';
                                if ($isUnavailable) {
                                    $message = '<p style="color: red; font-weight: bold;">Product is unavailable</p>';
                                } elseif ($isOutOfStock) {
                                    $message = '<p style="color: red; font-weight: bold;">Product is out of stock</p>';
                                }
                    
                                echo '<div class="col-sm-6 col-md-4 col-lg-3 p-b-35 isotope-item category-' . $promotion['category_id'] . '" style="margin-right: -30px;">
                                        <div class="block2 ' . $productStyle . '">
                                            <div class="block2-pic hov-img0" >
                                                <img src="images/' . $promotion['promotion_image'] . '" alt="IMG-PRODUCT" id="product-image-' . $promotion_id . '">
                                                <a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1" 
                                                    data-id="' . $promotion['promotion_id'] . '"' . ($isUnavailable || $isOutOfStock ? 'style="pointer-events: none; opacity: 0.5;"' : '') . '>Quick View
                                                </a>
                                                <a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2" style="position: absolute; top: 10px; right: 10px;">
                                                    <img class="icon-heart1 dis-block trans-04" src="images/icons/icon-heart-01.png" alt="ICON">
                                                    <img class="icon-heart2 dis-block trans-04 ab-t-l" src="images/icons/icon-heart-02.png" alt="ICON">
                                                </a>
                                            </div>
                                            <div class="block2-txt flex-w flex-t p-t-14">
                                                <div class="block2-txt-child1 flex-col-l ">
                                                    <a href="product-detail.php?id=' . $promotion['promotion_id'] . '&type=promotion" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6"' . ($isUnavailable || $isOutOfStock ? 'style="pointer-events: none; opacity: 0.5;"' : '') . '>'
                                                    . $promotion['promotion_name'] . 
                                                    '</a>
                                                    <span class="stext-105 cl3">$' . $promotion['promotion_price'] . '</span>
                                                    ' . $message . '
                                                </div>
                                                <div class="block2-txt-child2 flex-r p-t-3">';
                                        
                                // Display color circles
                                foreach ($colors as $index => $color) {
                                    $iconClass = strtolower($color['color']) === 'white' ? 'zmdi-circle-o' : 'zmdi-circle';
                                    $styleColor = strtolower($color['color']) === 'white' ? '#aaa' : $color['color'];
                                    echo '<span class="fs-15 lh-12 m-r-6 color-circle" style="color: ' . $styleColor . '; cursor: pointer;" 
                                            data-image="images/' . $color['image'] . '" data-product-id="' . $promotion_id . '">
                                            <i class="zmdi ' . $iconClass . '"></i>
                                        </span>';
                                }

                                echo '</div>
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
Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved |Made with <i class="fa fa-heart-o" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Colorlib</a> &amp; distributed by <a href="https://themewagon.com" target="_blank">ThemeWagon</a>
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
							<?php echo $promotion['promotion_name']; ?>
						</h4>

						<span class="mtext-106 cl2">
							<?php echo $promotion['promotion_price']; ?>
						</span>

						<p class="stext-102 cl3 p-t-23">
							<?php echo $promotion['promotion_des']; ?>
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
							</div>	
                            <p class="stock-warning" style="color: red; display: none;"></p>
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

<!-- Retain only one -->
<script src="vendor/slick/slick.min.js"></script>
<script src="vendor/slick/slick.min.js"></script>
<script src="js/slick-custom.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
<script src="vendor/daterangepicker/moment.min.js"></script>
<script src="vendor/daterangepicker/daterangepicker.js"></script>
<script src="vendor/slick/slick.min.js"></script>
<script src="js/slick-custom.js"></script>
<script src="vendor/parallax100/parallax100.js"></script>
<script>
    $('.parallax100').parallax100();
</script>
<script src="vendor/MagnificPopup/jquery.magnific-popup.min.js"></script>
<script>
    $('.gallery-lb').each(function() {
        $(this).magnificPopup({
            delegate: 'a',
            type: 'image',
            gallery: {
                enabled:true
            },
            mainClass: 'mfp-fade'
        });
    });
</script>
<script src="vendor/isotope/isotope.pkgd.min.js"></script>
<script src="vendor/sweetalert/sweetalert.min.js"></script>
<script>
    $('.js-addwish-b2').each(function(){
        var nameProduct = $(this).parent().parent().find('.js-name-b2').html();
        $(this).on('click', function(){
            swal(nameProduct, "is added to wishlist !", "success");

            $(this).addClass('js-addedwish-b2');
            $(this).off('click');
        });
    });
</script>
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
   $(document).on('click', '.js-show-modal1', function(event) {
    event.preventDefault();
    var promotionID = $(this).data('id'); // Correct variable name

    // Fetch product and variant details
    $.ajax({
        url: '', // Specify the correct PHP file
        type: 'GET',
        data: { fetch_promotion: true, id: promotionID }, // Correct variable name
        dataType: 'json',
        success: function(response) {
            if (response) {
                // Populate modal details
                $('.js-name-detail').data('id', promotionID);
                $('.js-name-detail').text(response.promotion_name);
                $('.mtext-106').text('$' + response.promotion_price);
                $('.stext-102').text(response.promotion_des);

                // Fetch variants for the product
                fetchVariants(promotionID, response);
                
                // Show modal
                $('.js-modal1').addClass('show-modal1');
            } else {
                alert('Product details not found.');
            }
        },
        error: function(xhr) {
            console.error('Error fetching product details:', xhr.responseText);
            alert('An error occurred while fetching product details.');
        }
    });
});

function fetchVariants(promotionId, promotionResponse) {
    console.log("Fetching variants for promotion ID:", promotionId);
    console.log("Product Response:", promotionResponse);
    $.ajax({
        url: '', // Correct PHP file
        type: 'GET',
        data: { fetch_variants: true, promotion_id: promotionId },
        dataType: 'json',
        success: function(variants) {
            window.promotionVariants = variants;
            if (variants && variants.length > 0) {
                var defaultVariant = variants.reduce((lowest, current) =>
                    current.variant_id < lowest.variant_id ? current : lowest
                );

                updateQuickViewImages(defaultVariant);

                var colorSelect = $('select[name="color"]');
                colorSelect.empty();
                colorSelect.append('<option value="">Choose an option</option>');
                var uniqueColors = [...new Set(variants.map(v => v.color))];
                uniqueColors.forEach(color => {
                    colorSelect.append('<option value="' + color + '">' + color + '</option>');
                });

                colorSelect.on('change', function() {
                    var selectedColor = $(this).val();
                    if (selectedColor) {
                        var variant = variants.find(v => v.color === selectedColor);
                        if (variant) {
                            updateQuickViewImages(variant);
                            $('.size-display').text('Size: ' + variant.size);
                        }
                    }
                });

                $('.size-display').text('Size: ' + defaultVariant.size);
            } else {
                alert('No variants found for this product.');
            }
        },
        error: function(xhr) {
            console.error('Error fetching variants:', xhr.responseText);
            alert('An error occurred while fetching product variants.');
        }
    });
}


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
    if (!window.promotionVariants || !selectedColor) return 0;

    // Find the variant matching the selected color
    const matchingVariant = window.promotionVariants.find(variant => variant.color === selectedColor);

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
            $input.val(currentVal --);
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

// Add to cart functionality
$(document).on('click', '.js-addcart-detail', function (event) {
    event.preventDefault();

    const productId = $('.js-name-detail').data('id'); // Product ID
    const productName = $('.js-name-detail').text(); // Product Name
    const productPrice = parseFloat($('.mtext-106').text().replace('$', '')); // Product Price
    const productQuantity = parseInt($('.num-product').val()); // Quantity
    const selectedColor = $('select[name="color"]').val(); // Selected Color

    // Validate color selection
    if (!selectedColor) {
        Swal.fire({
            title: 'Color Required!',
            text: 'Please select a color for the product.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Find the matching variant
    const matchingVariant = window.promotionVariants.find(variant => variant.color === selectedColor);

    if (!matchingVariant) {
        Swal.fire({
            title: 'Invalid Variant!',
            text: 'No size or variant found for the selected color.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    const variantId = matchingVariant.variant_id;
    const productStock = parseInt(matchingVariant.stock || 0);

    // Validate stock
    if (productQuantity > productStock) {
        Swal.fire({
            title: 'Stock Limit Exceeded!',
            text: `Only ${productStock} items are available for the selected color.`,
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    if (productQuantity === 0) {
        $('.stock-warning').text('Quantity cannot be zero.').show();
        return;
    }

    // Calculate total price
    const totalPrice = productPrice * productQuantity;

    // Add to cart AJAX request
    $.ajax({
        url: '', // PHP endpoint
        type: 'POST',
        data: {
            add_to_cart: true,
            variant_id: variantId,
            qty: productQuantity,
            total_price: totalPrice,
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                // Ensure Swal is shown only once
                Swal.fire({
                    title: 'Product added to cart!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Reload the page after the Swal dialog is closed
                    location.reload();
                });
            } else {
                alert('Failed to add product to cart: ' + (response.error || 'unknown error'));
            }

            // Reset modal state
            $('.js-modal1').removeClass('show-modal1');
            updateCart(); // Ensure cart is updated
        },
        error: function (xhr, status, error) {
            console.error("Add to Cart Error:", xhr.responseText);
            alert('An error occurred while adding to the cart.');
        }
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
<script>
// Delegate event to dynamically loaded elements
document.addEventListener("click", function (event) {
    // Check if the clicked element or its parent has the class "color-circle"
    if (event.target.closest(".color-circle")) {
        var circle = event.target.closest(".color-circle");

        // Retrieve necessary attributes
        var newImage = circle.getAttribute("data-image");
        var productId = circle.getAttribute("data-product-id");

        // Log for debugging
        console.log("Circle clicked!");
        console.log("Product ID:", productId);
        console.log("New Image Path:", newImage);

        // Find the product image element
        var productImageElement = document.getElementById("product-image-" + productId);

        // Check if the product image element exists and update the image
        if (productImageElement && newImage) {
            console.log("Updating product image for Product ID:", productId);
            productImageElement.setAttribute("src", newImage);
        } else {
            console.log("Product image element not found or new image path missing.");
        }
    }
});
</script>
<script>
    // Get all filter items and category containers
const filterItems = document.querySelectorAll('.filter-item');
const categoryContainers = document.querySelectorAll('.category-container');

// Add click event listener to each filter item
filterItems.forEach(item => {
    item.addEventListener('click', () => {
        const category = item.getAttribute('data-category');

        // Remove active class from all items
        filterItems.forEach(itm => itm.classList.remove('active'));

        // Add active class to the clicked item
        item.classList.add('active');

        // Hide all category containers and show only the matching one
        if (category === 'all') {
            categoryContainers.forEach(container => container.classList.add('active'));
        } else {
            categoryContainers.forEach(container => {
                if (container.getAttribute('data-category') === category) {
                container.classList.add('active');
                } else {
                container.classList.remove('active');
                }
            });
        }

        // Trigger resize and Slick.js recalculation (if applicable)
        window.dispatchEvent(new Event('resize'));
        if (typeof $('.slick1').slick === 'function') {
            $('.slick1').slick('setPosition');
        }
    });
});

// Show the first category by default
if (filterItems.length > 0) {
    filterItems[0].click();
}


</script>
<script src="js/main.js"></script>

</body>
</html>

<?php
// Close the connection
$connect->close();
?>
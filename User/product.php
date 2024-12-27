<?php
session_start(); // Start the session

// Include the database connection file
include("dataconnection.php"); 

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}
$currency = isset($_SESSION['currency']) ? $_SESSION['currency'] : 'aus'; // 默认 USD
$currency_field = 'product_price_' . strtolower($currency); // 动态选择数据库字段


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


if (!$result) {
    die("Query failed: " . $connect->error);
}

while ($row = $result->fetch_assoc()) {
    $price = isset($row['product_price']) ? $row['product_price'] : 0.00;
    echo '<div class="product">';
    echo '<h3>' . htmlspecialchars($row['product_name']) . '</h3>';
    echo '<p>Price: ' . number_format($price, 2) . ' ' . htmlspecialchars($currency) . '</p>';
    echo '</div>';
}
// Fetch and combine cart items for the logged-in user where the product_id is the same
$cart_items_query = "
    SELECT 
        sc.product_id, 
        p.product_name, 
        p.product_image, 
        $currency_field AS product_price,
        sc.color, 
        sc.size, 
        SUM(sc.qty) AS total_qty, 
        SUM(sc.total_price) AS total_price,
        sc.package_id,
        sc.package_qty,
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
        sc.package_id";
$cart_items_result = $connect->query($cart_items_query);

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

if (isset($_GET['fetch_packages']) && isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    $query = "
        SELECT 
            p.package_id, 
            p.package_name, 
            p.package_price, 
            p.package_description, 
            p.package_image,
            p.package_stock
        FROM product_package p
        LEFT JOIN product prod1 ON p.product1_id = prod1.product_id
        LEFT JOIN product prod2 ON p.product2_id = prod2.product_id
        LEFT JOIN product prod3 ON p.product3_id = prod3.product_id
        WHERE p.product1_id = $product_id 
           OR p.product2_id = $product_id 
           OR p.product3_id = $product_id";

    $result = $connect->query($query);

    $packages = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $packages[] = $row;
        }
    }

    echo json_encode($packages);
    exit;
}

$packages = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }
}
if (isset($_GET['fetch_package_products'])) {
    $packageId = intval($_GET['package_id']);
    $query = "SELECT product1_id, product2_id, product3_id FROM product_package WHERE package_id = $packageId";
    $result = $connect->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        $productIds = [$row['product1_id'], $row['product2_id'], $row['product3_id']];
        $productIds = array_filter($productIds); // Remove null values

        $productQuery = "SELECT product_id, product_name, product_image, color1, color2, size1, size2 
                         FROM product WHERE product_id IN (" . implode(',', $productIds) . ")";
        $productResult = $connect->query($productQuery);

        $products = [];
        while ($product = $productResult->fetch_assoc()) {
            $products[] = $product;
        }

        echo json_encode(['products' => $products]);
    } else {
        echo json_encode(['error' => 'No products found for the package.']);
    }
    exit;
}
if (isset($_POST['add_package_to_cart'])) {
    header('Content-Type: application/json');
    $user_id = $_SESSION['id'];
    $package_id = intval($_POST['package_id']);
    $package_qty = intval($_POST['qty']);

    $product1_color = htmlspecialchars($_POST['color_1'] ?? '');
    $product1_size = htmlspecialchars($_POST['size_1'] ?? '');
    $product2_color = htmlspecialchars($_POST['color_2'] ?? '');
    $product2_size = htmlspecialchars($_POST['size_2'] ?? '');
    $product3_color = htmlspecialchars($_POST['color_3'] ?? '');
    $product3_size = htmlspecialchars($_POST['size_3'] ?? '');

    $stmt = $connect->prepare("SELECT package_price FROM product_package WHERE package_id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $connect->error]);
        exit;
    }
    $stmt->bind_param('i', $package_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid package selected.']);
        exit;
    }

    $row = $result->fetch_assoc();
    $package_price = doubleval($row['package_price']);
    $stmt->close();

    $total_price = $package_price * $package_qty;

    $stmt = $connect->prepare("
        INSERT INTO shopping_cart (
            user_id, total_price, package_id, 
            product1_color, product1_size, 
            product2_color, product2_size, 
            product3_color, product3_size, 
            package_qty
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $connect->error]);
        exit;
    }
    $stmt->bind_param(
        'idissssssi',
        $user_id,
        $total_price,
        $package_id,
        $product1_color,
        $product1_size,
        $product2_color,
        $product2_size,
        $product3_color,
        $product3_size,
        $package_qty
    );

    $response = [
        'success' => true,
        'message' => 'Package added to cart successfully.'
    ];

    echo json_encode($response); // Only output JSON
    exit;
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


$selected_category = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
// Fetch categories
$category_query = "SELECT * FROM category";
$category_result = $connect->query($category_query);

// Count distinct product IDs in the shopping cart for the logged-in user
$distinct_products_query = "SELECT COUNT(DISTINCT product_id) AS distinct_count FROM shopping_cart WHERE user_id = $user_id";
$distinct_products_result = $connect->query($distinct_products_query);
$distinct_count = 0;

if ($distinct_products_result) {
    $row = $distinct_products_result->fetch_assoc();
    $distinct_count = $row['distinct_count'] ?? 0;
}


// Fetch products
$product_query = "SELECT * FROM product WHERE 1";

$product_result = $connect->query($product_query);
// Fetch products based on filters and search
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$price_filter = isset($_GET['price']) ? explode(',', $_GET['price']) : [];
$color_filter = isset($_GET['color']) ? explode(',', $_GET['color']) : [];
$tag_filter = isset($_GET['tag']) ? explode(',', $_GET['tag']) : [];
$category_filter = isset($_GET['category']) && $_GET['category'] !== 'all' ? intval($_GET['category']) : null;

// Base query to fetch products
$product_query = "SELECT * FROM product WHERE product_name LIKE '%$search_query%'";

// Apply category filter if it's not 'all'
if ($category_filter) {
    $product_query .= " AND category_id = $category_filter";
}
// Apply category filter if a valid category_id is provided
if ($selected_category !== null) {
    $product_query .= " AND category_id = $selected_category";
}
// Apply price filter if it's not 'all'
if (!empty($price_filter) && $price_filter[0] !== 'all') {
    $price_conditions = [];
    foreach ($price_filter as $range) {
        switch ($range) {
            case '0-2000':
                $price_conditions[] = "product_price BETWEEN 0 AND 2000";
                break;
            case '2000-3000':
                $price_conditions[] = "product_price BETWEEN 2000 AND 3000";
                break;
            case '3000-4000':
                $price_conditions[] = "product_price BETWEEN 3000 AND 4000";
                break;
            case '4000-5000':
                $price_conditions[] = "product_price BETWEEN 4000 AND 5000";
                break;
            case '5000+':
                $price_conditions[] = "product_price > 5000";
                break;
        }
    }
    if ($price_conditions) {
        $product_query .= " AND (" . implode(" OR ", $price_conditions) . ")";
    }
}

// Apply color filter if it's not 'all'
if (!empty($color_filter) && $color_filter[0] !== 'all') {
    $color_conditions = array_map(function ($color) {
        return "(color1 = '$color' OR color2 = '$color')";
    }, $color_filter);
    $product_query .= " AND (" . implode(" OR ", $color_conditions) . ")";
}

// Apply tag filter if it's not 'all'
if (!empty($tag_filter) && $tag_filter[0] !== 'all') {
    $tag_conditions = array_map(function ($tag) {
        return "tags LIKE '%$tag%'";
    }, $tag_filter);
    $product_query .= " AND (" . implode(" OR ", $tag_conditions) . ")";
}

$product_result = $connect->query($product_query);

// Render filtered products as HTML for AJAX response
if (isset($_GET['price']) || isset($_GET['color']) || isset($_GET['tag']) || isset($_GET['category'])) {
    ob_start();
	if ($product_result->num_rows > 0) {
    while ($product = $product_result->fetch_assoc()) {
        echo '<div class="col-sm-6 col-md-4 col-lg-3 p-b-35 isotope-item category-' . $product['category_id'] . '">
                <div class="block2">
                    <div class="block2-pic hov-img0">
                        <img src="images/' . $product['product_image'] . '" alt="IMG-PRODUCT">
                        <a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1" 
                            data-id="' . $product['product_id'] . '">Quick View</a>
                    </div>
                    <div class="block2-txt flex-w flex-t p-t-14">
                        <div class="block2-txt-child1 flex-col-l ">
                            <a href="product-detail.php?id=' . $product['product_id'] . '" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">'
                            . $product['product_name'] . 
                            '</a>
<span class="stext-105 cl3">' 
                                . strtoupper($currency) . ' ' . number_format($product_price, 2) .  
                                '</span>                        </div>
                        <div class="block2-txt-child2 flex-r p-t-3">
                            <a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2">
                                <img class="icon-heart1 dis-block trans-04" src="images/icons/icon-heart-01.png" alt="ICON">
                                <img class="icon-heart2 dis-block trans-04 ab-t-l" src="images/icons/icon-heart-02.png" alt="ICON">
                            </a>
                        </div>
                    </div>
                </div>
              </div>';
    }
    echo ob_get_clean();
    exit;
} else {
	echo "<p>No products found.</p>";
}
}
$output = ob_get_clean(); // Get any unexpected output
if (!empty($output)) {
    error_log("Unexpected output: $output"); // Log unexpected output for debugging
}
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

<style>
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

/* General Container Styling */
#packageBox {
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 8px;
        max-width: 800px;
        margin: 0 auto;
    }

    /* Section Title */
    .package-title {
        text-align: center;
        font-size: 24px;
        color: #333;
        margin-bottom: 20px;
    }

    /* Package Card Styling */
    .package-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-bottom: 20px;
        background-color: #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .package-card:hover {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    /* Image Styling */
    .p-image {
        max-width: 80px;
        height: auto;
        border-radius: 5px;
        margin-right: 15px;
    }

    /* Package Info */
    .package-info {
        flex-grow: 1;
        padding-left: 15px;
    }

    .package-name {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
        color: #555;
    }

    .package-price {
        font-size: 16px;
        color: #666;
        margin-bottom: 10px;
    }

    /* Quantity Controls */
    .qty-controls {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .qty-btn {
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        border-radius: 4px;
        font-size: 16px;
    }

    .qty-btn:hover {
        background-color: #0056b3;
    }

    .qty-input {
        width: 50px;
        text-align: center;
        margin: 0 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px;
    }

    /* Select Button */
    .btn-primary {
        background-color: #28a745;
        color: #fff;
        border: none;
        padding: 10px 20px;
        font-size: 14px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #218838;
    }

    /* Responsive Design */
    @media (max-width: 600px) {
        .package-card {
            flex-direction: column;
            align-items: center;
        }

        .p-image {
            margin-right: 0;
            margin-bottom: 10px;
        }

        .package-info {
            padding-left: 0;
            text-align: center;
        }
    }
/* Fullscreen overlay */
.popup-overlay {
    display: none; /* Hidden by default */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
    z-index: 9999; /* Ensures it appears above other content */
}

/* Popup content box */
.popup-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    max-width: 90%;
    width: 500px;
}

/* Close button */
.close-popup {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 20px;
    font-weight: bold;
    color: #aaa;
    cursor: pointer;
}

.close-popup:hover {
    color: #000;
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
						<a href="faq.php" class="flex-c-m trans-04 p-lr-25">
							Help & FAQs
						</a>

						

						<a href="#" class="flex-c-m trans-04 p-lr-25">
							EN
						</a>

						<div class="currency-switcher">
    <form id="currency-form" method="post" action="currency_switch.php">
        <select name="currency" id="currency-selector" onchange="document.getElementById('currency-form').submit();">
            <option value="AUS" <?= $_SESSION['currency'] == 'AUS' ? 'selected' : '' ?>>Australian Dollar (AUS)</option>
            <option value="MYR" <?= $_SESSION['currency'] == 'MYR' ? 'selected' : '' ?>>Malaysian Ringgit (MYR)</option>
            <option value="SGD" <?= $_SESSION['currency'] == 'SGD' ? 'selected' : '' ?>>Singapore Dollar (SGD)</option>
        </select>
    </form>
</div>




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
								<ul class="sub-menu">
									<li><a href="index.html">Homepage 1</a></li>
									<li><a href="home-02.html">Homepage 2</a></li>
									<li><a href="home-03.html">Homepage 3</a></li>
								</ul>
							</li>

							<li class="active-menu">
								<a href="product.php">Shop</a>
							</li>

							<li class="label1" data-label1="hot">
								<a href="voucher_page.php">Voucher</a>
							</li>

							<li>
								<a href="blog.html">Blog</a>
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
						<div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 js-show-modal-search">
							<i class="zmdi zmdi-search"></i>
						</div>

						<div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti js-show-cart" data-notify="<?php echo $distinct_count; ?>">
							<i class="zmdi zmdi-shopping-cart"></i>
						</div>

						<a href="#" class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti" >
							<i class="zmdi zmdi-favorite-outline"></i>
						</a>
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
                    // Display combined cart items
                    $total_price = 0;

                    if ($cart_items_result->num_rows > 0) {
                        while ($cart_item = $cart_items_result->fetch_assoc()) {
                            $total_price += $cart_item['total_price'];
                            if (!empty($cart_item['package_id'])) {
                                // Render package details
                                echo '
                                <li class="header-cart-item flex-w flex-t m-b-12">
                                    <div class="header-cart-item-img">
                                        <img src="images/' . $cart_item['package_image'] . '" alt="IMG">
                                    </div>
                                    <div class="header-cart-item-txt p-t-8">
                                        <a href="package-detail.php?id=' . $cart_item['package_id'] . '" class="header-cart-item-name m-b-18 hov-cl1 trans-04">
                                            ' . $cart_item['package_name'] . '
                                        </a>
                                        <span class="header-cart-item-info">
                                            ' . $cart_item['package_qty'] . ' x $' . number_format($cart_item['total_price'], 2) . '
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
                                    <div class="header-cart-item-img">
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


	
	<!-- Product -->
	<div class="bg0 m-t-23 p-b-140">
   		<div class="container">
        	<div class="flex-w flex-sb-m p-b-52">
            	<div class="flex-w flex-l-m filter-tope-group m-tb-10">
					<button class="stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5 <?= $selected_category === null ? 'how-active1' : '' ?>" data-filter="*">
        				All Products
    				</button>

                	<?php
						// Display categories dynamically
						if ($category_result->num_rows > 0) {
							while ($row = $category_result->fetch_assoc()) {
								$isActive = ($selected_category === intval($row['category_id'])) ? 'how-active1' : '';
								echo '<button class="stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5 ' . $isActive . '" 
										data-filter=".category-' . $row['category_id'] . '">'
									. $row['category_name'] .
									'</button>';
							}
						}
                	?>
            	</div>
        	

				<div class="flex-w flex-c-m m-tb-10">
        			<div class="flex-c-m stext-106 cl6 size-104 bor4 pointer hov-btn3 trans-04 m-r-8 m-tb-4 js-show-filter">
            			<i class="icon-filter cl2 m-r-6 fs-15 trans-04 zmdi zmdi-filter-list"></i>
            			<i class="icon-close-filter cl2 m-r-6 fs-15 trans-04 zmdi zmdi-close dis-none"></i>
            		 	 Filter
        			</div>

        			<div class="flex-c-m stext-106 cl6 size-105 bor4 pointer hov-btn3 trans-04 m-tb-4 js-show-search">
            			<i class="icon-search cl2 m-r-6 fs-15 trans-04 zmdi zmdi-search"></i>
            			<i class="icon-close-search cl2 m-r-6 fs-15 trans-04 zmdi zmdi-close dis-none"></i>
            			 Search
        			</div>
    			</div>

   				<!-- Search product -->
    			<div class="dis-none panel-search w-full p-t-10 p-b-15">
        			<form method="GET" action="">
            			<div class="bor8 dis-flex p-l-15">
                			<button class="size-113 flex-c-m fs-16 cl2 hov-cl1 trans-04">
                    			<i class="zmdi zmdi-search"></i>
                			</button>
                			<input class="mtext-107 cl2 size-114 plh2 p-r-15" type="text" name="search" placeholder="Search product">
            			</div>  
        			</form>
    			</div>

    			<!-- Filter panel -->
    			<div class="dis-none panel-filter w-full p-t-10">
					<div class="wrap-filter flex-w bg6 w-full p-lr-40 p-t-27 p-lr-15-sm">	
						<div class="filter-col2 p-r-15 p-b-27">
							<div class="mtext-102 cl2 p-b-15">
								Price
							</div>

							<ul>
								<li class="p-b-6">
									<a href="#" class="filter-link stext-106 trans-04" data-filter="price" data-value="all">
										All
									</a>
								</li>

								<li class="p-b-6">
									<a href="#" class="filter-link stext-106 trans-04" data-filter="price" data-value="0-2000">
										$0.00 - $2000.00
									</a>
								</li>

								<li class="p-b-6">
									<a href="#" class="filter-link stext-106 trans-04" data-filter="price" data-value="2000-3000">
										$2000.00 - $3000.00
									</a>
								</li>

								<li class="p-b-6">
									<a href="#" class="filter-link stext-106 trans-04" data-filter="price" data-value="3000-4000">
										$3000.00 - $4000.00
									</a>
								</li>

								<li class="p-b-6">
									<a href="#" class="filter-link stext-106 trans-04" data-filter="price" data-value="4000-5000">
										$4000.00 - $5000.00
									</a>
								</li>

								<li class="p-b-6">
									<a href="#" class="filter-link stext-106 trans-04" data-filter="price" data-value="5000+">
										$5000.00+
									</a>
								</li>
							</ul>
						</div>

						<div class="filter-col3 p-r-15 p-b-27">
							<div class="mtext-102 cl2 p-b-15">
								Color
							</div>

							<ul>
								<li class="p-b-6">
									<span class="fs-15 lh-12 m-r-6" style="color: #222;">
										<i class="zmdi zmdi-circle"></i>
									</span>

									<a href="#" class="filter-link stext-106 trans-04" data-filter="color" data-value="black">
										Black
									</a>
								</li>


								<li class="p-b-6">
									<span class="fs-15 lh-12 m-r-6" style="color: #b3b3b3;">
										<i class="zmdi zmdi-circle"></i>
									</span>

									<a href="#" class="filter-link stext-106 trans-04" data-filter="color" data-value="grey">
										Grey
									</a>
								</li>

		
								<li class="p-b-6">
									<span class="fs-15 lh-12 m-r-6" style="color: #f5deb3;">
										<i class="zmdi zmdi-circle"></i>
									</span>

									<a href="#" class="filter-link stext-106 trans-04" data-filter="color" data-value="beige">
										Beige
									</a>
								</li>

								<li class="p-b-6">
									<span class="fs-15 lh-12 m-r-6" style="color: #aaa;">
										<i class="zmdi zmdi-circle-o"></i>
									</span>

									<a href="#" class="filter-link stext-106 trans-04" data-filter="color" data-value="white">
										White
									</a>
								</li>
							</ul>
						</div>

						<div class="filter-col4 p-b-27">
							<div class="mtext-102 cl2 p-b-15">
								Tags
							</div>

							<div class="flex-w p-t-4 m-r--5">
								<a href="#" class="flex-c-m stext-107 cl6 size-301 bor7 p-lr-15 hov-tag1 trans-04 m-r-5 m-b-5 filter-link stext-106 trans-04" data-filter="tag" data-value="fashion">
									Fashion
								</a>

								<a href="#" class="flex-c-m stext-107 cl6 size-301 bor7 p-lr-15 hov-tag1 trans-04 m-r-5 m-b-5 filter-link stext-106 trans-04" data-filter="tag" data-value="lifestyle">
									Lifestyle
								</a>

								<a href="#" class="flex-c-m stext-107 cl6 size-301 bor7 p-lr-15 hov-tag1 trans-04 m-r-5 m-b-5 filter-link stext-106 trans-04" data-filter="tag" data-value="streetstyle">
									Streetstyle
								</a>

								<a href="#" class="flex-c-m stext-107 cl6 size-301 bor7 p-lr-15 hov-tag1 trans-04 m-r-5 m-b-5 filter-link stext-106 trans-04" data-filter="tag" data-value="crafts">
									Crafts
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>

	        <div class="row isotope-grid">
    <?php
    // 确定用户选择的货币


    // Display products dynamically
    if ($product_result->num_rows > 0) {
        while($product = $product_result->fetch_assoc()) {
            // 动态获取产品价格
            $product_price = $product[$currency_field];

            // 显示产品
            echo '<div class="col-sm-6 col-md-4 col-lg-3 p-b-35 isotope-item category-' . $product['category_id'] . '">
                    <div class="block2">
                        <div class="block2-pic hov-img0">
                            <img src="images/' . $product['product_image'] . '" alt="IMG-PRODUCT">
                            <a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1" 
                                data-id="' . $product['product_id'] . '">
                                Quick View
                            </a>
                        </div>
                        <div class="block2-txt flex-w flex-t p-t-14">
                            <div class="block2-txt-child1 flex-col-l ">
                                <a href="product-detail.php?id=' . $product['product_id'] . '" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">'
                                . $product['product_name'] . 
                                '</a>
                                <span class="stext-105 cl3">' 
                                . strtoupper($currency) . ' ' . number_format($product_price, 2) .  
                                '</span>
                            </div>
                            <div class="block2-txt-child2 flex-r p-t-3">
                                <a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2">
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

			<!-- Load more -->
			<div class="flex-c-m flex-w w-full p-t-45">
				<a href="#" class="flex-c-m stext-101 cl5 size-103 bg2 bor1 hov-btn1 p-lr-15 trans-04">
					Load More
				</a>
			</div>
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
					
					// Fetch packages containing the product ID
					fetchPackages(productId);

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
	function fetchPackages(productId) {
        $.ajax({
            url: '', // The same PHP file
            type: 'GET',
            data: { fetch_packages: true, product_id: productId },
            dataType: 'json',
            success: function(packages) {
                const packageBox = $('#packageBox');
                packageBox.empty(); // Clear existing packages

                if (packages.length > 0) {
                    packages.forEach(pkg => {
                        if (pkg.package_stock > 0) { // Only display packages with stock > 0
                            const packageHtml = `
                                <h1 class="package-title">Valuable Packages</h1>
                                <div class="package-card" data-package-id="${pkg.package_id}" data-package-stock="${pkg.package_stock}">
                                    <img src="images/${pkg.package_image}" alt="Package Image" class="p-image">
                                    <div class="package-info">
                                        <h3 class="package-name">${pkg.package_name}</h3>
                                        <p class="package-price">Price: $${parseFloat(pkg.package_price).toFixed(2)}</p>
                                        <div class="qty-controls">
                                            <button class="qty-btn minus">-</button>
                                            <input type="number" value="1" min="1" class="qty-input">
                                            <button class="qty-btn plus">+</button>
                                            <p class="stock-message" style="color: red; display: none;">The quantity of this package has reached the maximum.</p>
                                        </div>
                                        <button class="selectPackage btn-primary">Select Package</button>
                                    </div>
                                </div>`;
                            packageBox.append(packageHtml);
                        } else {
                            packageBox.append('<p>No packages found for this product.</p>');
                        }
                    });
                } else {
                    packageBox.append('<p>No packages found for this product.</p>');
                }
            },
            error: function() {
                alert('An error occurred while fetching packages.');
            }
        });
    }

    $(document).on('click', '.qty-btn.plus', function () {
        const $packageCard = $(this).closest('.package-card');
        const maxStock = parseInt($packageCard.data('package-stock'));
        const $input = $(this).siblings('.qty-input');
        const $message = $(this).siblings('.stock-message');
        const currentQty = parseInt($input.val()) || 1;

        if (currentQty < maxStock) {
            $input.val(currentQty + 1); // Increment the value
            $message.hide(); // Hide the message if visible
        } else {
            $message.show(); // Show the warning message
        }
    });

    $(document).on('click', '.qty-btn.minus', function () {
        const $input = $(this).siblings('.qty-input');
        const $message = $(this).siblings('.stock-message');
        const currentQty = parseInt($input.val()) || 1;

        const newQty = Math.max(1, currentQty - 1); // Ensure minimum value is 1
        $input.val(newQty); // Decrement the value
        if (newQty < parseInt($input.closest('.package-card').data('package-stock'))) {
            $message.hide(); // Hide the message if the quantity is below the maximum stock
        }
    });

	$(document).on('click change', '.qty-btn, .qty-input', function () {
		const $input = $(this).closest('.qty-container').find('.qty-input'); // Find the related input
		const enteredQty = parseInt($input.val()) || 1; // Get the entered quantity (default to 1)
		
		// Pass the quantity to the package form
		const $packageForm = $('#package-form'); // Replace with the actual form ID
		$packageForm.find('.qty-display').text(enteredQty); // Update the display field
		
		// Optional: Add the qty as a hidden input in the form for submission
		$packageForm.find('input[name="qty"]').val(enteredQty);
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
				alert('Please select a color and size.');
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
                        alert(`${productName} has been added to your cart!`);
                        location.reload(); // Refresh the page after a successful addition
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

<script>
// Initialize filters with 'all' default values for price, color, tag, and category.
let filters = { price: 'all', color: 'all', tag: 'all', category: 'all' };

// Function to update product display based on selected filters
function updateProducts() {
    $.ajax({
    url: '', 
    type: 'GET',
    data: {
        price: filters.price,
        color: filters.color,
        tag: filters.tag,
        category: filters.category
    },
    success: function(response) {
        // Check if the response contains error
        if (response.error) {
            alert('Error: ' + response.error);
            return;
        }
        // Proceed with normal filtering process
        if (response.trim() === '' || response.includes('No products found')) {
            $('.isotope-grid').html('<p>No products found for the selected filters.</p>');
        } else {
            $('.isotope-grid').html(response);
        }

        adjustLayoutAfterFiltering();
    },
    error: function(xhr, status, error) {
        // This handles other AJAX errors
        alert('An error occurred while fetching products: ' + error);
    }
});

}


// Function to adjust the layout and avoid overflow issues
function adjustLayoutAfterFiltering() {
    // Ensure that the container adjusts after filtering
    var container = $('.isotope-grid');
    
    // Reset any unnecessary inline styles from previous content
    container.css('height', 'auto'); 

    // If the container's height is too small, adjust it to avoid overlap with footer
    if (container.outerHeight() < $(window).height()) {
        container.css('min-height', $(window).height() - $('.footer').outerHeight());
    }

    // Optional: Trigger a reflow if necessary
    setTimeout(function() {
        container.css('visibility', 'visible'); // Ensure visibility if hidden for animation
    }, 10);
}
// Function to adjust the footer position dynamically based on content height
function adjustFooterPosition() {
    var container = $('.isotope-grid');
    var footer = $('.footer');
    var windowHeight = $(window).height();

    // Get the current height of the product container
    var contentHeight = container.outerHeight(true); // Includes margin/padding

    // Get the footer height
    var footerHeight = footer.outerHeight(true);

    // Calculate total height of the page (content + footer)
    var totalHeight = contentHeight + footerHeight;

    // If content height is smaller than the window height, adjust i
    if (totalHeight < windowHeight) {
        // Set the container's height to fill the remaining space
        container.css('min-height', windowHeight - footerHeight);
    } else {
        // Reset container min-height if the content is enough
        container.css('min-height', 'auto');
    }

    // Optional: Add a smooth transition to avoid sudden shifts
    container.css('transition', 'min-height 0.3s ease-in-out');
}

// Handle filter clicks for price, color, and tag
$(document).on('click', '.filter-link', function(event) {
    event.preventDefault();
    let filterType = $(this).data('filter');
    let filterValue = $(this).data('value');

    // Toggle filter - if clicked again, deselect by setting to 'all'
    if (filters[filterType] === filterValue) {
        filters[filterType] = 'all'; // Deselect if already selected
        $(this).removeClass('selected'); // Remove blue highlight
    } else {
        filters[filterType] = filterValue; // Apply selected filter value
        $(`.filter-link[data-filter=${filterType}]`).removeClass('selected'); // Remove highlight from other options
        $(this).addClass('selected'); // Highlight the selected option
    }

    // Fetch and update the product list based on the selected filters
    updateProducts();
});

// Handle category button clicks
$(document).on('click', '.filter-tope-group button', function(event) {
    event.preventDefault();
    let categoryValue = $(this).data('filter').replace('.category-', '');

    // Toggle category - if clicked again, deselect by setting to 'all'
    if (filters.category === categoryValue) {
        filters.category = 'all'; // Deselect if already selected
    } else {
        filters.category = categoryValue; // Apply selected category value
        $('.filter-tope-group button').removeClass('selected'); // Remove highlight from other options
    }

    // Fetch and update the product list based on the selected filters
    updateProducts();
});
</script>
<script>
        document.addEventListener("DOMContentLoaded", () => {
            // Handle quantity adjustments
            document.querySelectorAll(".qty-controls").forEach(control => {
                const minusButton = control.querySelector(".minus");
                const plusButton = control.querySelector(".plus");
                const qtyInput = control.querySelector("input");

                minusButton.addEventListener("click", () => {
                    const qty = Math.max(1, parseInt(qtyInput.value) - 1);
                    qtyInput.value = qty;
                });

                plusButton.addEventListener("click", () => {
                    qtyInput.value = parseInt(qtyInput.value) + 1;
                });
            });

            // Handle package selection
            document.querySelectorAll(".selectPackage").forEach(button => {
                button.addEventListener("click", function () {
                    const packageDiv = this.closest(".package");
                    const isSelected = packageDiv.classList.contains("selected");

                    if (isSelected) {
                        // Deselect the package
                        packageDiv.classList.remove("selected");
                        console.log(`Package ${packageDiv.dataset.packageId} deselected.`);
                    } else {
                        // Select the package
                        packageDiv.classList.add("selected");
                        console.log(`Package ${packageDiv.dataset.packageId} selected.`);
                    }
                });
            });
        });
    </script>
	<script>
		$(document).on('click', '.selectPackage', function () {
    const packageId = $(this).closest('.package-card').data('package-id');
    console.log("Package ID:", packageId);

    if (!packageId) {
        alert("Error: Package ID is undefined. Ensure .package-card has a valid data-package-id.");
        return;
    }

    // Fetch the products in the package
    $.ajax({
        url: '', // PHP endpoint to handle this request
        type: 'GET',
        data: { fetch_package_products: true, package_id: packageId },
        dataType: 'json',
        success: function (response) {
            console.log("Response:", response);

            if (!response.products || response.products.length === 0) {
                console.error("Error: response.products is undefined or empty.");
                alert("No products found for this package.");
                return;
            }

            let formHtml = `<h2>Select Options for Your Package</h2><form id="packageForm">`;
            const selectedQty = $('.qty-input').val() || 1;
            console.log("Selected Quantity:", selectedQty);

            if (!selectedQty) {
                alert("Error: Quantity is undefined.");
                return;
            }

            formHtml += `<p>You selected <span class="qty-display">${selectedQty}</span> package(s).</p>`;
            formHtml += `<input type="hidden" name="qty" value="${selectedQty}">`;

            // Loop through products and generate the form
            response.products.forEach((product, index) => {
                if (!product) {
                    console.error(`Error: Product at index ${index} is undefined.`);
                    return;
                }

                formHtml += `
                    <div class="product-options" id="product_${index + 1}">
                        <h3>${product.product_name || `Product ${index + 1}`}</h3>
                        <img src="images/${product.product_image || 'default.jpg'}" class="p-image">
                        <label for="color_${index + 1}">Color:</label>
                        <select name="color_${index + 1}" id="color_${index + 1}">
                            <option value="">Choose an option</option>
                            <option value="${product.color1 || ''}">${product.color1 || 'N/A'}</option>
                            <option value="${product.color2 || ''}">${product.color2 || 'N/A'}</option>
                        </select>
                        <label for="size_${index + 1}">Size:</label>
                        <select name="size_${index + 1}" id="size_${index + 1}">
                            <option value="">Choose an option</option>
                            <option value="${product.size1 || ''}">${product.size1 || 'N/A'}</option>
                            <option value="${product.size2 || ''}">${product.size2 || 'N/A'}</option>
                        </select>
                    </div>
                `;
            });

            formHtml += `<button type="submit" class="btn-primary">Add Package to Cart</button></form>`;

            // Inject the form into the popup container
            $('#packageFormContainer').html(formHtml);
            $('#packageFormPopup').fadeIn();

            // Handle form submission
            $('#packageForm').on('submit', function (e) {
                e.preventDefault();

                // Collect the form data
                const packageData = {
                    add_package_to_cart: true,
                    package_id: packageId,
                    qty: selectedQty
                };

                // Loop through inputs and append data
                response.products.forEach((product, index) => {
                    packageData[`color_${index + 1}`] = $(`#color_${index + 1}`).val() || '';
                    packageData[`size_${index + 1}`] = $(`#size_${index + 1}`).val() || '';
                });

                console.log("Package Data:", packageData);

                $.ajax({
                    url: '', // Replace with actual PHP script
                    type: 'POST',
                    data: packageData,
                    success: function (response) {
                        console.log("Add to Cart Response:", response);
                        if (response.success) {
                            alert('Package added to cart!');
                            $('#packageFormPopup').fadeOut();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function () {
                        alert('An error occurred while adding the package to the cart.');
                    }
                });
            });
        },
        error: function () {
            alert('An error occurred while fetching package details.');
        }
    });
});





		// Close popup on clicking the close button or outside the popup
		$(document).on('click', '.close-popup', function () {
			$('#packageFormPopup').fadeOut();
		});

		$(document).on('click', '.popup-overlay', function (e) {
			if ($(e.target).is('.popup-overlay')) {
				$('#packageFormPopup').fadeOut();
			}
		});
</script>
<script src="js/main.js"></script>

</body>
</html>

<?php
// Close the connection
$connect->close();
?>

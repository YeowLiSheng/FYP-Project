<?php
session_start(); // Start the session
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
// Handle AJAX request to fetch product details
if (isset($_GET['fetch_product']) && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $query = "SELECT * FROM product WHERE product_id = $product_id";
    $result = $conn->query($query);

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

    // Insert data into shopping_cart table
    $cart_query = "INSERT INTO shopping_cart (product_id, qty, total_price) VALUES ($product_id, $qty, $total_price)";
    if ($conn->query($cart_query) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}
// Fetch categories
$category_query = "SELECT * FROM category";
$category_result = $conn->query($category_query);

// Fetch products
$product_query = "SELECT * FROM product";
$product_result = $conn->query($product_query);

// Fetch products based on filters and search
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$price_range = isset($_GET['price']) ? $_GET['price'] : 'all';
$color = isset($_GET['color']) ? $_GET['color'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'default';

// Create the base SQL query
$product_query = "SELECT * FROM product WHERE product_name LIKE '%$search_query%'";

// Apply price filter
if ($price_range != 'all') {
    switch ($price_range) {
        case '0-50':
            $product_query .= " AND product_price BETWEEN 0 AND 50";
            break;
        case '50-100':
            $product_query .= " AND product_price BETWEEN 50 AND 100";
            break;
        case '100-150':
            $product_query .= " AND product_price BETWEEN 100 AND 150";
            break;
        case '150-200':
            $product_query .= " AND product_price BETWEEN 150 AND 200";
            break;
        case '200+':
            $product_query .= " AND product_price > 200";
            break;
    }
}

// Apply color filter
if ($color != '') {
    $product_query .= " AND product_des LIKE '%$color%'";
}

// Apply sorting
switch ($sort_by) {
    case 'popularity':
        $product_query .= " ORDER BY popularity DESC";
        break;
    case 'average_rating':
        $product_query .= " ORDER BY average_rating DESC";
        break;
    case 'newness':
        $product_query .= " ORDER BY product_id DESC";
        break;
    case 'price_low_high':
        $product_query .= " ORDER BY product_price ASC";
        break;
    case 'price_high_low':
        $product_query .= " ORDER BY product_price DESC";
        break;
    default:
        $product_query .= " ORDER BY product_id DESC";
        break;
}

$product_result = $conn->query($product_query);
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

							<li class="active-menu">
								<a href="product.html">Shop</a>
							</li>

							<li class="label1" data-label1="hot">
								<a href="shoping-cart.html">Features</a>
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
                <!-- Cart items will be dynamically added here -->
            </ul>
            
            <div class="w-full">
                <div class="header-cart-total w-full p-tb-40">
                    Total: $<span id="cart-total">0.00</span>
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


	
	<!-- Product -->
	<div class="bg0 m-t-23 p-b-140">
   		<div class="container">
        	<div class="flex-w flex-sb-m p-b-52">
            	<div class="flex-w flex-l-m filter-tope-group m-tb-10">
                	<button class="stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5 how-active1" data-filter="*">
                    	All Products
                	</button>

                	<?php
                	// Display categories dynamically
                	if ($category_result->num_rows > 0) {
                    	while($row = $category_result->fetch_assoc()) {
                        	// Display categories with their IDs in the data-filter
                        	echo '<button class="stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5" data-filter=".category-' . $row['category_id'] . '">'
                        	. $row['category_name'] .
                        	'</button>';
                    	}
                	}
                	?>
            	</div>
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
        <form method="GET" action="">
            <div class="wrap-filter flex-w bg6 w-full p-lr-40 p-t-27 p-lr-15-sm">
                <!-- Sort By -->
                <div class="filter-col1 p-r-15 p-b-27">
                    <div class="mtext-102 cl2 p-b-15">Sort By</div>
                    <select name="sort_by" class="filter-link stext-106 trans-04">
                        <option value="default">Default</option>
                        <option value="popularity">Popularity</option>
                        <option value="average_rating">Average rating</option>
                        <option value="newness">Newness</option>
                        <option value="price_low_high">Price: Low to High</option>
                        <option value="price_high_low">Price: High to Low</option>
                    </select>
                </div>

                <!-- Price -->
                <div class="filter-col2 p-r-15 p-b-27">
                    <div class="mtext-102 cl2 p-b-15">Price</div>
                    <select name="price" class="filter-link stext-106 trans-04">
                        <option value="all">All</option>
                        <option value="0-50">$0.00 - $50.00</option>
                        <option value="50-100">$50.00 - $100.00</option>
                        <option value="100-150">$100.00 - $150.00</option>
                        <option value="150-200">$150.00 - $200.00</option>
                        <option value="200+">$200.00+</option>
                    </select>
                </div>

                <!-- Color -->
                <div class="filter-col3 p-r-15 p-b-27">
                    <div class="mtext-102 cl2 p-b-15">Color</div>
                    <select name="color" class="filter-link stext-106 trans-04">
                        <option value="">All Colors</option>
                        <option value="Black">Black</option>
                        <option value="Blue">Blue</option>
                        <option value="Grey">Grey</option>
                        <option value="Green">Green</option>
                        <option value="Red">Red</option>
                        <option value="White">White</option>
                    </select>
                </div>

                <div class="p-t-20">
                    <button type="submit" class="flex-c-m stext-101 cl5 size-103 bg2 bor1 hov-btn1 p-lr-15 trans-04">
                        Apply Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

	        <div class="row isotope-grid">
            <?php
            // Display products dynamically
            if ($product_result->num_rows > 0) {
                while($product = $product_result->fetch_assoc()) {
                    // Assign a class to each product based on its category_id
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
                                        <span class="stext-105 cl3">$' . $product['product_price'] . '</span>
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
								<div class="item-slick3" data-thumb="images/<?php echo $product['Quick_View1']; ?>">
									<div class="wrap-pic-w pos-relative">
										<img src="images/<?php echo $product['Quick_View1']; ?>" alt="IMG-PRODUCT">
										<a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04" href="images/<?php echo $product['Quick_View1']; ?>">
											<i class="fa fa-expand"></i>
										</a>
									</div>
								</div>

								<div class="item-slick3" data-thumb="images/<?php echo $product['Quick_View2']; ?>">
									<div class="wrap-pic-w pos-relative">
										<img src="images/<?php echo $product['Quick_View2']; ?>" alt="IMG-PRODUCT">
										<a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04" href="images/<?php echo $product['Quick_View2']; ?>">
											<i class="fa fa-expand"></i>
										</a>
									</div>
								</div>

								<div class="item-slick3" data-thumb="images/<?php echo $product['Quick_View3']; ?>">
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

										<input class="mtext-104 cl3 txt-center num-product" type="number" name="num-product" value="1">

										<div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m">
											<i class="fs-16 zmdi zmdi-plus"></i>
										</div>
									</div>

									<button class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 trans-04 js-addcart-detail">
										Add to cart
									</button>
								</div>
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
	// Toggle search panel
    $('.js-show-search').on('click', function(){
        if (!$(this).hasClass('active')) {
            $(this).addClass('active');
            $('.panel-search').slideDown(400);
        } else {
            $(this).removeClass('active');
            $('.panel-search').slideUp(400);
        }
    });

    // Toggle filter panel
    $('.js-show-filter').on('click', function(){
        if (!$(this).hasClass('active')) {
            $(this).addClass('active');
            $('.panel-filter').slideDown(400);
        } else {
            $(this).removeClass('active');
            $('.panel-filter').slideUp(400);
        }
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
                    
                    // Update Quick View images
                    $('.gallery-lb .item-slick3').each(function(index) {
                        var imagePath = 'images/' + response['Quick_View' + (index + 1)];
                        $(this).find('.wrap-pic-w img').attr('src', imagePath);
                        $(this).find('.wrap-pic-w a').attr('href', imagePath);
                        $(this).attr('data-thumb', imagePath);
                    });

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

    // Close modal
    $('.js-hide-modal1').on('click', function(){
        $('.js-modal1').removeClass('show-modal1');
    });
</script>
<script>
    let cart = [];

    function updateCart() {
        const cartItemsContainer = $('#cart-items');
        const cartTotalElement = $('#cart-total');
        let total = 0;
        cartItemsContainer.empty();

        cart.forEach(item => {
            total += item.price * item.quantity;
            cartItemsContainer.append(`
                <li class="header-cart-item flex-w flex-t m-b-12">
                    <div class="header-cart-item-img">
                        <img src="images/${item.image}" alt="IMG">
                    </div>
                    <div class="header-cart-item-txt p-t-8">
                        <a href="#" class="header-cart-item-name m-b-18 hov-cl1 trans-04">
                            ${item.name} (ID: ${item.id})
                        </a>
                        <span class="header-cart-item-info">
                            ${item.quantity} x $${item.price.toFixed(2)}
                        </span>
                    </div>
                </li>
            `);
        });

        cartTotalElement.text(total.toFixed(2));
    }

    $(document).on('click', '.js-addcart-detail', function(event) {
    event.preventDefault();
    
    const productId = $('.js-show-modal1').data('id');
    const productName = $('.js-name-detail').text();
    const productPrice = parseFloat($('.mtext-106').text().replace('$', ''));
    const productQuantity = parseInt($('.num-product').val());
    const totalPrice = productPrice * productQuantity;

    $.ajax({
        url: '', // Use the same PHP file
        type: 'POST',
        data: {
            add_to_cart: true,
            product_id: productId,
            qty: productQuantity,
            total_price: totalPrice
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(`${productName} has been added to your cart!`);
            } else {
                alert('Failed to add product to cart: ' + (response.error || 'unknown error'));
            }
            updateCart();
            $('.js-modal1').removeClass('show-modal1');
        },
        error: function() {
            alert('An error occurred while adding to the cart.');
        }
    });
});
</script>
<script src="js/main.js"></script>

</body>
</html>

<?php
// Close the connection
$conn->close();
?>
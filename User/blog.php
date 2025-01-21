<?php
// Start session
session_start();

// Include the database connection file
include("dataconnection.php"); 

$user_email = '';
// Check if the user is logged in
if (isset($_SESSION['id'])) {
    // Check if the database connection exists
    if (!isset($connect) || !$connect) {
        die("Database connection failed.");
    }

    // Retrieve the user information
    $user_id = $_SESSION['id'];
    $result = mysqli_query($connect, "SELECT * FROM user WHERE user_id = '$user_id'");
	

    // Check if the query was successful and fetch user data
    if ($result && mysqli_num_rows($result) > 0) {
		$user_data = mysqli_fetch_assoc($result);
        $user_name = htmlspecialchars($user_data["user_name"]); // Get the user name
		 $user_email = $user_data['user_email'];
    } else {
        echo "User not found.";
        exit;
    }


}
	// Check if the user is logged i
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
		$user_name = htmlspecialchars($user_data["user_name"]); // Get the user name
		$user_email = $user_data['user_email'];
	} else {
		echo "User not found.";
		exit;
	}

	// Initialize total_price before fetching cart items
	$total_price = 0;

	// Fetch and combine cart items for the logged-in user where the product_id is the same
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
        SUM(sc.total_price) AS total_price
    FROM shopping_cart sc
    LEFT JOIN product_variant pv ON sc.variant_id = pv.variant_id
	LEFT JOIN product p ON pv.product_id = p.product_id
    LEFT JOIN promotion_product pm ON pv.promotion_id = pm.promotion_id
    WHERE sc.user_id = $user_id
    GROUP BY 
        sc.variant_id";
$cart_items_result = $connect->query($cart_items_query);

$query = "SELECT * FROM product_variant";
$result = mysqli_query($connect, $query);
$product_variants = mysqli_fetch_all($result, MYSQLI_ASSOC);

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



?>






<!DOCTYPE html>
<html lang="en">
<head>
	<title>Blog</title>
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
	.rainbow-text {
    background: linear-gradient(to right, red, orange, yellow, green, indigo, violet);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent; /* Ensures the text is transparent on WebKit browsers */
    color: transparent; /* Ensures the text is transparent on non-WebKit browsers */
    font-weight: bold;
    background-size: 200%;
    animation: fadeRainbow 2s infinite;
}

@keyframes fadeRainbow {
    0% { background-position: 0%; }
    100% { background-position: 100%; }
}
.hov-img0 {
    width: 100%; /* Adjust width as needed */
    height: 100%; /* Adjust height as needed */
    overflow: hidden; /* Ensures the video doesn’t exceed the container bounds */
}
.responsive-video {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Ensures the video covers the container without stretching */
}
/* Hide all default controls */
.no-controls::-webkit-media-controls,
.no-controls::-webkit-media-controls-enclosure {
    display: none !important;
}

/* Hide controls for other browsers */
.no-controls::-moz-media-controls,
.no-controls::-moz-media-controls-enclosure {
    display: none !important;
}

/* Prevent interaction with the video */
.no-controls {
    pointer-events: none;
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

                    <a href="shoping-cart.html" class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-b-10">
                        Check Out
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Title page -->
<section class="bg-img1 txt-center p-lr-15 p-tb-92" style="background-image: url('images/bg-02.jpg');">
    <h2 class="ltext-105 cl0 txt-center">
        Blog
    </h2>
</section>

<?php
// Number of blogs per page
$blogsPerPage = 3;

// Get the current page number from the URL, default to 1 if not provided
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the offset
$offset = ($currentPage - 1) * $blogsPerPage;

// Fetch the total number of blogs
$totalQuery = "SELECT COUNT(*) AS total FROM blog";
$totalResult = $connect->query($totalQuery);
$totalBlogs = $totalResult->fetch_assoc()['total'];

// Calculate total pages
$totalPages = ceil($totalBlogs / $blogsPerPage);

// Fetch the blog data with LIMIT and OFFSET
$query = "SELECT * FROM blog LIMIT $blogsPerPage OFFSET $offset";
$result = $connect->query($query);
?>

<!-- Content page -->
<section class="bg0 p-t-62 p-b-60">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-9 p-b-80">
                <div class="p-r-45 p-r-0-lg">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <!-- Item blog -->
                            <div class="p-b-63 text-center">
                                <!-- Blog Image -->
                                <a href="blog-detail.php?id=<?php echo $row['blog_id']; ?>" class="hov-img0 d-block mx-auto" style="position: relative; width: 100%;">
                                    <?php
                                    $imagePath = 'http://localhost/FYP-PROJECT/Admin/blog/' . $row['picture'];
                                    if (getimagesize($imagePath)): ?>
                                        <img src="<?php echo $imagePath; ?>" alt="IMG-BLOG" class="img-fluid">
                                    <?php else: ?>
                                        <p class="text-danger">Image not found: <?php echo $imagePath; ?></p>
                                    <?php endif; ?>

                                    <!-- Date overlay -->
                                    <div class="date-overlay" style="position: absolute; top: 10px; left: 10px; background-color: rgba(0, 0, 0, 0.7); color: white; padding: 10px; text-align: center; border-radius: 5px;">
                                        <span class="ltext-107 cl2" style="display: block; font-size: 24px; color: white;">
                                            <?php echo date('d', strtotime($row['date'])); ?>
                                        </span>
                                        <span class="stext-109 cl3" style="display: block; font-size: 14px; color: white;">
                                            <?php echo date('M Y', strtotime($row['date'])); ?>
                                        </span>
                                    </div>
                                </a>

                                <!-- Blog Content -->
                                <div class="p-t-32 text-left">
                                    <h4 class="p-b-15">
                                        <a href="blog-detail.php?id=<?php echo $row['blog_id']; ?>" class="ltext-108 cl2 hov-cl1 trans-04">
                                            <?php echo htmlspecialchars($row['title']); ?>
                                        </a>
                                    </h4>
                                    <p class="stext-117 cl6">
                                        <?php echo htmlspecialchars($row['subtitle']); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="stext-117 cl6 text-center">No blogs available at the moment.</p>
                    <?php endif; ?>

                    <!-- Pagination -->
                    <nav class="d-flex justify-content-center p-t-20">
                        <ul class="pagination">
                            <?php if ($currentPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <?php if ($currentPage < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Close the database connection
$connect->close();
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
		$(".js-select2").each(function(){
			$(this).select2({
				minimumResultsForSearch: 20,
				dropdownParent: $(this).next('.dropDownSelect2')
			});
		})
	</script>
<!--===============================================================================================-->
	<script src="vendor/MagnificPopup/jquery.magnific-popup.min.js"></script>
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
<!--===============================================================================================-->
	<script src="js/main.js"></script>
	
</body>
</html>
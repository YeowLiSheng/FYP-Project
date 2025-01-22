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
$result = mysqli_query($connect, "SELECT * FROM user WHERE user_id ='$user_id'"); // Changed $connect to $conn

// Check if the query was successful and fetch user data
if ($result && mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_assoc($result);
} else {
    echo "User not found.";
    exit;
}

// Fetch and combine cart items for the logged-in user
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
if (isset($_GET['fetch_product']) && isset($_GET['id']) && isset($_GET['type'])) {
    $id = intval($_GET['id']); // Fetch the ID from the request
    $type = $_GET['type'];     // Fetch the type (product or promotion)

    // Prepare the query based on the type
    if ($type === 'product') {
        $query = "SELECT * FROM product WHERE product_id = $id";
    } elseif ($type === 'promotion') {
        $query = "SELECT * FROM promotion_product WHERE promotion_id = $id";
    } else {
        // Invalid type
        echo json_encode(null);
        exit;
    }

    // Execute the query
    $result = $connect->query($query);

    if ($result && $result->num_rows > 0) {
        $details = $result->fetch_assoc(); // Fetch the row as an associative array
        echo json_encode($details);
    } else {
        echo json_encode(null); // Return null if no data is found
    }
    exit;
}

if (isset($_GET['check_cart_qty']) && isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    $user_id = $_SESSION['id'];

    $query = "SELECT SUM(qty) AS total_qty FROM shopping_cart WHERE user_id = $user_id AND product_id = $product_id";
    $result = $connect->query($query);

    if ($result) {
        $row = $result->fetch_assoc();
        $total_qty = $row['total_qty'] ?? 0;
        echo json_encode(['total_qty' => $total_qty]);
    } else {
        echo json_encode(['total_qty' => 0]);
    }
    exit;
}
// Handle AJAX request to add product to shopping cart
if (isset($_POST['add_to_cart']) && isset($_POST['variant_id']) && isset($_POST['qty']) && isset($_POST['total_price'])) {
    $variant_id = intval($_POST['variant_id']);
    $qty = intval($_POST['qty']);
    $total_price = doubleval($_POST['total_price']);
    $user_id = $_SESSION['id'];

    $cart_query = "INSERT INTO shopping_cart (user_id, variant_id, qty, total_price) VALUES ($user_id, $variant_id, $qty, $total_price)";
    if ($connect->query($cart_query) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $connect->error]);
    }
    exit;
}

// Count distinct product IDs in the shopping cart for the logged-in user
$id = $_GET['id'];
$type = $_GET['type'];

if ($type === 'promotion') {
    $promotion_id = $id;
    // Now you can use $promotion_id as needed
}else{
	$product_id = $id;
}


// Handle AJAX request for fetching variant by color
if (isset($_GET['fetch_variant_by_color']) && isset($_GET['id']) && isset($_GET['color']) && isset($_GET['type'])) {
    $id = intval($_GET['id']);
    $color = $_GET['color'];
    $type = $_GET['type'];

    if ($type === 'promotion') {
        $query = "SELECT pv.*, pm.promotion_name, pm.promotion_price, pm.promotion_des 
                  FROM product_variant pv 
                  JOIN promotion_product pm ON pv.promotion_id = pm.promotion_id 
                  WHERE pv.promotion_id = ? AND pv.color = ?";
    } else {
        $query = "SELECT pv.*, p.product_name, p.product_price, p.product_des 
                  FROM product_variant pv 
                  JOIN product p ON pv.product_id = p.product_id 
                  WHERE pv.product_id = ? AND pv.color = ?";
    }

    $stmt = $connect->prepare($query);
    $stmt->bind_param("is", $id, $color);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $variant = $result->fetch_assoc();

        // Fetch sizes
        $size_query = "SELECT DISTINCT size FROM product_variant WHERE " . ($type === 'promotion' ? "promotion_id" : "product_id") . " = ? AND color = ?";
        $size_stmt = $connect->prepare($size_query);
        $size_stmt->bind_param("is", $id, $color);
        $size_stmt->execute();
        $size_result = $size_stmt->get_result();

        $sizes = [];
        while ($size_row = $size_result->fetch_assoc()) {
            $sizes[] = $size_row['size'];
        }

        $variant['sizes'] = $sizes;

        echo json_encode($variant);
    } else {
        echo json_encode(null);
    }
    exit;
}

if (isset($_GET['fetch_variant_by_color_promotion']) && isset($_GET['promotion_id']) && isset($_GET['color'])) {
    $promotion_id = intval($_GET['promotion_id']);
    $color = $_GET['color'];

    // Fetch the variant data for the selected color and product ID
    $variant_query = "SELECT pv.*, pm.promotion_name, pm.promotion_price, pm.promotion_des 
                      FROM product_variant pv 
                      JOIN promotion_product pm ON pv.promotion_id = pm.promotion_id 
                      WHERE pv.promotion_id = ? AND pv.color = ?";
    $stmt = $connect->prepare($variant_query);
    $stmt->bind_param("is", $promotion_id, $color);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $variant = $result->fetch_assoc();

        // Fetch unique sizes for this product and color
        $size_query = "SELECT DISTINCT size FROM product_variant WHERE promotion_id = ? AND color = ?";
        $size_stmt = $connect->prepare($size_query);
        $size_stmt->bind_param("is", $promotion_id, $color);
        $size_stmt->execute();
        $size_result = $size_stmt->get_result();

        $sizes = [];
        while ($size_row = $size_result->fetch_assoc()) {
            $sizes[] = $size_row['size'];
        }

        // Include sizes in the response
        $variant['sizes'] = $sizes;

        echo json_encode($variant);
    } else {
        echo json_encode(null);
    }
    exit;
}


if ($type === 'promotion') {
    $promotion_id = $id;

    // Fetch promotion details
    $promotion_query = "SELECT * FROM promotion_product WHERE promotion_id = ?";
    $stmt = $connect->prepare($promotion_query);
    $stmt->bind_param("i", $promotion_id);
    $stmt->execute();
    $promotion_result = $stmt->get_result();
    $promotion = $promotion_result->fetch_assoc();

    // Fetch promotion variants
    $variant_query = "SELECT * FROM product_variant WHERE promotion_id = ?";
    $stmt = $connect->prepare($variant_query);
    $stmt->bind_param("i", $promotion_id);
    $stmt->execute();
    $variant_result = $stmt->get_result();
    $variant = $variant_result->fetch_assoc();

    // Fetch colors for the promotion
    $color_query = "SELECT DISTINCT color FROM product_variant WHERE promotion_id = ?";
    $stmt = $connect->prepare($color_query);
    $stmt->bind_param("i", $promotion_id);
    $stmt->execute();
    $color_result = $stmt->get_result();
    $colors = [];
    while ($row = $color_result->fetch_assoc()) {
        $colors[] = $row['color'];
    }
} else {
    $product_id = $id;

    // Fetch product details
    $product_query = "SELECT * FROM product WHERE product_id = ?";
    $stmt = $connect->prepare($product_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();
    $product = $product_result->fetch_assoc();

    // Fetch product variants
    $variant_query = "SELECT * FROM product_variant WHERE product_id = ?";
    $stmt = $connect->prepare($variant_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $variant_result = $stmt->get_result();
    $variant = $variant_result->fetch_assoc();

    // Fetch colors for the product
    $color_query = "SELECT DISTINCT color FROM product_variant WHERE product_id = ?";
    $stmt = $connect->prepare($color_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $color_result = $stmt->get_result();
    $colors = [];
    while ($row = $color_result->fetch_assoc()) {
        $colors[] = $row['color'];
    }
}

// Fetch reviews for the product
$review_query = "
    SELECT 
        r.comment, 
        r.rating, 
        r.image,
        r.created_at,
        r.status,
        r.admin_reply,
        r.admin_reply_updated_at,
        u.user_name, 
        u.user_image, 
        a.admin_id 
    FROM 
        reviews r
    JOIN 
        user u ON r.user_id = u.user_id
    JOIN 
        order_details od ON r.detail_id = od.detail_id
    JOIN 
        product_variant pv ON od.variant_id = pv.variant_id
    LEFT JOIN 
        admin a ON r.staff_id = a.staff_id
    WHERE 
        (pv.product_id = ? OR pv.promotion_id = ?) 
        AND r.status = 'active'";

$stmt = $connect->prepare($review_query);
if (!$stmt) {
    die("SQL prepare failed: " . $connect->error); 
}
$stmt->bind_param("ii", $product_id, $promotion_id);
$stmt->execute();
$reviews_result = $stmt->get_result();


$review_count_query = "
    SELECT 
        COUNT(*) as review_count 
    FROM 
        reviews r
    JOIN 
        order_details od ON r.detail_id = od.detail_id
    JOIN 
        product_variant pv ON od.variant_id = pv.variant_id
    WHERE 
        (pv.product_id = ? OR pv.promotion_id = ?) 
        AND r.status = 'active'";

$stmt = $connect->prepare($review_count_query);
if (!$stmt) {
    die("SQL prepare failed: " . $connect->error); 
}
$stmt->bind_param("ii", $product_id, $promotion_id);
$stmt->execute();
$review_count_result = $stmt->get_result();
$review_count = $review_count_result->fetch_assoc()['review_count'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Product Detail</title>
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
.wrap-slick3-dots {
    display: none; /* Hides the thumbnails/dots at the left */
}
.wrap-slick3-arrows {
	right: 120px;
}
.size-option {
        display: inline-block;
        padding: 10px 15px;
        margin: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .size-option:hover {
        background-color: #f1f1f1;
    }

    .size-option.selected {
        background-color: #000;
        color: #fff;
        border-color: #000;
    }

    .no-size {
        color: red;
        font-size: 14px;
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

	<!-- breadcrumb -->
	<div class="container">
		<div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
			<a href="index.html" class="stext-109 cl8 hov-cl1 trans-04">
				Home
				<i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
			</a>

			<a href="product.html" class="stext-109 cl8 hov-cl1 trans-04">
				Women Bag
				<i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
			</a>

			<span class="stext-109 cl4">
				<?php echo isset($type) && $type === 'promotion' ? $promotion['promotion_name'] : $product['product_name']; ?>
			</span>
		</div>
	</div>
		

	<!-- Product Detail -->
<section class="sec-product-detail bg0 p-t-65 p-b-60">
    <div class="container">
        <div class="row">
			<div class="col-md-6 col-lg-7 p-b-30">
				<div class="p-l-25 p-r-30 p-lr-0-lg">
					<div class="wrap-slick3 flex-sb flex-w">
						<div class="wrap-slick3-dots"></div>
						<div class="wrap-slick3-arrows flex-sb-m flex-w"></div>
						<div class="slick3 gallery-lb">
							<?php 
							// Check if ID type is promotion or product
							if (isset($promotion_id) || isset($product_id)) {
								// Determine the query based on the type
								$query = "";
								if (isset($promotion_id)) {
									$query = "SELECT * FROM product_variant WHERE promotion_id = $promotion_id ORDER BY variant_id ASC LIMIT 1";
								} elseif (isset($product_id)) {
									$query = "SELECT * FROM product_variant WHERE product_id = $product_id ORDER BY variant_id ASC LIMIT 1";
								}

								// Execute the query
								$result = $connect->query($query);
								if ($result && $result->num_rows > 0) {
									$variant = $result->fetch_assoc();

									// Loop through quick view images for the selected variant
									for ($i = 1; $i <= 3; $i++) {
										$image_key = "Quick_View$i"; // Dynamically generate the key for the quick view image
										if (!empty($variant[$image_key])) { // Check if the image exists
											echo '
											<div class="item-slick3">
												<div class="wrap-pic-w pos-relative">
													<img src="images/' . $variant[$image_key] . '" alt="IMG-' . (isset($promotion_id) ? 'PROMOTION' : 'PRODUCT') . '">
													<a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04" href="images/' . $variant[$image_key] . '">
														<i class="fa fa-expand"></i>
													</a>
												</div>
											</div>';
										}
									}
								} else {
									echo '<p>No images available for this product or promotion.</p>';
								}
							} else {
								echo '<p>No images available for this product or promotion.</p>';
							}
							?>
						</div>
					</div>
				</div>
			</div>


                
            <div class="col-md-6 col-lg-5 p-b-30">
                <div class="p-r-50 p-t-5 p-lr-0-lg">
                    <h4 class="mtext-105 cl2 js-name-detail p-b-14">
						<?php echo isset($type) && $type === 'promotion' ? $promotion['promotion_name'] : $product['product_name']; ?>
                    </h4>

                    <span class="mtext-106 cl2">
						$<?php echo isset($type) && $type === 'promotion' ? $promotion['promotion_price'] : $product['product_price']; ?>
                    </span>

                    <!--  -->
					<div class="p-t-33">
						<div class="flex-w flex-r-m p-b-10">
							<div class="size-203 flex-c-m respon6">
								Size
							</div>

							<div class="size-204 respon6-next">
								<div id="size-container" class="flex-w flex-m">
									<!-- Sizes will be dynamically added here -->
								</div>
							</div>
						</div>

						<div class="flex-w flex-r-m p-b-10">
							<div class="size-203 flex-c-m respon6">
								Color
							</div>

							<div class="size-204 respon6-next">
								<div class="rs1-select2 bor8 bg0">
									<select class="js-select2" id="color-select" name="color">
										<option>Choose an option</option>
										<?php foreach ($colors as $color): ?>
											<option value="<?php echo $color; ?>"data-variant-id="<?php echo $variant['variant_id']; ?>"><?php echo $color; ?></option>
										<?php endforeach; ?>
									</select>
									<div class="dropDownSelect2"></div>
								</div>
							</div>
						</div>

                    	<!-- Add to Cart Section -->
                    	<div class="flex-w flex-r-m p-b-10">
                        	<div class="size-204 flex-w flex-m respon6-next warning">
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
						<!--  -->
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

			<div class="bor10 m-t-50 p-t-43 p-b-40">
				<!-- Tab01 -->
				<div class="tab01">
					<!-- Nav tabs -->
					<ul class="nav nav-tabs" role="tablist">
						<li class="nav-item p-b-10">
							<a class="nav-link active" data-toggle="tab" href="#description" role="tab">Description</a>
						</li>

						<li class="nav-item p-b-10">
							<a class="nav-link" data-toggle="tab" href="#information" role="tab">Additional information</a>
						</li>

						<li class="nav-item p-b-10">
						<a class="nav-link" data-toggle="tab" href="#reviews" role="tab">Reviews (<?php echo $review_count; ?>)</a>
						</li>
					</ul>

					<!-- Tab panes -->
					<div class="tab-content p-t-43">
						<!-- - -->
						<div class="tab-pane fade show active" id="description" role="tabpanel">
							<div class="how-pos2 p-lr-15-md">
								<p class="stext-102 cl6">
									<?php echo isset($type) && $type === 'promotion' ? $promotion['promotion_des'] : $product['product_des']; ?>
								</p>
							</div>
						</div>

						<!-- - -->
						<div class="tab-pane fade" id="information" role="tabpanel">
							<div class="row">
								<div class="col-sm-10 col-md-8 col-lg-6 m-lr-auto">
									<ul class="p-lr-28 p-lr-15-sm">
										<li class="flex-w flex-t p-b-7">
											<span class="stext-102 cl3 size-205">
												Weight
											</span>

											<span class="stext-102 cl6 size-206">
												0.79 kg
											</span>
										</li>

										<li class="flex-w flex-t p-b-7">
											<span class="stext-102 cl3 size-205">
												Dimensions
											</span>

											<span class="stext-102 cl6 size-206">
												110 x 33 x 100 cm
											</span>
										</li>

										<li class="flex-w flex-t p-b-7">
											<span class="stext-102 cl3 size-205">
												Materials
											</span>

											<span class="stext-102 cl6 size-206">
												60% cotton
											</span>
										</li>

										<li class="flex-w flex-t p-b-7">
											<span class="stext-102 cl3 size-205">
												Color
											</span>

											<span class="stext-102 cl6 size-206">
												Black, Blue, Grey, Green, Red, White
											</span>
										</li>

										<li class="flex-w flex-t p-b-7">
											<span class="stext-102 cl3 size-205">
												Size
											</span>

											<span class="stext-102 cl6 size-206">
												XL, L, M, S
											</span>
										</li>
									</ul>
								</div>
							</div>
						</div>

						<div class="tab-pane fade" id="reviews" role="tabpanel"> 
    <div class="row">
        <div class="col-sm-10 col-md-8 col-lg-6 m-lr-auto">
            <div class="p-b-30 m-lr-15-sm">
                <!-- Check if there are reviews -->
                <?php if ($reviews_result->num_rows > 0) { ?>
                    <?php while ($review = $reviews_result->fetch_assoc()) { ?>
                        <div class="flex-w flex-t p-b-68">
                            <!-- User Image -->
                            <div class="wrap-pic-s size-109 bor0 of-hidden m-r-18 m-t-6">
                                <img src="<?php echo !empty($review['user_image']) ? $review['user_image'] : 'images/default-avatar.png'; ?>" alt="User"
                                     style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 15px;">
                            </div>
                            <!-- Review Content -->
                            <div class="size-207">
                                <!-- Header: Username and Rating -->
                                <div class="flex-w flex-sb-m p-b-17">
                                    <div class="flex-w align-items-center">
                                        <span class="mtext-107 cl2 p-r-20">
                                            <?php echo htmlspecialchars($review['user_name']); ?>
                                        </span>
                                        <span class="stext-101 cl4" style="font-size: 12px; color: #888; margin-left: 10px;">
                                            <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($review['created_at']))); ?>
                                        </span>
                                    </div>
                                    <!-- Rating -->
                                    <span class="fs-18 cl11">
                                        <?php for ($i = 1; $i <= 5; $i++) { ?>
                                            <i class="zmdi zmdi-star<?php echo $i <= $review['rating'] ? '' : '-outline'; ?>"></i>
                                        <?php } ?>
                                    </span>
                                </div>
                                <!-- Comment -->
                                <p class="stext-102 cl6">
                                    <?php echo htmlspecialchars($review['comment']); ?>
                                </p>
                                <!-- Review Image -->
                                <?php if (!empty($review['image'])) { ?>
                                    <div class="review-image">
                                        <img src="<?php echo $review['image']; ?>" alt="Review Image"
                                             style="width: 150px; height: 150px; border-radius: 10px; object-fit: cover; margin-top: 10px; cursor: pointer;"
                                             onclick="openModal('<?php echo $review['image']; ?>')">
                                    </div>
                                    <!-- Modal -->
                                    <div id="imageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 9999; justify-content: center; align-items: center;">
                                        <span style="position: absolute; top: 20px; right: 20px; font-size: 30px; color: white; cursor: pointer;" onclick="closeModal()">&times;</span>
                                        <img id="modalImage" src="" alt="Full Image" style="max-width: 90%; max-height: 90%; border-radius: 10px;">
                                    </div>
                                <?php } ?>
                                <!-- Admin Reply -->
                                <?php if (!empty($review['admin_reply'])) { ?>
                                    <div class="flex-w flex-t p-t-20">
                                        <div class="wrap-pic-s size-109 bor0 of-hidden m-r-18 m-t-6">
                                            <img src="images/admin-avatar.png" alt="Admin"
                                                 style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 15px;">
                                        </div>
                                        <div>
                                            <div class="flex-w align-items-center">
											<?php echo htmlspecialchars($review['admin_id'] ?? 'Admin'); ?>
											<span class="stext-101 cl4" style="font-size: 12px; color: #888; margin-left: 10px;">
                                                    <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($review['admin_reply_updated_at']))); ?>
                                                </span>
                                            </div>
                                            <p class="stext-102 cl6">
                                                <?php echo htmlspecialchars($review['admin_reply']); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p class="stext-102 cl6">No reviews to show.</p>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
					</div>
				</div>
			</div>
		</div>

		<div class="bg6 flex-c-m flex-w size-302 m-t-73 p-tb-15">
			<span class="stext-107 cl6 p-lr-25">
				SKU: JAK-01
			</span>

			<span class="stext-107 cl6 p-lr-25">
				Categories: Jacket, Men
			</span>
		</div>
</section>


	<!-- Related Products -->
	<section class="sec-relate-product bg0 p-t-45 p-b-105">
		<div class="container">
			<div class="p-b-45">
				<h3 class="ltext-106 cl5 txt-center">
					Related Products
				</h3>
			</div>

			<!-- Slide2 -->
			<div class="wrap-slick2">
				<div class="slick2">
					<div class="item-slick2 p-l-15 p-r-15 p-t-15 p-b-15">
						<!-- Block2 -->
						<div class="block2">
							<div class="block2-pic hov-img0">
								<img src="images/product-01.jpg" alt="IMG-PRODUCT">

								<a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1">
									Quick View
								</a>
							</div>

							<div class="block2-txt flex-w flex-t p-t-14">
								<div class="block2-txt-child1 flex-col-l ">
									<a href="product-detail.html" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">
										Esprit Ruffle Shirt
									</a>

									<span class="stext-105 cl3">
										$16.64
									</span>
								</div>

								<div class="block2-txt-child2 flex-r p-t-3">
									<a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2">
										<img class="icon-heart1 dis-block trans-04" src="images/icons/icon-heart-01.png" alt="ICON">
										<img class="icon-heart2 dis-block trans-04 ab-t-l" src="images/icons/icon-heart-02.png" alt="ICON">
									</a>
								</div>
							</div>
						</div>
					</div>

					<div class="item-slick2 p-l-15 p-r-15 p-t-15 p-b-15">
						<!-- Block2 -->
						<div class="block2">
							<div class="block2-pic hov-img0">
								<img src="images/product-02.jpg" alt="IMG-PRODUCT">

								<a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1">
									Quick View
								</a>
							</div>

							<div class="block2-txt flex-w flex-t p-t-14">
								<div class="block2-txt-child1 flex-col-l ">
									<a href="product-detail.html" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">
										Herschel supply
									</a>

									<span class="stext-105 cl3">
										$35.31
									</span>
								</div>

								<div class="block2-txt-child2 flex-r p-t-3">
									<a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2">
										<img class="icon-heart1 dis-block trans-04" src="images/icons/icon-heart-01.png" alt="ICON">
										<img class="icon-heart2 dis-block trans-04 ab-t-l" src="images/icons/icon-heart-02.png" alt="ICON">
									</a>
								</div>
							</div>
						</div>
					</div>

					<div class="item-slick2 p-l-15 p-r-15 p-t-15 p-b-15">
						<!-- Block2 -->
						<div class="block2">
							<div class="block2-pic hov-img0">
								<img src="images/product-03.jpg" alt="IMG-PRODUCT">

								<a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1">
									Quick View
								</a>
							</div>

							<div class="block2-txt flex-w flex-t p-t-14">
								<div class="block2-txt-child1 flex-col-l ">
									<a href="product-detail.html" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">
										Only Check Trouser
									</a>

									<span class="stext-105 cl3">
										$25.50
									</span>
								</div>

								<div class="block2-txt-child2 flex-r p-t-3">
									<a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2">
										<img class="icon-heart1 dis-block trans-04" src="images/icons/icon-heart-01.png" alt="ICON">
										<img class="icon-heart2 dis-block trans-04 ab-t-l" src="images/icons/icon-heart-02.png" alt="ICON">
									</a>
								</div>
							</div>
						</div>
					</div>

					<div class="item-slick2 p-l-15 p-r-15 p-t-15 p-b-15">
						<!-- Block2 -->
						<div class="block2">
							<div class="block2-pic hov-img0">
								<img src="images/product-04.jpg" alt="IMG-PRODUCT">

								<a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1">
									Quick View
								</a>
							</div>

							<div class="block2-txt flex-w flex-t p-t-14">
								<div class="block2-txt-child1 flex-col-l ">
									<a href="product-detail.html" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">
										Classic Trench Coat
									</a>

									<span class="stext-105 cl3">
										$75.00
									</span>
								</div>

								<div class="block2-txt-child2 flex-r p-t-3">
									<a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2">
										<img class="icon-heart1 dis-block trans-04" src="images/icons/icon-heart-01.png" alt="ICON">
										<img class="icon-heart2 dis-block trans-04 ab-t-l" src="images/icons/icon-heart-02.png" alt="ICON">
									</a>
								</div>
							</div>
						</div>
					</div>

					<div class="item-slick2 p-l-15 p-r-15 p-t-15 p-b-15">
						<!-- Block2 -->
						<div class="block2">
							<div class="block2-pic hov-img0">
								<img src="images/product-05.jpg" alt="IMG-PRODUCT">

								<a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1">
									Quick View
								</a>
							</div>

							<div class="block2-txt flex-w flex-t p-t-14">
								<div class="block2-txt-child1 flex-col-l ">
									<a href="product-detail.html" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">
										Front Pocket Jumper
									</a>

									<span class="stext-105 cl3">
										$34.75
									</span>
								</div>

								<div class="block2-txt-child2 flex-r p-t-3">
									<a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2">
										<img class="icon-heart1 dis-block trans-04" src="images/icons/icon-heart-01.png" alt="ICON">
										<img class="icon-heart2 dis-block trans-04 ab-t-l" src="images/icons/icon-heart-02.png" alt="ICON">
									</a>
								</div>
							</div>
						</div>
					</div>

					<div class="item-slick2 p-l-15 p-r-15 p-t-15 p-b-15">
						<!-- Block2 -->
						<div class="block2">
							<div class="block2-pic hov-img0">
								<img src="images/product-06.jpg" alt="IMG-PRODUCT">

								<a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1">
									Quick View
								</a>
							</div>

							<div class="block2-txt flex-w flex-t p-t-14">
								<div class="block2-txt-child1 flex-col-l ">
									<a href="product-detail.html" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">
										Vintage Inspired Classic 
									</a>

									<span class="stext-105 cl3">
										$93.20
									</span>
								</div>

								<div class="block2-txt-child2 flex-r p-t-3">
									<a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2">
										<img class="icon-heart1 dis-block trans-04" src="images/icons/icon-heart-01.png" alt="ICON">
										<img class="icon-heart2 dis-block trans-04 ab-t-l" src="images/icons/icon-heart-02.png" alt="ICON">
									</a>
								</div>
							</div>
						</div>
					</div>

					<div class="item-slick2 p-l-15 p-r-15 p-t-15 p-b-15">
						<!-- Block2 -->
						<div class="block2">
							<div class="block2-pic hov-img0">
								<img src="images/product-07.jpg" alt="IMG-PRODUCT">

								<a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1">
									Quick View
								</a>
							</div>

							<div class="block2-txt flex-w flex-t p-t-14">
								<div class="block2-txt-child1 flex-col-l ">
									<a href="product-detail.html" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">
										Shirt in Stretch Cotton
									</a>

									<span class="stext-105 cl3">
										$52.66
									</span>
								</div>

								<div class="block2-txt-child2 flex-r p-t-3">
									<a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2">
										<img class="icon-heart1 dis-block trans-04" src="images/icons/icon-heart-01.png" alt="ICON">
										<img class="icon-heart2 dis-block trans-04 ab-t-l" src="images/icons/icon-heart-02.png" alt="ICON">
									</a>
								</div>
							</div>
						</div>
					</div>

					<div class="item-slick2 p-l-15 p-r-15 p-t-15 p-b-15">
						<!-- Block2 -->
						<div class="block2">
							<div class="block2-pic hov-img0">
								<img src="images/product-08.jpg" alt="IMG-PRODUCT">

								<a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1">
									Quick View
								</a>
							</div>

							<div class="block2-txt flex-w flex-t p-t-14">
								<div class="block2-txt-child1 flex-col-l ">
									<a href="product-detail.html" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">
										Pieces Metallic Printed
									</a>

									<span class="stext-105 cl3">
										$18.96
									</span>
								</div>

								<div class="block2-txt-child2 flex-r p-t-3">
									<a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2">
										<img class="icon-heart1 dis-block trans-04" src="images/icons/icon-heart-01.png" alt="ICON">
										<img class="icon-heart2 dis-block trans-04 ab-t-l" src="images/icons/icon-heart-02.png" alt="ICON">
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
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

											<a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04" href="images/product-detail-01.jpg">
												<i class="fa fa-expand"></i>
											</a>
										</div>
									</div>

									<div class="item-slick3" data-thumb="images/product-detail-02.jpg">
										<div class="wrap-pic-w pos-relative">
											<img src="images/product-detail-02.jpg" alt="IMG-PRODUCT">

											<a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04" href="images/product-detail-02.jpg">
												<i class="fa fa-expand"></i>
											</a>
										</div>
									</div>

									<div class="item-slick3" data-thumb="images/product-detail-03.jpg">
										<div class="wrap-pic-w pos-relative">
											<img src="images/product-detail-03.jpg" alt="IMG-PRODUCT">

											<a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04" href="images/product-detail-03.jpg">
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
								Nulla eget sem vitae eros pharetra viverra. Nam vitae luctus ligula. Mauris consequat ornare feugiat.
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

							<!--  -->
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
    });
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
            gallery: { enabled: true },
            mainClass: 'mfp-fade'
        });
    });
</script>
<script src="vendor/isotope/isotope.pkgd.min.js"></script>
<script src="vendor/sweetalert/sweetalert.min.js"></script>
<script>
    $('.js-addwish-b2, .js-addwish-detail').on('click', function(e){
        e.preventDefault();
    });

    $('.js-addwish-b2').each(function(){
        var nameProduct = $(this).parent().parent().find('.js-name-b2').html();
        $(this).on('click', function(){
            swal(nameProduct, "is added to wishlist!", "success");
            $(this).addClass('js-addedwish-b2');
            $(this).off('click');
        });
    });

    $('.js-addwish-detail').each(function(){
        var nameProduct = $(this).parent().parent().parent().find('.js-name-detail').html();
        $(this).on('click', function(){
            swal(nameProduct, "is added to wishlist!", "success");
            $(this).addClass('js-addedwish-detail');
            $(this).off('click');
        });
    });

	$(document).ready(function () {
    // Show stock warning
    function showStockWarning(message) {
        $('.stock-warning').remove(); // Remove existing warnings
        const warning = `<div class="stock-warning" style="color: red; margin-top: 10px;">${message}</div>`;
        $('.warning').append(warning);
    }

    // Clear stock warning
    function clearStockWarning() {
        $('.stock-warning').remove();
    }

    // Function to lock quantity input
    function lockQuantityInput() {
        $('.num-product').val(0).prop('readonly', true); // Set value to 0 and lock the input
        $('.btn-num-product-up, .btn-num-product-down').prop('disabled', true); // Disable buttons
    }

    // Fetch total quantity in cart for the product
    function checkCartQuantity(productId, productStock) {
        return new Promise((resolve) => {
            $.ajax({
                url: '', // Replace with your URL to fetch cart quantity
                type: 'GET',
                data: { check_cart_qty: true, product_id: productId },
                dataType: 'json',
                success: function (response) {
                    if (response && response.total_qty) {
                        const totalCartQty = parseInt(response.total_qty) || 0;
                        resolve(totalCartQty >= productStock);
                    } else {
                        resolve(false);
                    }
                },
                error: function () {
                    resolve(false);
                }
            });
        });
    }

    // Button Up Quantity Adjustment
    $(document).on('click', '.btn-num-product-up', async function () {
        const $input = $(this).siblings('.num-product');
        const productStock = parseInt($('.js-addcart-detail').data('stock')) || 0;

        const exceedsStock = await checkCartQuantity(productStock);

        if (exceedsStock) {
            showStockWarning("Your quantity in the cart for this product already reached the maximum.");
            $input.val(0);
        } else {
            let currentVal = parseInt($input.val()) || 0;

            if (currentVal < productStock) {
                $input.val(currentVal++);
                clearStockWarning();
            } else {
                showStockWarning(`Only ${productStock} items are available in stock.`);
				$input.val(productStock);
            }
        }
    });

    // Button Down Quantity Adjustment
    $(document).on('click', '.btn-num-product-down', function () {
        const $input = $(this).siblings('.num-product');
        let currentVal = parseInt($input.val()) || 0;

        if (currentVal > 1) {
            $input.val(currentVal - 1);
            clearStockWarning();
        }
    });

    // Add to Cart Functionality
    $(document).on('click', '.js-addcart-detail', async function (event) {
    event.preventDefault();

    const variantId = $('#color-select').find(':selected').data('variant-id'); // Get the selected variant ID
    const productName = $('.js-name-detail').text();
    const productPrice = parseFloat($('.mtext-106').text().replace('$', ''));
    const productQuantity = parseInt($('.num-product').val());
    const productStock = parseInt($(this).data('stock')) || 0;

    if (!variantId) {
        showStockWarning("Please select a color!!!");
        return;
    }

    const exceedsStock = await checkCartQuantity(variantId, productStock);

    if (exceedsStock) {
        showStockWarning("Your quantity in the cart for this product already reached the maximum.");
        lockQuantityInput();
        return;
    }

    if (productQuantity > productStock) {
        showStockWarning(`Cannot add more than ${productStock} items.`);
        return;
    } else if (productQuantity === 0) {
        showStockWarning('Quantity cannot be zero.');
        return;
    }

    const totalPrice = productPrice * productQuantity;

    // Send data to the server via AJAX
    $.ajax({
        url: '', // Replace with your PHP URL to handle adding to the cart
        type: 'POST',
        data: {
            add_to_cart: true,
            variant_id: variantId, // Send the variant ID instead of the product ID
            qty: productQuantity,
            total_price: totalPrice
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                swal(`Peoduct has been added to your cart!`, "", "success");
                clearStockWarning();
            } else {
                showStockWarning(response.error || "Failed to add product to cart.");
            }
        },
        error: function () {
            showStockWarning("An error occurred while adding to the cart.");
        }
    });
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
    // Trigger AJAX call when a different product or promotion needs to be loaded dynamically
    $(document).on('click', '.js-load-product', function(event) {
        event.preventDefault();
        var id = $(this).data('id'); // ID for the product or promotion
        var type = $(this).data('type'); // 'product' or 'promotion'

        // Make an AJAX call to fetch details based on type
        $.ajax({
            url: '', // Ensure this is the URL where details can be fetched
            type: 'GET',
            data: { fetch_product: true, id: id, type: type },
            dataType: 'json',
            success: function(response) {
                if (response) {
                    // Populate the page with data
                    if (type === 'product') {
                        $('.js-name-detail').text(response.product_name);
                        $('.mtext-106').text('$' + response.product_price);
                        $('.stext-102').text(response.product_des);
                    } else if (type === 'promotion') {
                        $('.js-name-detail').text(response.promotion_name);
                        $('.mtext-106').text('$' + response.promotion_price);
                        $('.stext-102').text(response.promotion_des);
                    }

                    // Update Quick View images (example logic for both product and promotion)
                    $('.gallery-lb .item-slick3').each(function(index) {
                        var imagePath = 'images/' + response['Quick_View' + (index + 1)];
                        $(this).find('.wrap-pic-w img').attr('src', imagePath);
                        $(this).find('.wrap-pic-w a').attr('href', imagePath);
                    });
                } else {
                    alert('Details not found.');
                }
            },
            error: function() {
                alert('An error occurred while fetching details.');
            }
        });
    });
</script>

<script>
    // Handle color change
	$('#color-select').change(function() {
        var selectedColor = $(this).val();
        var productId = <?php echo $product_id; ?>; // Current product ID

        if (selectedColor) {
            // Fetch new variant data via AJAX
            $.ajax({
                url: '', // Ensure this is the URL for the request
                type: 'GET',
                data: { fetch_variant_by_color: true, product_id: productId, color: selectedColor },
                dataType: 'json',
                success: function(response) {
                    if (response) {
                        const sizeContainer = $('#size-container');
                        sizeContainer.empty(); // Clear existing sizes
						console.log('Response for Product Variant:', response);

                        // Update product details
                        $('.js-name-detail').text(response.product_name);
                        $('.mtext-106').text('$' + response.product_price);

                        // Update Quick View images
                        $('.gallery-lb .item-slick3').each(function(index) {
                            var imagePath = 'images/' + response['Quick_View' + (index + 1)];
                            $(this).find('.wrap-pic-w img').attr('src', imagePath);
                            $(this).find('.wrap-pic-w a').attr('href', imagePath);
                        });

                        // Update stock
                        $('.js-addcart-detail').data('stock', response.stock);

                        // Populate sizes in the size container
                        if (response.sizes && response.sizes.length > 0) {
                            response.sizes.forEach(function(size) {
                                const sizeOption = $('<div>')
                                    .addClass('size-option')
                                    .text(size);
                                sizeContainer.append(sizeOption);
                            });
                        } else {
                            sizeContainer.append('<div class="no-sizes">No sizes available</div>');
                        }
                    } else {
                        alert('Variant not found for the selected color.');
                    }
                },
                error: function() {
                    alert('An error occurred while fetching variant data.');
                }
            });
        }
    });
</script>
<script>
    // Handle promotion color change
    $('#color-select').change(function () {
        var selectedColor = $(this).val();
        var promotionId = <?php echo $promotion_id; ?>;

        if (selectedColor) {
            $.ajax({
                url: '', // Replace with your URL for fetching promotion variant
                type: 'GET',
                data: { fetch_variant_by_color_promotion: true, promotion_id: promotionId, color: selectedColor },
                dataType: 'json',
                success: function (response) {
                    if (response) {
                        const sizeContainer = $('#size-container');
                        sizeContainer.empty();

						console.log('Response for Product Variant:', response);
						
                        $('.js-name-promotion').text(response.promotion_name);
                        $('.mtext-106').text('$' + response.promotion_price);

						$('.gallery-lb .item-slick3').each(function(index) {
                            var imagePath = 'images/' + response['Quick_View' + (index + 1)];
                            $(this).find('.wrap-pic-w img').attr('src', imagePath);
                            $(this).find('.wrap-pic-w a').attr('href', imagePath);
                        });


                        $('.js-addcart-detail').data('stock', response.stock);

                        if (response.sizes && response.sizes.length > 0) {
                            response.sizes.forEach(function (size) {
                                const sizeOption = $('<div>')
                                    .addClass('size-option')
                                    .text(size);
                                sizeContainer.append(sizeOption);
                            });
                        } else {
                            sizeContainer.append('<div class="no-sizes">No sizes available</div>');
                        }
                    } else {
                        alert('Variant not found for the selected color.');
                    }
                },
                error: function () {
                    alert('An error occurred while fetching promotion variant data.');
                }
            });
        }
    });


</script>

	<script src="js/main.js"></script>
	<script> function openModal(imageSrc) {
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        modalImage.src = imageSrc;
        modal.style.display = 'flex';
    }

    // 关闭模态框
    function closeModal() {
        document.getElementById('imageModal').style.display = 'none';
    }</script>

</body>
</html>
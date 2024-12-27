<?php
session_start(); // Start the session

// Include the database connection file
include("dataconnection.php"); 

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

// Query to fetch package data along with product names
$package_query = "
    SELECT 
        pkg.package_id, 
        pkg.package_name, 
        pkg.package_image, 
        pkg.package_price, 
        pkg.package_description, 
        p1.product_name AS product1_name, 
        p1.product_image AS product1_image,
        p2.product_name AS product2_name, 
        p2.product_image AS product2_image,
        p3.product_name AS product3_name, 
        p3.product_image AS product3_image
    FROM product_package pkg
    LEFT JOIN product p1 ON pkg.product1_id = p1.product_id
    LEFT JOIN product p2 ON pkg.product2_id = p2.product_id
    LEFT JOIN product p3 ON pkg.product3_id = p3.product_id";
$package_result = $connect->query($package_query);

// Handle AJAX request to fetch package details
if (isset($_GET['package_id'])) {
    $package_id = intval($_GET['package_id']);

    $package_query = "
        SELECT 
            pkg.package_id, 
            pkg.package_name,
            pkg.package_stock, 
            p1.product_id AS product1_id, 
            p1.product_name AS product1_name,
            p1.product_image As product1_image, 
            p1.color1 AS product1_color1, 
            p1.color2 AS product1_color2, 
            p1.size1 AS product1_size1, 
            p1.size2 AS product1_size2, 
            p2.product_id AS product2_id,
            p2.product_image As product2_image,  
            p2.product_name AS product2_name, 
            p2.color1 AS product2_color1, 
            p2.color2 AS product2_color2, 
            p2.size1 AS product2_size1, 
            p2.size2 AS product2_size2, 
            p3.product_id AS product3_id, 
            p3.product_name AS product3_name,
            p3.product_image As product3_image, 
            p3.color1 AS product3_color1, 
            p3.color2 AS product3_color2, 
            p3.size1 AS product3_size1, 
            p3.size2 AS product3_size2
        FROM product_package pkg
        LEFT JOIN product p1 ON pkg.product1_id = p1.product_id
        LEFT JOIN product p2 ON pkg.product2_id = p2.product_id
        LEFT JOIN product p3 ON pkg.product3_id = p3.product_id
        WHERE pkg.package_id = $package_id";

    $package_result = $connect->query($package_query);

    if ($package_result && $package_result->num_rows > 0) {
        $package_data = $package_result->fetch_assoc();
        $response = [
            'success' => true,
            'package_stock' => $package_data['package_stock'],
            'products' => []
        ];

        for ($i = 1; $i <= 3; $i++) {
            if (!empty($package_data["product{$i}_id"])) {
                $response['products'][] = [
                    'id' => $package_data["product{$i}_id"],
                    'name' => $package_data["product{$i}_name"],
                    'image' => $package_data["product{$i}_image"],
                    'colors' => [
                        $package_data["product{$i}_color1"],
                        $package_data["product{$i}_color2"]
                    ],
                    'sizes' => [
                        $package_data["product{$i}_size1"],
                        $package_data["product{$i}_size2"]
                    ]
                ];
            }
        }

        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Package not found.']);
    }
    exit;
}

// Count distinct product IDs in the shopping cart for the logged-in user
$distinct_products_query = "SELECT COUNT(DISTINCT product_id) AS distinct_count FROM shopping_cart WHERE user_id = $user_id";
$distinct_products_result = $connect->query($distinct_products_query);
$distinct_count = 0;

if ($distinct_products_result) {
    $row = $distinct_products_result->fetch_assoc();
    $distinct_count = $row['distinct_count'] ?? 0;
}

// Handle adding a package to the cart
// Handle adding a package to the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_package_to_cart'])) {
    // Validate and sanitize inputs
    $package_id = isset($_POST['package_id']) ? intval($_POST['package_id']) : null;
    $user_id = isset($_SESSION['id']) ? intval($_SESSION['id']) : null;
    $package_qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
    $product_colors = [
        $_POST['product1_color'] ?? null,
        $_POST['product2_color'] ?? null,
        $_POST['product3_color'] ?? null,
    ];
    $product_sizes = [
        $_POST['product1_size'] ?? null,
        $_POST['product2_size'] ?? null,
        $_POST['product3_size'] ?? null,
    ];

    // Check if essential inputs are present
    if (!$package_id ) {
        echo json_encode(['success' => false, 'message' => 'Missing package ID']);
        exit;
    }
	if (!$user_id ) {
        echo json_encode(['success' => false, 'message' => 'Missing user ID']);
        exit;
    }

    // Fetch package price
    $package_price_query = "SELECT package_price FROM product_package WHERE package_id = ?";
    $stmt = $connect->prepare($package_price_query);
    $stmt->bind_param('i', $package_id);
    $stmt->execute();
    $package_price_result = $stmt->get_result();

    if ($package_price_result && $package_price_result->num_rows > 0) {
        $row = $package_price_result->fetch_assoc();
        $package_price = $row['package_price'];
        $total_price = $package_qty * $package_price;

        // Insert into shopping cart
        $insert_query = "
            INSERT INTO shopping_cart (
                user_id, package_id, qty, total_price,
                product1_color, product1_size, product2_color, product2_size, product3_color, product3_size
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connect->prepare($insert_query);
        $stmt->bind_param(
            'iiidssssss',
            $user_id, $package_id, $package_qty, $total_price,
            $product_colors[0], $product_sizes[0],
            $product_colors[1], $product_sizes[1],
            $product_colors[2], $product_sizes[2]
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add package to cart.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid package ID.']);
    }

    $stmt->close();
    $connect->close();
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Package</title>
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
    .package-container {
        max-width: 1500px;
        margin: 50px auto;
        padding: 20px;
    }
    .package-card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .package-card img {
        height: 200px;
        object-fit: cover;
    }
    .package-card-title {
        font-size: 1.5rem;
        font-weight: bold;
        color: #343a40;
    }
    .package-card-text {
        color: #6c757d;
    }
    .list-group-item {
        border: none;
        background-color: #f8f9fa;
        color: #495057;
    }
	.popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .popup-content {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        max-width: 600px;
        width: 100%;
        position: relative;
    }

    .close-popup {
        position: absolute;
        top: 10px;
        right: 10px;
        cursor: pointer;
        font-size: 20px;
        font-weight: bold;
    }
    .p-image {
        max-width: 80px;
        height: auto;
        border-radius: 5px;
        margin-right: 15px;
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
								<ul class="sub-menu">
									<li><a href="index.html">Homepage 1</a></li>
									<li><a href="home-02.html">Homepage 2</a></li>
									<li><a href="home-03.html">Homepage 3</a></li>
								</ul>
							</li>

							<li>
								<a href="product.php">Shop</a>
							</li>

							<li class="label1 active-menu" data-label1="hot">
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
    <div class="package-container mt-5">
        <h1 class="mb-4">Winter New Combo!!!</h1>

        <?php
        if ($package_result && $package_result->num_rows > 0) {
            while ($row = $package_result->fetch_assoc()) {
                echo "<div class='package-card mb-4' data-package-id='" . htmlspecialchars($row['package_id']) . "'>";
                echo "  <div class='row g-0'>";
                echo "      <div class='col-md-4'>";
                echo "          <img src='images/" . htmlspecialchars($row['package_image']) . "' class='img-fluid rounded-start' alt='Package Image'>";
                echo "      </div>";
                echo "      <div class='col-md-8'>";
                echo "          <div class='card-body'>";
                echo "              <h5 class='package-card-title'>" . htmlspecialchars($row['package_name']) . "</h5>";
                echo "              <p class='package-card-text'>Price: $" . number_format($row['package_price'], 2) . "</p>";
                echo "              <p class='package-card-text'>" . htmlspecialchars($row['package_description']) . "</p>";
                echo "              <ul class='list-group list-group-flush'>";
                echo "                  <li class='list-group-item'>Product 1: " . htmlspecialchars($row['product1_name']) . "</li>";
                echo "                  <li class='list-group-item'>Product 2: " . htmlspecialchars($row['product2_name']) . "</li>";
                if (!empty($row['product3_name'])) {
                    echo "                  <li class='list-group-item'>Product 3: " . htmlspecialchars($row['product3_name']) . "</li>";
                }
                echo "              </ul>";
                echo "          </div>";
                echo "      </div>";
                echo "  </div>";
				echo "<button class='btn btn-primary selectPackage'>Select Package</button>";
                echo "</div>";
            }
        } else {
            echo "<p class='text-warning'>No packages found.</p>";
        }
        ?>
    </div>
	<div id="packageFormPopup" class="popup-overlay" style="display: none;">
		<div class="popup-content">
			<span class="close-popup">&times;</span>
			<div id="packageFormContainer"></div>
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
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    $(document).on('click', '.selectPackage', function () {
        console.log("Select package button clicked.");
        const packageId = $(this).closest('.package-card').data('package-id');
        console.log("Package ID:", packageId);
        
        if (!packageId) {
            console.error("Package ID is missing.");
            alert("Package ID is missing.");
            return;
        }

        // Fetch package details via AJAX
        $.ajax({
            url: '', // Current PHP file as endpoint
            type: 'GET',
            data: { package_id: packageId },
            dataType: 'json',
            success: function (response) {
                console.log("AJAX success response received:", response);
                if (response.success) {
                    console.log("Response indicates success. Generating form.");
                    let formHtml = `<h3>Select Options for Package</h3>
                        <form id="packageForm" data-package-id="${packageId}" data-package-stock="${response.package_stock}">
                            <input type="hidden" name="package_id" value="${packageId}">`; // Include hidden field for package_id

                    // Generate form fields for each product in the package
                    response.products.forEach((product, index) => {
                        console.log(`Processing product ${index + 1}:`, product);
                        formHtml += `
                            <div class="product">
                                <h4>${product.name}</h4>
                                <img src="images/${product.image}" class="p-image">
                                <label>Color:</label>
                                <select name="product${index + 1}_color">
                                    ${product.colors.filter(Boolean).map(color => `<option value="${color}">${color}</option>`).join('')}
                                </select>
                                <label>Size:</label>
                                <select name="product${index + 1}_size">
                                    ${product.sizes.filter(Boolean).map(size => `<option value="${size}">${size}</option>`).join('')}
                                </select>
                            </div>`;
                    });
                    formHtml += `
                        <div class="qty-controls">
                            <button class="qty-btn minus" type="button">-</button>
                            <input type="number" value="1" min="1" class="qty-input">
                            <button class="qty-btn plus" type="button">+</button>
                            <p class="stock-message" style="color: red; display: none;">The quantity of this package has reached the maximum.</p>
                        </div>`;
                    formHtml += `
                        <button type="submit" class="btn btn-success">Add to Cart</button>
                    </form>`;

                    $('#packageFormContainer').html(formHtml);
                    console.log("Form HTML generated and added to DOM.");
                    $('#packageFormPopup').fadeIn();
                } else {
                    console.error("Response indicates failure:", response.message || "Unknown error.");
                    alert(response.message || "Failed to fetch package details.");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX error occurred:", status, error);
                alert("An error occurred.");
            }
        });
    });
    $(document).on('click', '.qty-btn.plus', function () {
        const $form = $(this).closest('#packageForm');
        const maxStock = parseInt($form.data('package-stock')) || Infinity;
        const $input = $form.find('.qty-input');
        const $message = $form.find('.stock-message');
        const currentQty = parseInt($input.val()) || 1;

        if (currentQty < maxStock) {
            $input.val(currentQty + 1).trigger('change');
            $message.hide();
        } else {
            $message.show();
        }
    });

    $(document).on('click', '.qty-btn.minus', function () {
        const $form = $(this).closest('#packageForm');
        const $input = $form.find('.qty-input');
        const $message = $form.find('.stock-message');
        const currentQty = parseInt($input.val()) || 1;

        if (currentQty > 1) {
            $input.val(currentQty - 1).trigger('change');
            $message.hide();
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

    // Handle form submission
    $(document).on('submit', '#packageForm', function (e) {
        e.preventDefault();
        console.log("Form submitted.");

        const packageId = $(this).data('package-id');
        console.log("Form package ID:", packageId);

        if (!packageId) {
            console.error("Package ID is missing in the form.");
            alert("Package ID is missing. Please try again.");
            return;
        }

        const formData = $(this).serializeArray(); // Get all form data as an array
        formData.push({ name: 'add_package_to_cart', value: true }); // Add a custom flag

        console.log("Serialized form data:", formData);

        $.ajax({
            url: '', // Replace with the correct PHP file path
            type: 'POST',
            data: $.param(formData), // Convert formData to query string format
            dataType: 'json',
            success: function (response) {
                console.log("AJAX success response:", response);
                if (response.success) {
                    console.log("Package successfully added to cart.");
                    alert("Package added to cart!");
                    $('#packageFormPopup').fadeOut();
                } else {
                    console.error("Error adding package to cart:", response.message);
                    alert("Error: " + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
                alert("An error occurred. Please try again.");
            }
        });
    });

    // Close the popup
    $(document).on('click', '.close-popup', function () {
        console.log("Close popup button clicked.");
        $('#packageFormPopup').fadeOut();
    });

    $(document).on('click', '.popup-overlay', function (e) {
        if ($(e.target).is('.popup-overlay')) {
            console.log("Popup overlay clicked.");
            $('#packageFormPopup').fadeOut();
        }
    });
</script>




	<script src="js/main.js"></script>

</body>
</html>
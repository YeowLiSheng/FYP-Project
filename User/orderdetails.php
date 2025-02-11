<?php
session_start();  

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);


$conn->set_charset("utf8mb4");


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


$stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();


if ($user_result && $user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
} else {
    echo "User not found.";
    exit;
}


$current_user_id = $_SESSION['id']; 
$current_user_query = $conn->prepare("SELECT user_name, user_image FROM user WHERE user_id = ?");
$current_user_query->bind_param("i", $current_user_id);
$current_user_query->execute();
$current_user = $current_user_query->get_result()->fetch_assoc();

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
$cart_items_result = $conn->query($cart_items_query);
// Handle AJAX request to delete item

$query = "SELECT * FROM product_variant";
$result = mysqli_query($conn, $query);
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

$distinct_items_result = $conn->query($distinct_items_query);
$distinct_count = 0;

if ($distinct_items_result) {
    $row = $distinct_items_result->fetch_assoc();
    $distinct_count = $row['distinct_count'] ?? 0;
}

if (!isset($_GET['order_id'])) {
    echo "Invalid order ID.";
    exit;
}



$order_id = intval($_GET['order_id']); 


$order_stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.Grand_total, o.discount_amount,
           o.final_amount, o.order_status, o.shipping_address, o.shipping_method, o.user_message,
           u.user_name
    FROM orders o
    JOIN user u ON o.user_id = u.user_id
    WHERE o.order_id = ?
");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    echo "Order not found.";
    exit;
}

$order = $order_result->fetch_assoc();


$details_stmt = $conn->prepare("
    SELECT 
        od.detail_id,
        od.order_id,
        pv.variant_id,
		pv.color,
        COALESCE(p.product_id, pp.promotion_id) AS product_or_promotion_id,
        COALESCE(p.product_name, pp.promotion_name) AS name,
        od.quantity,
        od.unit_price,
        od.total_price,
        pv.Quick_View1 AS image
    FROM order_details od
    JOIN product_variant pv ON od.variant_id = pv.variant_id
    LEFT JOIN product p ON pv.product_id = p.product_id
    LEFT JOIN promotion_product pp ON pv.promotion_id = pp.promotion_id
    WHERE od.order_id = ?
");
$details_stmt->bind_param("i", $order_id);
$details_stmt->execute();
$details_result = $details_stmt->get_result();

$order_details = [];
while ($detail = $details_result->fetch_assoc()) {
    $order_details[] = $detail;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $variant_id = intval($_POST['variant_id']); 
    $rating = intval($_POST['rating']);
    $comment = htmlspecialchars($_POST['comment'], ENT_QUOTES);
    $user_id = $_SESSION['id'];
    $image_path = null;

    
    $detail_query = $conn->prepare("SELECT detail_id FROM order_details WHERE variant_id = ? AND order_id = ?");
    $detail_query->bind_param("ii", $variant_id, $order_id);
    $detail_query->execute();
    $detail_result = $detail_query->get_result();

    if ($detail_result->num_rows === 0) {
        echo "error";
        exit;
    }

    $detail = $detail_result->fetch_assoc();
    $detail_id = $detail['detail_id'];


    if (!empty($_FILES['image']['name'])) {
        $upload_dir = "uploads/reviews/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $image_name = uniqid() . "_" . basename($_FILES['image']['name']);
        $target_path = $upload_dir . $image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_path = $target_path;
        }
    }


$check_stmt = $conn->prepare("SELECT review_id FROM reviews WHERE detail_id = ? AND user_id = ?");
$check_stmt->bind_param("ii", $detail_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo "duplicate"; 
    exit;
}


$stmt = $conn->prepare("
    INSERT INTO reviews (detail_id, rating, comment, image, user_id) 
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("iissi", $detail_id, $rating, $comment, $image_path, $user_id);

if ($stmt->execute()) {
    echo "success"; 
} else {
    echo "error"; 
}
    exit;
}



?>

<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Details</title>
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
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
	<link rel="stylesheet" type="text/css" href="css/main.css">
	<!--===============================================================================================-->
<style>

	
    .main-container {
    display: flex;
    flex-direction: row;
    width: 100%; 

}
    .sidebar {
	width: 250px;
    padding: 20px;
    height: 100%;
    position: static; 
    background-color: #fff;
    border-right: 1px solid #e0e0e0;
    overflow-y: auto;
    flex-shrink: 0;
    z-index: 1; 
}

    .sidebar .user-info {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .sidebar .user-info img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 15px;
    }

    .sidebar .user-info h3 {
        margin: 0;
        font-size: 18px;
        color: #333;
    }

    .sidebar ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }

    .sidebar ul li {
        padding: 10px 15px;
        cursor: pointer;
        display: flex;
        align-items: center;
        border-radius: 5px;
        transition: background-color 0.3s ease;
        font-size: 16px;
        color: #333;
    }

    .sidebar ul li i {
        margin-right: 10px;
        font-size: 18px;
        color: #555;
    }

    .sidebar ul li:hover {
        background-color: #f0f0f0;
    }

    .sidebar ul li.profile-item {
        padding-left: 30px;
        font-size: 14px;
        color: #666;
    }

    .order-details-container {
       
        margin: 0 auto;
        font-family: 'Arial', sans-serif;
        background-color: #f4f4f9;
        color: #333;
        padding: 20px;
        margin: 0;
        flex: 1; 

    }
    .card {
        background: #ffffff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }
    .card h2 {
        font-size: 1.5em;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }
    .icon {
        font-size: 1.2em;
        margin-right: 10px;
        color: #007bff;
    }
    .summary-item {
        display: flex;
        justify-content: space-between;
        margin: 8px 0;
        font-size: 0.95em;
    }
    .product-table {
        width: 100%;
        border-collapse: collapse;
    }
    .product-table th, .product-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    .product-table th {
        background-color: #f9f9f9;
        color: #333;
    }
    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .back-button, .print-button {
        display: inline-block;
        padding: 10px 25px;
        color: #fff;
        text-decoration: none;
        border-radius: 8px;
        margin-top: 20px;
        text-align: center;
        cursor: pointer;
        transition: 0.3s;
    }
    .back-button {
        background: #007bff;
        margin-right: 10px;
    }
    .back-button:hover {
        background: #0056b3;
    }
    .print-button {
        background: #28a745;
        float: right;
    }
    .print-button:hover {
        background: #218838;
    }
    .pricing-item {
        display: flex;
        justify-content: space-between;
        margin: 8px 0;
        font-weight: bold;
    }
	.rate-button {
        display: inline-block;
        padding: 10px 25px;
        color: #fff;
        text-decoration: none;
        border-radius: 8px;
        margin-top: 20px;
        text-align: center;
        cursor: pointer;
        background: #28a745; 
        transition: 0.3s;
    }

    .rate-button:hover {
        background: #e0a800;
    }


.popup-container {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #fff;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 20px;
    z-index: 1600;
    border-radius: 10px;
    width: 400px;
    max-width: 90%;
}

.popup-content {
    text-align: center;
}

.product-select-container {
    position: relative;
    margin-bottom: 20px;
}

.selected-product-preview {
    display: flex;
    flex-direction: column; 
    align-items: center;
    margin-top: 10px;
}

.selected-product-preview img {
    width: 100px; 
    height: 100px;
    border-radius: 10px;
    margin-bottom: 10px;
    object-fit: cover;
}

input[type="file"] {
    display: block;
    margin: 0 auto; 
    padding: 10px;
    font-size: 14px;
    cursor: pointer;
}
.rating-stars {
    display: flex;
    justify-content: center;
    margin-bottom: 10px;
}

.rating-stars .fa-star {
    font-size: 25px;
    color: #ccc;
    cursor: pointer;
    margin: 0 5px;
}

.rating-stars .fa-star.active {
    color: #FFD700;
}

textarea {
    width: 100%;
    resize: none;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 10px;
    margin-bottom: 15px;
}

.submit-button, .cancel-button {
    margin-top: 10px;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
}

.submit-button {
    background-color: #4CAF50;
    color: white;
}

.cancel-button {
    background-color: #f44336;
    color: white;
    margin-left: 10px;
}
.swal2-container {
    z-index: 9999 !important;
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
								echo "HI '" . htmlspecialchars($user['user_name']);
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

<div class="main-container">
    <div class="sidebar">
        <!-- User Info -->
        <div class="user-info">
            <img src="<?= $current_user['user_image'] ?>" alt="User Image">
            <h3><?= $current_user['user_name'] ?></h3>
        </div>
        <ul>
           <!-- My Account -->
		   <li><i class="fa fa-user"></i> My Account</li>
            <!-- Profile items directly below My Account with indentation -->

<li class="profile-item">
    <a href="edit_profile.php">
        <i class="fa fa-edit"></i> Edit Profile
    </a>
</li>           

            <!-- My Orders -->
			<li><a href="Order.php"><i class="fa fa-box"></i> My Orders</a></li>
			</ul>
    </div>

<div class="order-details-container">
    
    <div class="card">
        <h2><span class="icon">🆔</span> Order ID: <?= $order['order_id'] ?></h2>
    </div>

    <div class="card">
        <h2><span class="icon">📋</span>Order Summary</h2>
        <div class="summary-item"><strong>User:</strong> <span><?= $order['user_name'] ?></span></div>
        <div class="summary-item"><strong>Order Date:</strong> <span><?= date("Y-m-d H:i:s", strtotime($order['order_date'])) ?></span></div>
        <div class="summary-item"><strong>Status:</strong> <span><?= $order['order_status'] ?></span></div>
        <div class="summary-item"><strong>Shipping Address:</strong> <span><?= $order['shipping_address'] ?></span></div>
        <div class="summary-item"><strong>Shipping Method:</strong> <span><?= $order['shipping_method'] ?></span></div>
        <div class="summary-item"><strong>User Message:</strong> <span><?= !empty($order['user_message']) ? htmlspecialchars($order['user_message']) : 'N/A' ?></span></div>           
    </div>


    <div class="card">
        <h2><span class="icon">🛒</span>Product Details</h2>
        <table class="product-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product Name</th>
					<th>Color</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
			<?php foreach ($order_details as $detail) { ?>
                <tr>
    <td><img src="images/<?= $detail['image'] ?>" alt="<?= $detail['name'] ?>" class="product-image"></td>
    <td><?= $detail['name'] ?></td>
	<td><?= $detail['color']?></td>
    <td><?= $detail['quantity'] ?></td>
    <td>$ <?= number_format($detail['unit_price'], 2) ?></td>
    <td>$ <?= number_format($detail['total_price'], 2) ?></td>
</tr>
                <?php } ?>
            </tbody>
        </table>
    </div>


    <div class="card">
        <h2><span class="icon">💰</span>Pricing Details</h2>
        <div class="pricing-item"><span>Grand Total:</span><span>$ <?= number_format($order['Grand_total'], 2) ?></span></div>
        <div class="pricing-item"><span>Discount:</span><span>- $<?= number_format($order['discount_amount'], 2) ?></span></div>
        <div class="pricing-item"><span>Final Amount:</span><span>$ <?= number_format($order['final_amount'], 2) ?></span></div>
    </div>


    <a href="order.php" class="back-button">Back to Orders</a>
    <a href="receipt.php?order_id=<?= $order['order_id'] ?>" class="print-button">🖨️ Print Receipt</a>
	<?php if ($order['order_status'] === 'Complete') { ?>
		<a href="javascript:void(0);" class="rate-button" onclick="openPopup()">⭐ Rate Order</a>
<?php } ?>
<div id="ratePopup" class="popup-container" style="display: none;">
    <div class="popup-content">
        <h2>Rate Product</h2>
        <form id="rateForm" method="POST" enctype="multipart/form-data">
  
            <label for="productSelect">Select Product:</label>
            <div class="product-select-container">
                <select id="productSelect" name="variant_id" required>
                    <option value="" disabled selected>Select a product</option>
                    <?php foreach ($order_details as $detail) { ?>
						<option value="<?= $detail['variant_id'] ?>" 
						data-img="images/<?= $detail['image'] ?>">
                            <?= $detail['name'] ?>
                        </option>
                    <?php } ?>
                </select>
                <div class="selected-product-preview" id="productPreview">
                    <img id="productImage" src="" alt="Product Image" style="display: none;" />
                    <span id="productName" style="display: block;"></span>
                </div>
            </div>

            
            <label for="rating">Rating:</label>
            <div id="stars" class="rating-stars">
                <?php for ($i = 1; $i <= 5; $i++) { ?>
                    <i class="fa fa-star" data-value="<?= $i ?>"></i>
                <?php } ?>
            </div>
            <input type="hidden" id="rating" name="rating" value="" required>

           
            <label for="comment">Comment:</label>
            <textarea id="comment" name="comment" rows="4" required></textarea>

        
            <label for="image">Upload Image (optional):</label>
            <input type="file" id="image" name="image" accept="image/*">

         
            <button type="submit" class="submit-button">Submit</button>
            <button type="button" class="cancel-button" onclick="closePopup()">Cancel</button>
        </form>
    </div>
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
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
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

function openPopup() {
    document.getElementById("ratePopup").style.display = "block";
}


function closePopup() {
    document.getElementById("ratePopup").style.display = "none";
    document.getElementById("rateForm").reset(); 
    resetStars();   
    resetProductPreview(); 
}

document.getElementById("rateForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    fetch(window.location.href, {
        method: "POST",
        body: formData
    })
        .then(response => response.text())
        .then(data => {
            const handleSwalPopup = (icon, title, text) => {
                Swal.fire({
                    icon: icon,
                    title: title,
                    text: text,
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'swal-popup-highest'
                    },
                    didOpen: () => {
                        const swalContainer = document.querySelector('.swal2-container');
                        if (swalContainer) {
                            swalContainer.style.zIndex = '9999'; 
                        }
                    }
                }).then(() => {
                    redirectToPage();
                });
            };

            if (data.trim() === "success") {
                handleSwalPopup('success', 'Review Submitted', 'Your review has been successfully submitted!');
            } else if (data.trim() === "duplicate") {
                handleSwalPopup('warning', 'Duplicate Review', 'You have already reviewed this product.');
            } else {
                handleSwalPopup('error', 'Submission Failed', 'Failed to submit review. Please try again.');
            }
        })
        .catch(error => {
            console.error("Error submitting review:", error);
        });
});


function redirectToPage() {
    window.location.href = "orderdetails.php?order_id=<?= $order_id ?>";
}

const stars = document.querySelectorAll(".rating-stars .fa-star");
stars.forEach(star => {
    star.addEventListener("click", function () {
        const value = this.getAttribute("data-value");
        document.getElementById("rating").value = value;

        stars.forEach(s => s.classList.remove("active"));
        for (let i = 0; i < value; i++) {
            stars[i].classList.add("active");
        }
    });
});

function resetStars() {
    stars.forEach(star => star.classList.remove("active"));
}


const productSelect = document.getElementById("productSelect");
const productImage = document.getElementById("productImage");
const productName = document.getElementById("productName");

productSelect.addEventListener("change", function () {
    const selectedOption = productSelect.options[productSelect.selectedIndex];
    const imgSrc = selectedOption.getAttribute("data-img");
    const name = selectedOption.textContent;

    if (imgSrc) {
        productImage.src = imgSrc;
        productImage.style.display = "block";
    } else {
        productImage.style.display = "none";
    }

    productName.textContent = name;
});

function resetProductPreview() {
    productImage.style.display = "none";
    productName.textContent = "";
}



</script>
</body>
</html>

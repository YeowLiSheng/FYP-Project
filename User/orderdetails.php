<?php
session_start();  // 启动会话

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

// 设置字符集
$conn->set_charset("utf8mb4");

// 检查连接
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

// 使用预处理语句来防止 SQL 注入
$stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

// 获取用户信息
if ($user_result && $user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
} else {
    echo "User not found.";
    exit;
}

// 获取当前用户的详细信息（动态获取用户ID）
$current_user_id = $_SESSION['id']; 
$current_user_query = $conn->prepare("SELECT user_name, user_image FROM user WHERE user_id = ?");
$current_user_query->bind_param("i", $current_user_id);
$current_user_query->execute();
$current_user = $current_user_query->get_result()->fetch_assoc();

// 获取订单 ID
if (!isset($_GET['order_id'])) {
    echo "Invalid order ID.";
    exit;
}



$order_id = intval($_GET['order_id']); // 或使用适当的获取方式

// 使用预处理语句获取订单信息
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

// 获取订单详情
$details_stmt = $conn->prepare("
    SELECT od.detail_id, od.order_id, od.quantity, od.unit_price, od.total_price,
           CASE
               WHEN od.product_id IS NOT NULL THEN p.product_name
               ELSE pp.package_name
           END AS item_name,
           CASE
               WHEN od.product_id IS NOT NULL THEN p.product_image
               ELSE pp.package_image
           END AS item_image
    FROM order_details od
    LEFT JOIN product p ON od.product_id = p.product_id
    LEFT JOIN product_package pp ON od.package_id = pp.package_id
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
	$detail_id = intval($_POST['detail_id']);
    $rating = intval($_POST['rating']);
    $comment = htmlspecialchars($_POST['comment'], ENT_QUOTES);
    $user_id = $_SESSION['id'];
    $image_path = null;

	
    // 处理图片上传
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

    // 检查是否存在重复评论
$check_stmt = $conn->prepare("SELECT review_id FROM reviews WHERE detail_id = ? AND user_id = ?");
$check_stmt->bind_param("ii", $detail_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo "duplicate"; // 返回重复状态
    exit;
}

// 插入评论数据
$stmt = $conn->prepare("
    INSERT INTO reviews (detail_id, rating, comment, image, user_id) 
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("iissi", $detail_id, $rating, $comment, $image_path, $user_id);

if ($stmt->execute()) {
    echo "success"; // 向前端返回成功状态
} else {
    echo "error"; // 向前端返回错误状态
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
    /* 全局样式 */
	
    .main-container {
    display: flex;
    flex-direction: row;
    width: 100%; /* 确保容器宽度为全屏 */

}
    .sidebar {
	width: 250px;
    padding: 20px;
    height: 100%;
    position: static; /* 保持 static */
    background-color: #fff;
    border-right: 1px solid #e0e0e0;
    overflow-y: auto;
    flex-shrink: 0;
    z-index: 1; /* 设置层级，确保 sidebar 不会覆盖其他内容 */
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
        flex: 1; /* 让容器填满 sidebar 旁边的剩余空间 */

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
        background: #28a745; /* 使用黄色作为评分按钮颜色 */
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
    z-index: 2000;
    border-radius: 10px;
    width: 400px;
    max-width: 90%;
}

.popup-content {
    text-align: center;
}

.product-item-container {
    position: relative;
    margin-bottom: 20px;
}

.selected-item-preview {
    display: flex;
    flex-direction: column; /* 垂直对齐 */
    align-items: center;
    margin-top: 10px;
}

.selected-item-preview img {
    width: 100px; /* 调整图片大小 */
    height: 100px;
    border-radius: 10px;
    margin-bottom: 10px;
    object-fit: cover;
}

input[type="file"] {
    display: block;
    margin: 0 auto; /* 居中 */
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
#overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}
.popup-success {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    padding: 20px 40px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    text-align: center;
	z-index: 2000;
    animation: fadeIn 0.5s ease;
}

.success-content {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.success-icon {
    font-size: 60px;
    color: #28a745; /* 绿色图标 */
    margin-bottom: 15px;
}

.popup-success h3 {
    font-size: 20px;
    color: #333;
}

/* 淡入动画 */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translate(-50%, -60%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
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
					<div class="left-top-bar">
						Free shipping for standard order over $100
					</div>

					<div class="right-top-bar flex-w h-full">
						<a href="#" class="flex-c-m trans-04 p-lr-25">
							Help & FAQs
						</a>



						<a href="#" class="flex-c-m trans-04 p-lr-25">
							EN
						</a>

						<a href="#" class="flex-c-m trans-04 p-lr-25">
							USD
						</a>




						<a href="edit_profile.php?edit_user=<?php echo $user_id; ?>" class="flex-c-m trans-04 p-lr-25">
							<?php
							echo "HI '" . htmlspecialchars($user["user_name"]);
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
					<a href="#" class="logo">
						<img src="images/icons/logo-01.png" alt="IMG-LOGO">
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

						<div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti js-show-cart"
							data-notify="2">
							<i class="zmdi zmdi-shopping-cart"></i>
						</div>

						<a href="#" class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti"
							data-notify="0">
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

				<div class="icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti js-show-cart"
					data-notify="2">
					<i class="zmdi zmdi-shopping-cart"></i>
				</div>

				<a href="#" class="dis-block icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti"
					data-notify="0">
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
					<a href="dashboard.php">Home</a>
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
					<a href="product.php">Shop</a>
				</li>

				<li>
					<a href="shoping-cart.php" class="label1 rs1" data-label1="hot">Features</a>
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
					<?php
					// Display combined cart items
					$total_price = 0;
					if ($cart_items_result->num_rows > 0) {
						while ($cart_item = $cart_items_result->fetch_assoc()) {
							$total_price += $cart_item['total_price'];
							echo '
                        <li class="header-cart-item flex-w flex-t m-b-12">
                            <div class="header-cart-item-img">
                                <img src="images/' . $cart_item['product_image'] . '" alt="IMG">
                            </div>
                            <div class="header-cart-item-txt p-t-8">
                                <a href="#" class="header-cart-item-name m-b-18 hov-cl1 trans-04">
                                    ' . $cart_item['product_name'] . '
                                </a>
                                <span class="header-cart-item-info">
                                    ' . $cart_item['total_qty'] . ' x $' . number_format($cart_item['product_price'], 2) . '
                                </span>
                            </div>
                        </li>';
						}
					} else {
						echo '<p>Your cart is empty.</p>';
					}
					?>
				</ul>

				<div class="w-full">
					<div class="header-cart-total w-full p-tb-40">
						Total: RM<span id="cart-total"><?php echo number_format($total_price, 2); ?></span>
					</div>

					<div class="header-cart-buttons flex-w w-full">
						<a href="shoping-cart.php"
							class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-r-8 m-b-10">
							View Cart
						</a>

						<a href="shoping-cart.html"
							class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-b-10">
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
            <li class="profile-item"><i class="fa fa-id-card"></i> My Profile</li>
            <li class="profile-item"><i class="fa fa-edit"></i> Edit Profile</li>
            <li class="profile-item"><i class="fa fa-lock"></i> Change Password</li>
            <!-- My Orders -->
            <li><i class="fa fa-box"></i> My Orders</li>
        </ul>
    </div>

<div class="order-details-container">
    
    <div class="card">
        <h2><span class="icon">🆔</span> Order ID: <?= $order['order_id'] ?></h2>
    </div>
    <!-- 订单概要 -->
    <div class="card">
        <h2><span class="icon">📋</span>Order Summary</h2>
        <div class="summary-item"><strong>User:</strong> <span><?= $order['user_name'] ?></span></div>
        <div class="summary-item"><strong>Order Date:</strong> <span><?= date("Y-m-d H:i:s", strtotime($order['order_date'])) ?></span></div>
        <div class="summary-item"><strong>Status:</strong> <span><?= $order['order_status'] ?></span></div>
        <div class="summary-item"><strong>Shipping Address:</strong> <span><?= $order['shipping_address'] ?></span></div>
        <div class="summary-item"><strong>Shipping Method:</strong> <span><?= $order['shipping_method'] ?></span></div>
        <div class="summary-item"><strong>User Message:</strong> <span><?= !empty($order['user_message']) ? htmlspecialchars($order['user_message']) : 'N/A' ?></span></div>           
    </div>

    <!-- 产品明细 -->
    <div class="card">
        <h2><span class="icon">🛒</span>Purchasing Details</h2>
        <table class="product-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
			<?php foreach ($order_details as $detail) { ?>
                <tr>
				<td><img src="images/<?= $detail['item_image'] ?>" alt="<?= $detail['item_name'] ?>" class="product-image"></td>
                <td><?= $detail['item_name'] ?></td>
                    <td><?= $detail['quantity'] ?></td>
                    <td>RM <?= number_format($detail['unit_price'], 2) ?></td>
                    <td>RM <?= number_format($detail['total_price'], 2) ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- 价格明细 -->
    <div class="card">
        <h2><span class="icon">💰</span>Pricing Details</h2>
        <div class="pricing-item"><span>Grand Total:</span><span>RM <?= number_format($order['Grand_total'], 2) ?></span></div>
        <div class="pricing-item"><span>Discount:</span><span>- RM <?= number_format($order['discount_amount'], 2) ?></span></div>
        <div class="pricing-item"><span>Final Amount:</span><span>RM <?= number_format($order['final_amount'], 2) ?></span></div>
    </div>

    <!-- 操作按钮 -->
    <a href="order.php" class="back-button">Back to Orders</a>
    <a href="receipt.php?order_id=<?= $order['order_id'] ?>" class="print-button">🖨️ Print Receipt</a>
	<?php if ($order['order_status'] === 'Complete') { ?>
		<a href="javascript:void(0);" class="rate-button" onclick="openPopup()">⭐ Rate Order</a>
<?php } ?>
<div id="ratePopup" class="popup-container" style="display: none;">
    <div class="popup-content">
        <h2>Rate Product</h2>
        <form id="rateForm" method="POST" enctype="multipart/form-data">
            <!-- 产品选择 -->
            <label for="itemSelect">Select Item:</label>
            <div class="item-select-container">
			<select id="itemSelect" name="detail_id" required>
    <option value="" disabled selected>Select an Item</option>
    <?php foreach ($order_details as $detail) { ?>
        <option value="<?= $detail['detail_id'] ?>" 
                data-img="images/<?= $detail['item_image'] ?>">
            <?= $detail['item_name'] ?>
        </option>
    <?php } ?>
</select>
                <div class="selected-item-preview" id="itemPreview">
                    <img id="itemImage" src="" alt="item Image" style="display: none;" />
                    <span id="itemName" style="display: block;"></span>
                </div>
            </div>

            <!-- 评分 -->
            <label for="rating">Rating:</label>
            <div id="stars" class="rating-stars">
                <?php for ($i = 1; $i <= 5; $i++) { ?>
                    <i class="fa fa-star" data-value="<?= $i ?>"></i>
                <?php } ?>
            </div>
            <input type="hidden" id="rating" name="rating" value="" required>

            <!-- 评论 -->
            <label for="comment">Comment:</label>
            <textarea id="comment" name="comment" rows="4" required></textarea>

            <!-- 上传图片 -->
            <label for="image">Upload Image (optional):</label>
            <input type="file" id="image" name="image" accept="image/*">

            <!-- 按钮 -->
            <button type="submit" class="submit-button">Submit</button>
            <button type="button" class="cancel-button" onclick="closePopup()">Cancel</button>
        </form>
    </div>
</div>
<div id="overlay" style="display: none;"></div>

<div id="successPopup" class="popup-success" style="display: none;">
    <div class="success-content">
        <div class="success-icon">
            <i class="fa fa-check-circle"></i>
        </div>
        <h3>Review Submitted Successfully!</h3>
		<button class="submit-button" onclick="redirectToPage()">OK</button>

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
						Any questions? Let us know in store at 8th floor, 379 Hudson St, New York, NY 10018 or call us
						on (+1) 96 716 6879
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
							<input class="input1 bg-none plh1 stext-107 cl7" type="text" name="email"
								placeholder="email@example.com">
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
					Copyright &copy;
					<script>document.write(new Date().getFullYear());</script> All rights reserved | Made with <i
						class="fa fa-heart-o" aria-hidden="true"></i> by <a href="https://colorlib.com"
						target="_blank">Colorlib</a> &amp; distributed by <a href="https://themewagon.com"
						target="_blank">ThemeWagon</a>
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

											<a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04"
												href="images/product-detail-01.jpg">
												<i class="fa fa-expand"></i>
											</a>
										</div>
									</div>

									<div class="item-slick3" data-thumb="images/product-detail-02.jpg">
										<div class="wrap-pic-w pos-relative">
											<img src="images/product-detail-02.jpg" alt="IMG-PRODUCT">

											<a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04"
												href="images/product-detail-02.jpg">
												<i class="fa fa-expand"></i>
											</a>
										</div>
									</div>

									<div class="item-slick3" data-thumb="images/product-detail-03.jpg">
										<div class="wrap-pic-w pos-relative">
											<img src="images/product-detail-03.jpg" alt="IMG-PRODUCT">

											<a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04"
												href="images/product-detail-03.jpg">
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
								Nulla eget sem vitae eros pharetra viverra. Nam vitae luctus ligula. Mauris consequat
								ornare feugiat.
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

											<input class="mtext-104 cl3 txt-center num-product" type="number"
												name="num-product" value="1">

											<div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m">
												<i class="fs-16 zmdi zmdi-plus"></i>
											</div>
										</div>

										<button
											class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 trans-04 js-addcart-detail">
											Add to cart
										</button>
									</div>
								</div>
							</div>

							<!--  -->
							<div class="flex-w flex-m p-l-100 p-t-40 respon7">
								<div class="flex-m bor9 p-r-10 m-r-11">
									<a href="#"
										class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 js-addwish-detail tooltip100"
										data-tooltip="Add to Wishlist">
										<i class="zmdi zmdi-favorite"></i>
									</a>
								</div>

								<a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 m-r-8 tooltip100"
									data-tooltip="Facebook">
									<i class="fa fa-facebook"></i>
								</a>

								<a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 m-r-8 tooltip100"
									data-tooltip="Twitter">
									<i class="fa fa-twitter"></i>
								</a>

								<a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 m-r-8 tooltip100"
									data-tooltip="Google Plus">
									<i class="fa fa-google-plus"></i>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
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
// 打开弹窗
// 打开弹窗
function openPopup() {
    document.getElementById("ratePopup").style.display = "block";
}

// 关闭弹窗
function closePopup() {
    document.getElementById("ratePopup").style.display = "none";
    document.getElementById("rateForm").reset(); // 重置表单
    resetStars();   // 重置评分星星
    resetProductPreview(); // 重置产品预览
}

// 禁用重复提交
document.getElementById("rateForm").addEventListener("submit", function (e) {
    // 阻止默认表单提交行为
    e.preventDefault();

    // 获取表单元素
    const form = e.target;
    const formData = new FormData(form);

    // 发送表单数据到后端
    fetch(window.location.href, {
        method: "POST",
        body: formData
    })
        .then(response => response.text())
        .then(data => {
            // 检查后端响应
			if (data.trim() === "success") {
    // 显示成功弹窗
    document.getElementById("successPopup").style.display = "block";
} else if (data.trim() === "duplicate") {
    alert("You have already reviewed this product.");
} else {
    alert("Failed to submit review. Please try again.");
}
        })
        .catch(error => {
            console.error("Error submitting review:", error);
        });
});

function redirectToPage() {
    window.location.href = "orderdetails.php?order_id=<?= $order_id ?>";
}
// 评分逻辑
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
const itemSelect = document.getElementById("itemSelect");
const itemImage = document.getElementById("itemImage");
const itemName = document.getElementById("itemName");

itemSelect.addEventListener("change", function () {
    const selectedOption = itemSelect.options[itemSelect.selectedIndex];
    const imgSrc = selectedOption.getAttribute("data-img");
    const name = selectedOption.textContent;

    if (imgSrc) {
        itemImage.src = imgSrc;
        itemImage.style.display = "block";
    } else {
        itemImage.style.display = "none";
    }

    itemName.textContent = name;
});

function resetProductPreview() {
    itemImage.style.display = "none";
    itemName.textContent = "";
}


</script>
</body>
</html>

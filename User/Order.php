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

function fetchOrdersWithProducts($conn, $user_id, $status = null) {
    $sql = "
        SELECT 
            o.order_id, 
            o.order_date, 
            o.final_amount, 
            o.order_status, 
            GROUP_CONCAT(
                CASE 
                    WHEN pv.product_id IS NOT NULL THEN p.product_name
                    WHEN pv.promotion_id IS NOT NULL THEN pp.promotion_name
                END
                SEPARATOR ', '
            ) AS products, 
            pv.Quick_View1 AS product_image
        FROM orders o
        JOIN order_details od ON o.order_id = od.order_id
        JOIN product_variant pv ON od.variant_id = pv.variant_id
        LEFT JOIN product p ON pv.product_id = p.product_id
        LEFT JOIN promotion_product pp ON pv.promotion_id = pp.promotion_id
        WHERE o.user_id = ?"; 

   
    if ($status) {
        $sql .= " AND o.order_status = ?";
    }

    $sql .= " GROUP BY o.order_id 
              ORDER BY o.order_date DESC";

    $stmt = $conn->prepare($sql);
    if ($status) {
        $stmt->bind_param("is", $user_id, $status); 
    } else {
        $stmt->bind_param("i", $user_id); 
    }
    $stmt->execute();
    return $stmt->get_result();
}

// Fetch orders for each tab
$all_orders = fetchOrdersWithProducts($conn, $user_id);
$processing_orders = fetchOrdersWithProducts($conn, $user_id, 'Processing');
$shipping_orders = fetchOrdersWithProducts($conn, $user_id, 'Shipping');
$complete_orders = fetchOrdersWithProducts($conn, $user_id, 'Complete');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_order'])) {
   
    $order_id = intval($_POST['order_id']);
    
    
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        
        $update_stmt = $conn->prepare("UPDATE orders SET order_status = 'Complete' WHERE order_id = ?");
        $update_stmt->bind_param("i", $order_id);

        if ($update_stmt->execute()) {
			echo "
				<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
				<script>
					document.addEventListener('DOMContentLoaded', function() {
						Swal.fire({
							icon: 'success',
							title: 'Order Status Updated Successfully',
							text: 'Thank you for confirming your order.',
							confirmButtonText: 'OK'
						}).then((result) => {
							if (result.isConfirmed) {
								window.location.href = '" . $_SERVER['PHP_SELF'] . "';
							}
						});
					});
				</script>";
		} else {
			echo "
				<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
				<script>
					document.addEventListener('DOMContentLoaded', function() {
						Swal.fire({
							icon: 'error',
							title: 'Failed to Update Order Status',
							text: 'Please try again later.',
							confirmButtonText: 'OK'
						});
					});
				</script>";
		}
		} else {
			echo "
				<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
				<script>
					document.addEventListener('DOMContentLoaded', function() {
						Swal.fire({
							icon: 'error',
							title: 'Invalid Order',
							text: 'Invalid order or permission denied.',
							confirmButtonText: 'OK'
						});
					});
				</script>";
		}
    $stmt->close();
    $update_stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>Home</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
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
    /* General layout styling */
    /* General layout styling */
.my-account-container {
    display: flex;
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

    .content {
        flex: 1;
        padding: 20px;
        background-color: #f9f9f9;
        min-height: 100vh;
    }

    .tabs {
        display: flex;
        border-bottom: 2px solid #e0e0e0;
        margin-bottom: 20px;
    }

    .tabs button {
        background: none;
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
    }

    .tabs button.active {
        color: #4caf50;
        border-bottom: 2px solid #4caf50;
    }

	.order-summary {
    display: grid;
    grid-template-columns: 100px 1fr;
    gap: 20px;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 12px;
    background-color: #f7f9fc; 
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    width: 100%;
    cursor: pointer;
    position: relative; 
}

.order-summary:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.order-summary img {
    width: 100%;
    height: 100px; 
    border-radius: 10px;
    object-fit: cover;
}

.order-summary div {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 10px; 
}

.order-summary h3 {
    font-size: 20px;
    color: #333;
    margin-bottom: 5px;
}

.order-summary p {
    font-size: 14px;
    color: #555;
    margin: 3px 0;
    line-height: 1.4;
}

.order-summary p span {
    font-weight: bold;
}

.order-summary .order-status {
    font-size: 14px;
    color: #fff;
    background-color: #4caf50;
    padding: 5px 10px;
    border-radius: 5px;
    display: inline-block;
    align-self: flex-start;
    margin-bottom: 5px;
}

.order-summary .additional-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
}

.order-summary .additional-info p {
    margin: 0;
    font-size: 14px;
}

.order-summary .price-info {
    font-size: 18px;
    font-weight: bold;
    color: #333;
    margin-left: auto;
}
.complete-btn {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    position: absolute; 
    bottom: 20px; 
    right: 20px; 
    transition: background-color 0.3s ease;
}

.complete-btn:hover {
    background-color: #45a049;
}
.popup-form {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.popup-content {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    width: 300px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.popup-content h2 {
    margin-bottom: 15px;
    font-size: 18px;
}

.popup-content button {
    margin: 10px 5px;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.popup-confirm-btn {
    background-color: #28a745;
    color: white;
}

.popup-cancel-btn {
    background-color: #dc3545;
    color: white;
}
.no-orders {
    text-align: center;
    margin-top: 50px;
    background-color: #f9f9f9;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}


.pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 20px 0;
        gap: 5px;
    }
    .pagination .page-btn {
    margin: 0;
    padding: 10px 15px; 
    border: 1px solid #007bff; 
    background-color: #f8f9fa; 
    color: #007bff; 
    cursor: pointer;
    border-radius: 5px;
    font-size: 1em; 
    transition: background-color 0.3s, color 0.3s; 
}

.pagination .page-btn.active {
    background-color: #007bff; 
    color: white; 
    font-weight: bold; 
}

.pagination .page-btn:hover {
    background-color: #0056b3; 
    color: white; 
}

.pagination .page-btn:disabled {
    background-color: #e9ecef; 
    color: #6c757d; 
    cursor: not-allowed;
    border-color: #ced4da; 
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

					
	<!-- Main Container -->
<div class="my-account-container">
    <!-- Sidebar -->
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

    <!-- Content Area -->
    <div class="content">
        <h1>My Orders</h1>
        <!-- Tab Buttons -->
        <div class="tabs">
            <button id="All-tab" onclick="showTab('All')" class="active">All</button>
            <button id="Processing-tab" onclick="showTab('Processing')">Processing</button>
            <button id="Shipping-tab" onclick="showTab('Shipping')">Shipping</button>
            <button id="Complete-tab" onclick="showTab('Complete')">Complete</button>
        </div>

        <!-- Order Containers for Each Status -->
        <?php
       function renderOrders($orders, $showCompleteButton = false) {
		if ($orders->num_rows > 0) {
			
			while ($order = $orders->fetch_assoc()) {
				echo '

                    <div class="order-summary" onclick="window.location.href=\'orderdetails.php?order_id=' . $order['order_id'] . '\'">
					<img src="images/' . $order['product_image'] . '" alt="Product Image">
					<div>
						<h3><i class="fa fa-box"></i> Order #' . $order['order_id'] . '</h3>
						<p><i class="fa fa-calendar-alt"></i> Date: ' . date("Y-m-d", strtotime($order['order_date'])) . '</p>
						<p><i class="fa fa-tag"></i> Products: ' . $order['products'] . '</p>
						<p><i class="fa fa-dollar-sign"></i> Total Price: $ ' . $order['final_amount'] . '</p>';
						
				// if need "Complete" button
				if ($showCompleteButton) {
					echo '
						<button type="button" class="complete-btn" 
 onclick="openPopup(event, ' . $order['order_id'] . ')">
 							Complete
						</button>';
				}
	
				echo '
					</div>
				</div>';
			}
		} else {
			echo '
			<div class="no-orders">
				<p><i class="fa fa-ice-cream"></i> Nothing to show here.</p>
				<button onclick="window.location.href=\'shop.php\'">Continue Shopping</button>
			</div>';
		}
	}
	
        ?>

        <!-- All Orders -->
        <div class="order-container" id="All" style="display: block;">
            <?php renderOrders($all_orders); ?>
			<div class="pagination" id="pagination-All"></div>

        </div>

        <!-- Processing Orders -->
        <div class="order-container" id="Processing" style="display: none;">
            <?php renderOrders($processing_orders); ?>
			<div class="pagination" id="pagination-Processing"></div>

        </div>

        <!-- Shipping Orders -->
        <div class="order-container" id="Shipping" style="display: none;">
    <?php renderOrders($shipping_orders, true); ?>
	<div class="pagination" id="pagination-Shipping"></div>

</div>

        <!-- Completed Orders -->
        <div class="order-container" id="Complete" style="display: none;">
            <?php renderOrders($complete_orders); ?>
			<div class="pagination" id="pagination-Complete"></div>

        </div>
    </div>

	<div id="popup-form" class="popup-form" style="display: none;">
    <div class="popup-content">
        <h2>Confirm Order Completion</h2>
        <p>Are you sure you have received your order?</p>
        <form method="post">
            <input type="hidden" id="popup-order-id" name="order_id">
            <button type="submit" name="complete_order" class="popup-confirm-btn">Yes</button>
            <button type="button" class="popup-cancel-btn" onclick="closePopup()">No</button>
        </form>
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
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
    function showTab(status) {
        document.querySelectorAll('.order-container').forEach(container => {
            container.style.display = container.id === status ? 'block' : 'none';
        });
        document.querySelectorAll('.tabs button').forEach(button => {
            button.classList.remove('active');
        });
        document.getElementById(status + '-tab').classList.add('active');
    }

	function openPopup(event, orderId) {
    event.stopPropagation(); 
    document.getElementById("popup-order-id").value = orderId;
    document.getElementById("popup-form").style.display = "flex";
}

function closePopup() {
    document.getElementById("popup-form").style.display = "none";
}

document.addEventListener("DOMContentLoaded", function () {
    const rowsPerPage = 1; // Adjust the number of rows per page as needed
    const tabIds = ["All", "Processing", "Shipping", "Complete"];

    // Initialize pagination for each tab
    tabIds.forEach((tabId) => {
        const container = document.getElementById(tabId);
        const rows = Array.from(container.querySelectorAll(".order-summary"));
        const pagination = document.getElementById(`pagination-${tabId}`);

        if (rows.length === 0) {
            pagination.innerHTML = "";
            return;
        }

        let currentPage = 1;

        function initPagination() {
            const totalRows = rows.length;
            const totalPages = Math.ceil(totalRows / rowsPerPage);
            pagination.innerHTML = "";

            const prevButton = document.createElement("button");
            prevButton.textContent = "Previous";
            prevButton.disabled = currentPage === 1;
            prevButton.classList.add("page-btn");
            prevButton.addEventListener("click", () => goToPage(currentPage - 1));
            pagination.appendChild(prevButton);

            const maxPageButtons = 5;
            const halfRange = Math.floor(maxPageButtons / 2);
            const startPage = Math.max(1, currentPage - halfRange);
            const endPage = Math.min(totalPages, currentPage + halfRange);

            for (let i = startPage; i <= endPage; i++) {
                const pageButton = document.createElement("button");
                pageButton.textContent = i;
                pageButton.classList.add("page-btn");
                if (i === currentPage) pageButton.classList.add("active");
                pageButton.addEventListener("click", () => goToPage(i));
                pagination.appendChild(pageButton);
            }

            const nextButton = document.createElement("button");
            nextButton.textContent = "Next";
            nextButton.disabled = currentPage === totalPages;
            nextButton.classList.add("page-btn");
            nextButton.addEventListener("click", () => goToPage(currentPage + 1));
            pagination.appendChild(nextButton);
        }

        function goToPage(pageNumber) {
            const totalRows = rows.length;
            const totalPages = Math.ceil(totalRows / rowsPerPage);

            currentPage = Math.max(1, Math.min(pageNumber, totalPages));
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;

            rows.forEach((row, index) => {
                row.style.display = index >= start && index < end ? "" : "none";
            });

            initPagination();
        }

        goToPage(1);
    });
});
	</script>

</body>

</html>
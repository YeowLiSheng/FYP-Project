
<?php
session_start();
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
$user_result = mysqli_query($conn, "SELECT * FROM user WHERE user_id ='$user_id'");
$address_result = mysqli_query($conn, "SELECT * FROM user_address WHERE user_id ='$user_id'");

// Check if the query was successful and fetch user data
if ($user_result && mysqli_num_rows($user_result) > 0) {
	$user = mysqli_fetch_assoc($user_result);
} else {
	echo "User not found.";
	exit;
}


$address = null;
if ($address_result && mysqli_num_rows($address_result) > 0) {
    $address = mysqli_fetch_assoc($address_result);
} else {

    $address = null;
}

// Retrieve unique products with total quantity and price in the cart for the logged-in user
$cart_query = "
    SELECT 
        COALESCE(p.product_id, pp.promotion_id) AS item_id,
        COALESCE(p.product_name, pp.promotion_name) AS item_name,
        COALESCE(p.product_price, pp.promotion_price) AS item_price,
        pv.variant_id,
        pv.color, 
        pv.size, 
        pv.Quick_View1 AS product_image,
        SUM(sc.qty) AS total_qty, 
        (COALESCE(p.product_price, pp.promotion_price) * SUM(sc.qty)) AS item_total_price
    FROM 
        shopping_cart AS sc
    JOIN 
        product_variant AS pv ON sc.variant_id = pv.variant_id
    LEFT JOIN 
        product AS p ON pv.product_id = p.product_id
    LEFT JOIN 
        promotion_product AS pp ON pv.promotion_id = pp.promotion_id
    WHERE 
        sc.user_id = '$user_id'
    GROUP BY 
        pv.variant_id
";


$cart_result = mysqli_query($conn, $cart_query);
$cart_result_order = mysqli_query($conn, $cart_query);


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

if (mysqli_num_rows($cart_result) === 0) {

    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "
	<script>
	    document.addEventListener('DOMContentLoaded', function() {

        Swal.fire({
            icon: 'error',
            title: 'Your Shopping Cart is Empty',
            text: 'Please add a product first.',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'product.php';
        });
		 });
    </script>";
    exit;
}



if ($cart_result && mysqli_num_rows($cart_result) > 0) {



	// Retrieve discount amount for the user from shopping_cart table
	$discount_query = "SELECT discount_amount FROM shopping_cart WHERE user_id = '$user_id' LIMIT 1";
	$discount_result = mysqli_query($conn, $discount_query);

	if ($discount_result && mysqli_num_rows($discount_result) > 0) {
		$discount_row = mysqli_fetch_assoc($discount_result);
		$discount_amount = $discount_row['discount_amount'];
	} else {
		$discount_amount = 0; // Default to 0 if no discount is found
	}
} else {
	echo "<p>Your cart is empty.</p>";
}
$paymentSuccess = false; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {

	
    $cardHolderName = isset($_POST['cardHolderName']) ? $_POST['cardHolderName'] : '';
    $cardNum = isset($_POST['cardNum']) ? $_POST['cardNum'] : '';
    $expiryDate = isset($_POST['expiry-date']) ? $_POST['expiry-date'] : '';
    $cvv = isset($_POST['cvv']) ? $_POST['cvv'] : '';
    $errorMessages = [];

	if (!empty($cardHolderName) && !empty($cardNum) && !empty($expiryDate) && !empty($cvv)) {

        // Validate card details
        $query = "SELECT * FROM bank_card WHERE card_holder_name = ? AND card_number = ? AND valid_thru = ? AND cvv = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $cardHolderName, $cardNum, $expiryDate, $cvv);
        $stmt->execute();
        $result = $stmt->get_result();

		if ($result->num_rows > 0) {
            $paymentSuccess = true;
            
            // Check stock for each product in the cart
            $cart_result = mysqli_query($conn, $cart_query);
            while ($row = mysqli_fetch_assoc($cart_result)) {
                $variant_id = $row['variant_id'];
                $product_name = $row['item_name'];
                $total_qty = $row['total_qty'];

                // Get current stock
                $stock_query = "SELECT stock FROM product_variant WHERE variant_id = ?";
                $stock_stmt = $conn->prepare($stock_query);
                $stock_stmt->bind_param("i", $variant_id);
                $stock_stmt->execute();
                $stock_result = $stock_stmt->get_result();

                if ($stock_row = $stock_result->fetch_assoc()) {
                    $current_stock = $stock_row['stock'];

                    if ($current_stock <= 0) {
                        $errorMessages[] = "$product_name is out of stock. Please select again product in your shopping cart.";
                        // Remove out-of-stock product from cart
                        $delete_query = "DELETE FROM shopping_cart WHERE variant_id = ? AND user_id = ?";
                        $delete_stmt = $conn->prepare($delete_query);
                        $delete_stmt->bind_param("ii", $variant_id, $user_id);
                        $delete_stmt->execute();
                        $delete_stmt->close();
                    } elseif ($total_qty > $current_stock) {
                        $errorMessages[] = "$product_name only has $current_stock items left, cannot fulfill requested quantity of $total_qty. Please select again product in your shopping cart.";
                        // Adjust the quantity in the cart to match available stock
                        $update_query = "UPDATE shopping_cart SET qty = ? WHERE variant_id = ? AND user_id = ?";
                        $update_stmt = $conn->prepare($update_query);
                        $update_stmt->bind_param("iii", $current_stock, $variant_id, $user_id);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }
                }
            }

            // If there are error messages, display them and prevent payment processing
            if (!empty($errorMessages)) 
			{
				// Combine all error messages into a single string
				$allMessages = implode('<br>', $errorMessages); // Use <br> to separate messages into multiple lines
			
				echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>"; // Include SweetAlert2 library
				echo "<script>

					document.addEventListener('DOMContentLoaded', function() {
					document.body.innerHTML = '';
            		document.body.style.backgroundColor = 'white';
						Swal.fire({
							icon: 'error',
							title: 'Stock Issues',
							html: '$allMessages', // Use HTML to display multiple lines of messages
							confirmButtonText: 'OK'
						}).then(() => {
							window.location.href = 'dashboard.php'; // Redirect to dashboard.php after confirmation
						});
					});
				</script>";
				$paymentSuccess = false; // Prevent further processing due to stock issues
			}
        } else {
			echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>"; // Include SweetAlert2 library
			echo "<script>
				document.addEventListener('DOMContentLoaded', function() {
					Swal.fire({
						icon: 'error',
						title: 'Invalid Card Details',
						text: 'Please check your card information and try again.',
						confirmButtonText: 'OK'
					});
				});
			</script>";
		}

        $stmt->close();
	}



}



?>

<?php if ($paymentSuccess): 
	
	if ($paymentSuccess) {
		foreach ($cart_result as $item) {
			$variant_id = $item['variant_id'];
			$total_qty = $item['total_qty']; 
	

			$update_stock_query = "UPDATE product_variant SET stock = stock - ? WHERE variant_id = ?";
			$update_stock_stmt = $conn->prepare($update_stock_query);
			$update_stock_stmt->bind_param("ii", $total_qty, $variant_id);
	
			if (!$update_stock_stmt->execute()) {
				die("Error updating stock for variant_id $variant_id: " . $update_stock_stmt->error);
			}
		}
	
	
	}?>
	<script>
	window.onload = function() {
	confirmPayment();
	}
	</script>
	<?php endif; ?>			


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
	<link rel="stylesheet" type="text/css" href="css/main.css">
	<!--===============================================================================================-->





</head>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');

/* Reset styles */
.checkout-reset * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

/* Root container for centering the layout */
.checkout-root {
    width: 100%;
    display: flex;
    justify-content: center;
    background: #f5f5f5; /* Light background for the entire page */
    padding: 20px 0; /* Space around the main content */
}

/* Main content container, aligning with the header width */
.checkout-container {
    width: 100%;
    max-width: 1200px; /* Adjust to match header width */
    background: #fff; /* White background for content */
    border-radius: 8px; /* Soft rounding for a cleaner look */
    margin-left: 135px;
    padding: 40px; /* Internal padding for comfortable spacing */
}

/* Layout for the form rows and columns */
.checkout-row {
    display: flex;
    flex-wrap: wrap; /* Allows responsiveness on smaller screens */
    gap: 20px;
    align-items:  flex-start;
}

.checkout-column {
    flex: 1 1 30%;
    min-width: 280px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start; 
    
}


/* Section titles */
.checkout-title {
    font-size: 20px;
    color: #333;
    text-transform: uppercase;
    margin-bottom: 20px;
    font-weight: 600;
}

/* Input boxes styling */
.checkout-input-box {
    margin: 15px 0;
    position: relative;

}

.checkout-input-box span {
    display: block;
    margin-bottom: 8px;
    color: #555;
    font-weight: 500;
}

.checkout-input-box input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
    transition: border-color 0.3s;
}

.checkout-input-box input:focus {
    border-color: #8175d3; /* Highlighted border on focus */
    outline: none;
}

/* Style the autofill checkbox */
.autofill-checkbox {
    display: flex;
    align-items: center;
    gap: 10px; /* Space between checkbox and label */
    margin-top: 15px;
    margin-left: 5px; /* Align with State field */
}

.autofill-checkbox input[type="checkbox"] {
    transform: scale(1.2); /* Slightly enlarge checkbox */
}

.autofill-checkbox label {
    font-size: 15px;
    color: #555;
    font-weight: 500;
    cursor: pointer;
}




/* Order summary and item styling */
.checkout-order-summary {
    font-size: 16px;
    color: #333;
    margin-left: 20px;
}

.checkout-order-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 15px;
}

.checkout-order-item img {
    width: 100px;
    height: 100px;
    border-radius: 5px;
    border: 1px solid #ccc;
    object-fit: cover;
}

/* Total summary section */
.checkout-order-totals {
    border-top: 1px solid #ccc;
    padding-top: 10px;
    margin-top: 20px;
}

.checkout-order-totals p {
    display: flex;
    justify-content: space-between;
    margin-top: 8px;
}

.checkout-order-totals .checkout-total {
    font-weight: bold;
    color: #333;
}


/* Styling for accepted card images */
.checkout-input-box img {
    width: 100px;
    height: auto;
    margin-top: 5px;
    filter: drop-shadow(0 0 1px #000);
}

.checkout-btn, .ok-btn {
    padding: 10px 20px;
    font-size: 16px;
    color: #fff;
    background-color: #4CAF50;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.checkout-btn:hover, .ok-btn:hover {
    background-color: #45a049;
}

/* Overlay background covering the entire screen */
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s, visibility 0.3s;
}

/* Popup content */
.popup {
    text-align: center;
    background-color: #fff;
    padding: 30px;
    border-radius: 10px;
    max-width: 400px;
    width: 90%;
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
}

/* Loading spinner */
.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #4CAF50;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 0.8s linear infinite;
    margin: 20px auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}


/* Responsive design for smaller screens */
@media (max-width: 768px) {
    .checkout-row {
        flex-direction: column;
    }
}
    
/* Payment button styling */
.checkout-btn {
    width: 100%;
    padding: 10px;
    background: #8175d3;
    border: none;
    border-radius: 6px;
    font-size: 17px;
    color: #fff;
    cursor: pointer;
    text-align: center;
    transition: background 0.3s;
    margin-top: 62px;
}

.checkout-btn:hover {
    background: #6a5acd;
}

    .checkout-input-box select {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 15px;
        font-family: 'Poppins', sans-serif;
        color: #555;
        background-color: #fff;
        transition: border-color 0.3s, box-shadow 0.3s;
        appearance: none; /* Hides default arrow for consistent styling */
        background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" width="10" height="5" viewBox="0 0 10 5"><path fill="%23555" d="M0 0l5 5 5-5z"/></svg>');
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 12px;
    }

    .checkout-input-box select:focus,
    .checkout-input-box select:hover {
        border-color: #8175d3;
        box-shadow: 0 0 5px rgba(129, 117, 211, 0.5);
        outline: none;
    }

    .checkout-input-box select option:disabled {
        color: #aaa;
    }

    /* Add scrolling and set max visible items */
    .checkout-input-box select {
        overflow-y: auto; /* Enable vertical scrolling */
        max-height: 150px; /* Limit height to show 3 items */
    }

.checkout-flex .checkout-input-box:nth-child(1) {
    flex: 2;
}

.checkout-flex .checkout-input-box:nth-child(2) {
    flex: 1;
}

.checkout-flex {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px; 
}

.checkout-input-box {
    flex: 1;
    display: flex;
    flex-direction: column;
}

/* Pagination container positioned at the bottom-right */
.pagination-controls {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 5px;
    margin-top: 10px;
    margin-right: 10px;
    position: relative;
    bottom: 0;
    right: 0;
}

/* Smaller pagination buttons */
.pagination-button {
    padding: 4px 8px;
    font-size: 12px;
    color: #333;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 3px;
    cursor: pointer;
    transition: background-color 0.3s, color 0.3s;
}

.pagination-button:hover {
    background-color: #f0f0f0;
}

.pagination-button.active {
    background-color: #6a5acd;
    color: #fff;
    border-color: #6a5acd;
    font-weight: bold;
}

.pagination-button:disabled {
    color: #aaa;
    cursor: not-allowed;
    background-color: #f9f9f9;
}



    </style>

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
                // Initialize grand total
                $grand_total = 0;

                // Check if there are items in the cart
                if (mysqli_num_rows($cart_result) > 0) {
                    while ($row = mysqli_fetch_assoc($cart_result)) {
                        $item_name = htmlspecialchars($row['item_name']);
                        $item_price = number_format($row['item_price'], 2);
                        $product_image = htmlspecialchars($row['product_image']);
                        $color = htmlspecialchars($row['color']);
                        $total_qty = $row['total_qty'];
                        $item_total_price = number_format($row['item_total_price'], 2);

                        // Accumulate the grand total
                        $grand_total += $row['item_total_price'];

                        // Render each cart item
                        echo '
                        <li class="header-cart-item flex-w flex-t m-b-12">
                            <div class="header-cart-item-img">
                                <img src="images/' . $product_image . '" alt="IMG">
                            </div>
                            <div class="header-cart-item-txt p-t-8">
                                <a href="#" class="header-cart-item-name m-b-18 hov-cl1 trans-04">
                                    ' . $item_name . '
                                </a>
                                <span class="header-cart-item-info">
                                    ' . $total_qty . ' x $' . $item_price . '
                                </span>
                                <span class="header-cart-item-color">Color: ' . $color . '</span>
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
                    Total: $<span id="cart-total"><?php echo number_format($grand_total, 2); ?></span>
                </div>

                <div class="header-cart-buttons flex-w w-full">
                    <a href="shoping-cart.php"
                        class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-r-8 m-b-10">
                        View Cart
                    </a>

                </div>
            </div>
        </div>
    </div>
</div>


					
	<body class="checkout-root checkout-reset">

		<div class="checkout-container">
			<form action="checkout.php" method="post" onsubmit="return validateForm()">

				<div class="checkout-row">
					<!-- Billing Address Section -->
					<div class="checkout-column">
						<h3 class="checkout-title">Delivery Address</h3>

						<div class="checkout-input-box">
							<span class="required">Full Name :</span>
							<input type="text" value="<?php echo htmlspecialchars($user['user_name']); ?>"  readonly>

						</div>
						<div class="checkout-input-box">
							<span class="required">Email :</span>
							<input type="email" value="<?php echo htmlspecialchars($user['user_email']); ?>" readonly>
						</div>
						<div class="checkout-input-box">
							<span class="required">Address :</span>
							<input type="text" name="address" id="address" value="" required>
						</div>
						<div class="checkout-input-box">
							<span class="required">City :</span>
							<input type="text" name="city" id="city" value="" required>
						</div>
						<div class="checkout-flex">
							<div class="checkout-input-box">
							<span class="required">State :</span>
							<select name="state" id="state" required>
    							<option value="" disabled selected>Select a state</option>
    							<option value="Johor">Johor</option>
								<option value="Kelantan">Kelantan</option>
								<option value="Kedah">Kedah</option>
								<option value="Malacca">Malacca</option>
            					<option value="Negeri Sembilan" >Negeri Sembilan</option>
            					<option value="Pahang" >Pahang</option>
            					<option value="Penang" >Penang</option>
            					<option value="Perak" >Perak</option>
            					<option value="Perlis" >Perlis</option>
            					<option value="Selangor">Selangor</option>
            					<option value="Terengganu" >Terengganu</option>
            					<option value="Kuala Lumpur" >Kuala Lumpur</option>
            					<option value="Labuan" >Labuan</option>
            					<option value="Putrajaya" >Putrajaya</option>
            					<option value="Sabah" >Sabah</option>
            					<option value="Sarawak" >Sarawak</option>
							</select>
							</div>
							<div class="checkout-input-box">
    						<span class="required">Postcode :</span>
    							<input type="text" name="postcode" id="postcode" placeholder="12345" minlength="5" maxlength="5" 
        						pattern="\d{5}" title="Please enter exactly 5 digits number" autocomplete="off" required>
							</div>
						</div>

						<?php if (!empty($address)): ?>
<div class="autofill-checkbox">
    <input type="checkbox" id="autofill-checkbox" name="autofill-checkbox" onclick="toggleAutofill()">
    <label for="autofill-checkbox">Use saved address information</label>
</div>
<?php endif; ?>
					</div>

					<!-- Payment Section -->
					<div class="checkout-column">
						<h3 class="checkout-title">Payment</h3>
						<div class="checkout-input-box">
							<span>Cards Accepted :</span>
							<img src="images/payment card.png" alt="Cards Accepted">
						</div>
						<div class="checkout-input-box">
							<span>Card Holder Name :</span>
							<input type="text" name="cardHolderName" placeholder="Cheong Wei Kit" autocomplete="off"
								required>
						</div>
						<div class="checkout-input-box">
							<span> Card Number :</span>
							<input type="text" name="cardNum" placeholder="1111 2222 3333 4444" minlength="16"
								maxlength="19" pattern="\d{4}\s\d{4}\s\d{4}\s\d{4}"
								title="Please enter exactly 16 digits" autocomplete="off" required
								oninput="formatCardNumber(this)">
						</div>
						<div class="checkout-input-box">
							<span>Message for Seller :</span>
							<input type="text" name="user_message" placeholder="leave a message (optional)">
						</div>

						<div class="checkout-flex">
							<div class="checkout-input-box">
								<span>Valid Thru (MM/YY) :</span>
								<input type="text" name="expiry-date" id="expiry-date" placeholder="MM/YY" required minlength="5" maxlength="5" pattern="(0[1-9]|1[0-2])\/\d{2}" title="Please enter a valid MM/YY format" autocomplete="off" oninput="formatExpiryDate(this)">
								<small id="expiry-error" style="color: red; display: none;">Please enter a valid,
									non-expired date.</small>
							</div>
							<div class="checkout-input-box">
								<span>CVV :</span>
								<input type="number" name="cvv" id="cvv" placeholder="123" maxlength="3"
									oninput="validateCVV()" required>
								<small id="cvv-error" style="color: red; display: none;">Please enter a 3-digit CVV
									code.</small>

							</div>
						</div>
					</div>

					<!-- Order Summary Section -->
					<div class="checkout-column checkout-order-summary">
						<h3 class="checkout-title">Your Order</h3>
						<!-- Product List -->
						<?php

						$grand_total = 0;
							
						while ($row = mysqli_fetch_assoc($cart_result_order)):
							$item_name = $row['item_name']; 
							$item_price = $row['item_price'];
							$product_image = $row['product_image']; 
							$color = $row['color']; 
							$total_qty = $row['total_qty']; 
							$item_total_price = $row['item_total_price']; 

							// Accumulate the grand total
							$grand_total += $item_total_price;

							?>
							<div class="checkout-order-item">
								<img src="images/<?php echo htmlspecialchars($product_image); ?>"
									alt="<?php echo htmlspecialchars($item_name); ?>">
								<div>
								<p><?php echo htmlspecialchars($item_name); ?> (<?php echo htmlspecialchars($color); ?>)</p>
									<span>Price: $<?php echo number_format($item_price, 2); ?></span><br>
									<span>Quantity: <?php echo $total_qty; ?></span><br>
									<span>Subtotal: $<?php echo number_format($item_total_price, 2); ?></span>
								</div>
							</div>
						<?php endwhile; ?>

						<!-- Order Totals -->
						<div class="checkout-order-totals">
							<?php
							// Assuming $discount is calculated elsewhere or based on some logic
							$total_payment = $grand_total - $discount_amount ;
							?>
							<p>Grand total: <span>$<?php echo number_format($grand_total, 2); ?></span></p>
							<p>Discount: <span>-$<?php echo number_format($discount_amount, 2); ?></span></p>
							<p class="checkout-total">Total Payment:
								<span>$<?php echo number_format($total_payment, 2); ?></span>
							</p>
						</div>
							

						<!-- Confirm Payment Button -->
						
						<button type="submit" class="checkout-btn">Confirm Payment</button>

									
						
						
					</div>
				</div>
			</form>
		</div>

	</body>
	<?php
if ($paymentSuccess) {

    $grand_total = 0;
    foreach ($cart_result as $item) 
	{
        $grand_total += $item['item_total_price']; 
    }

    
    $final_amount = $grand_total - $discount_amount; 


    if ($grand_total <= 0 || $final_amount <= 0) {
        die("Error: Invalid grand total or final amount!");
    }



// Check if the autofill checkbox is selected
$use_autofill = isset($_POST['autofill-checkbox']) && $_POST['autofill-checkbox'] === 'on';

if ($use_autofill && $address) {
    // Use saved address
    $shipping_address = ($address['address'] ?? '') . ', ' . ($address['postcode'] ?? '') . ', ' . ($address['city'] ?? '') . ', ' . ($address['state'] ?? '');
} else {
    // Use user input
    $shipping_address = ($_POST['address'] ?? '') . ', ' . ($_POST['postcode'] ?? '') . ', ' . ($_POST['city'] ?? '') . ', ' . ($_POST['state'] ?? '');
}

    $user_message = isset($_POST['user_message']) ? $_POST['user_message'] : ''; 

    
    $order_query = "INSERT INTO orders (user_id, order_date, Grand_total, discount_amount, final_amount, shipping_address, user_message) VALUES (?, NOW(), ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("idddss", $user_id, $grand_total, $discount_amount, $final_amount, $shipping_address, $user_message);
    if (!$stmt->execute()) {
        die("Error inserting into orders table: " . $stmt->error);
    }

    $order_id = $stmt->insert_id;

    foreach ($cart_result as $item) {
        $variant_id = $item['variant_id'];
        $quantity = $item['total_qty'];
        $unit_price = $item['item_price'];
        $total_price = $item['item_total_price']; 

        $detail_query = "INSERT INTO order_details (order_id, variant_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)";
        $detail_stmt = $conn->prepare($detail_query);
        $detail_stmt->bind_param("iiidd", $order_id, $variant_id, $quantity, $unit_price, $total_price);
        if (!$detail_stmt->execute()) {
            die("Error inserting into order_details table: " . $detail_stmt->error);
        }
    }

    $clear_cart_query = "DELETE FROM shopping_cart WHERE user_id = ?";
    $clear_cart_stmt = $conn->prepare($clear_cart_query);
    $clear_cart_stmt->bind_param("i", $user_id);
    if (!$clear_cart_stmt->execute()) {
        die("Error clearing shopping cart: " . $clear_cart_stmt->error);
    }

    $payment_query = "INSERT INTO payment (user_id, order_id, payment_amount, payment_status) VALUES (?, ?, ?, ?)";
    $payment_status = 'Completed'; 
    $payment_stmt = $conn->prepare($payment_query);
    $payment_stmt->bind_param("iids", $user_id, $order_id, $final_amount, $payment_status);
    if (!$payment_stmt->execute()) {
        die("Error inserting into payment table: " . $payment_stmt->error);
    }

    echo "Order placed successfully!";
}
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

function toggleAutofill() { 
    const autofillCheckbox = document.getElementById('autofill-checkbox');
    const address = document.getElementById('address');
    const city = document.getElementById('city');
    const state = document.getElementById('state');
    const postcode = document.getElementById('postcode');

    if (autofillCheckbox.checked) {
        // Autofill fields with saved address data
        address.value = "<?php echo htmlspecialchars($address['address'] ?? ''); ?>";
        city.value = "<?php echo htmlspecialchars($address['city'] ?? ''); ?>";
        postcode.value = "<?php echo htmlspecialchars($address['postcode'] ?? ''); ?>";

        // Set the correct state in the dropdown
        const savedState = "<?php echo htmlspecialchars($address['state'] ?? ''); ?>";
        if (savedState) {
            state.value = savedState;
        }

        // Disable fields to prevent modification
        address.disabled = true;
        city.disabled = true;
        state.disabled = true;
        postcode.disabled = true;
    } else {
        // Enable fields for manual input
        address.disabled = false;
        city.disabled = false;
        state.disabled = false;
        postcode.disabled = false;

        // Clear input fields
        address.value = "";
        city.value = "";
        state.value = "";
        postcode.value = "";
    }
}




		function formatExpiryDate(input) {
    let value = input.value.replace(/\D/g, ""); 


    if (value.length > 2) {
        value = value.slice(0, 2) + '/' + value.slice(2, 4);
    }


    input.value = value.slice(0, 5);
}

document.getElementById('expiry-date').addEventListener('input', function () {
    const input = this.value;
    const error = document.getElementById('expiry-error');


    const datePattern = /^(0[1-9]|1[0-2])\/\d{2}$/;
    if (!datePattern.test(input)) {
        error.style.display = 'none';
        return;
    }

    
    const [month, year] = input.split('/').map(Number);
    const currentYear = new Date().getFullYear() % 100; 
    const currentMonth = new Date().getMonth() + 1; 

   
    if (year > currentYear || (year === currentYear && month >= currentMonth)) {
        error.style.display = 'none'; 
    } else {
        error.style.display = 'block'; 
    }
});

		function formatCardNumber(input) {
			// Remove all spaces and get only digits
			let cardNum = input.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');

			// Split into groups of 4 digits
			let formattedCardNum = cardNum.match(/.{1,4}/g);

			// Join groups with a space
			if (formattedCardNum) {
				input.value = formattedCardNum.join(' ');
			}
		}

		document.getElementById("expiry-date").addEventListener("input", function (e) {
			const input = e.target;
			let value = input.value.replace(/\D/g, ""); // Remove non-digit characters

			// Insert '/' after the month if exactly two digits are entered
			if (value.length > 2) {
				value = value.slice(0, 2) + '/' + value.slice(2, 4);
			}

			// Limit to 5 characters (MM/YY)
			if (value.length > 5) {
				value = value.slice(0, 5);
			}

			input.value = value;
		});
		function validateCVV() {
			const cvvInput = document.getElementById("cvv");
			const cvvError = document.getElementById("cvv-error");


			if (cvvInput.value.length > 3) {
				cvvInput.value = cvvInput.value.slice(0, 3);
			}


			if (cvvInput.value.length < 3) {
				cvvInput.setCustomValidity("Please enter a 3-digit CVV code.");
				cvvError.style.display = "inline";
			} else {
				cvvInput.setCustomValidity("");
				cvvError.style.display = "none";
			}
		}

		function validateForm() {
			const fullName = document.querySelector('input[name="cardHolderName"]');
			const cardNum = document.querySelector('input[name="cardNum"]');
			const expiryDate = document.getElementById('expiry-date');
			const cvv = document.getElementById('cvv');
			const address = document.getElementById('address');
			const city = document.getElementById('city');
			const state = document.getElementById('state');
			const postcode = document.getElementById('postcode');

			if (
				!fullName.value.trim() ||
				!cardNum.value.trim() ||
				!expiryDate.value.trim() ||
				!cvv.value.trim() ||
				!address.value.trim() ||
				!city.value.trim() ||
				!state.value.trim() ||
				!postcode.value.trim()
			) {
				alert('Please fill in all required fields.');
				return false;
			}

			const cardNumberPattern = /^\d{4}\s\d{4}\s\d{4}\s\d{4}$/;
			if (!cardNumberPattern.test(cardNum.value)) {
				alert('Please enter a valid 16-digit card number (format: 1111 2222 3333 4444).');
				return false;
			}

			if (cvv.value.length !== 3) {
				alert('Please enter a 3-digit CVV code.');
				return false;
			}

			const datePattern = /^(0[1-9]|1[0-2])\/\d{2}$/;
			if (!datePattern.test(expiryDate.value)) {
				Swal.fire({
  icon: 'error',
  title: 'Invalid Expiration Date',
  text: 'Please enter a valid expiration date (format: MM/YY).',
});        return false;
    } else {
        const [month, year] = expiryDate.value.split('/').map(Number);
        const currentYear = new Date().getFullYear() % 100;
        const currentMonth = new Date().getMonth() + 1;
        if (year < currentYear || (year === currentYear && month < currentMonth)) {
            Swal.fire({
  icon: 'error',
  title: 'Invalid Expiration Date',
  text: 'Please enter a valid expiration date (format: MM/YY).',
});
            return false;
        }
    }
	address.disabled = false;
    city.disabled = false;
    state.disabled = false;
    postcode.disabled = false;


    form.submit();
			return true;
		}

	

		
		
		function confirmPayment() {

        document.body.innerHTML = '';
        document.body.style.backgroundColor = 'white';


        Swal.fire({
            title: 'Processing Payment',
            text: 'Please wait...',
            allowOutsideClick: false,
            showConfirmButton: false, 
            didOpen: () => {
                Swal.showLoading(); 
            }
        });

       
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Payment Successful',
                text: 'Your payment was processed successfully.',
                confirmButtonText: 'OK',
                allowOutsideClick: false 
            }).then(() => {
                goToDashboard(); 
            });
        }, 2000);
    }

    function goToDashboard() {
        window.location.href = 'dashboard.php'; 
    }



// JavaScript for Pagination with Shopee-like design
document.addEventListener('DOMContentLoaded', () => {
    const itemsPerPage = 2; // Number of items per page
    const items = document.querySelectorAll('.checkout-order-item');
    const totalPages = Math.ceil(items.length / itemsPerPage);

    let currentPage = 1;

    const renderPage = (page) => {
        items.forEach((item, index) => {
            if (index >= (page - 1) * itemsPerPage && index < page * itemsPerPage) {
                item.style.display = 'flex'; // Show items for the current page
            } else {
                item.style.display = 'none'; // Hide other items
            }
        });

        updatePaginationControls(page);
    };

	const createPaginationControls = () => {
    const paginationContainer = document.createElement('div');
    paginationContainer.classList.add('pagination-controls');

    const orderSummary = document.querySelector('.checkout-order-summary');
    const orderTotals = document.querySelector('.checkout-order-totals');
    orderSummary.insertBefore(paginationContainer, orderTotals); // Ensure it's before Order Totals
};

    const renderPaginationControls = (container) => {
        container.innerHTML = ''; // Clear existing controls

        // Previous button
        const prevButton = document.createElement('button');
        prevButton.textContent = 'Previous';
        prevButton.classList.add('pagination-button');
        prevButton.disabled = currentPage === 1;
        prevButton.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderPage(currentPage);
            }
        });
        container.appendChild(prevButton);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const pageButton = document.createElement('button');
            pageButton.textContent = i;
            pageButton.classList.add('pagination-button');
            if (i === currentPage) {
                pageButton.classList.add('active');
            }
            pageButton.addEventListener('click', () => {
                currentPage = i;
                renderPage(currentPage);
            });
            container.appendChild(pageButton);
        }

        // Next button
        const nextButton = document.createElement('button');
        nextButton.textContent = 'Next';
        nextButton.classList.add('pagination-button');
        nextButton.disabled = currentPage === totalPages;
        nextButton.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderPage(currentPage);
            }
        });
        container.appendChild(nextButton);
    };

    const updatePaginationControls = (page) => {
        const paginationContainer = document.querySelector('.pagination-controls');
        renderPaginationControls(paginationContainer);
    };

    if (items.length > itemsPerPage) {
        createPaginationControls();
        renderPage(currentPage);
    }
});
	</script>
</body>

</html>
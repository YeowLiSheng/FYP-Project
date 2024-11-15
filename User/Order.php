<?php 
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch current user details (replace with your session logic)
$current_user_id = 36; // Example user ID, replace with actual session user ID
$current_user_query = $conn->query("SELECT user_name, user_image FROM user WHERE user_id = $current_user_id");
$current_user = $current_user_query->fetch_assoc();

// Fetch orders with all products for each order
function fetchOrdersWithProducts($conn, $status = null) {
    $sql = "
        SELECT o.order_id, o.order_date, o.final_amount, o.order_status, 
               GROUP_CONCAT(p.product_name SEPARATOR ', ') AS products, 
               MIN(p.product_image) AS product_image
        FROM orders o
        JOIN order_details od ON o.order_id = od.order_id
        JOIN product p ON od.product_id = p.product_id";
        
    // Add a condition to filter by status if provided
    if ($status) {
        $sql .= " WHERE o.order_status = '$status'";
    }

    $sql .= " GROUP BY o.order_id 
              ORDER BY o.order_date DESC";
              
    return $conn->query($sql);
}

// Fetch orders for each tab
$all_orders = fetchOrdersWithProducts($conn);
$processing_orders = fetchOrdersWithProducts($conn, 'Processing');
$shipping_orders = fetchOrdersWithProducts($conn, 'Shipping');
$completed_orders = fetchOrdersWithProducts($conn, 'Complete');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Orders</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    /* General layout styling */
    body {
        font-family: Arial, sans-serif;
        display: flex;
    }
    .sidebar {
        width: 250px;
        background-color: #333;
        color: #fff;
        padding: 20px;
        height: 100vh;
        position: fixed;
        display: flex;
        flex-direction: column;
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
    }
    .sidebar ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }
    .sidebar ul li {
        padding: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
    }
    .sidebar ul li i {
        margin-right: 10px;
    }
    .sidebar ul li:hover {
        background-color: #444;
    }
    .submenu {
        padding-left: 20px;
        margin-top: 10px;
        display: none;
        flex-direction: column;
    }
    .submenu li {
        padding: 8px;
        background-color: #444;
        border-radius: 5px;
        margin-top: 5px;
    }
    .submenu li:hover {
        background-color: #555;
    }
    .content {
        margin-left: 270px;
        padding: 20px;
        flex: 1;
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
        border: 1px solid #e0e0e0;
        padding: 15px;
        margin-bottom: 15px;
        cursor: pointer;
        display: flex;
        align-items: center;
    }
    .order-summary img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        margin-right: 15px;
    }
    .order-summary h3 {
        font-size: 18px;
        font-weight: bold;
        margin: 0;
        display: flex;
        align-items: center;
    }
    .order-summary p {
        margin: 5px 0;
        font-size: 14px;
        display: flex;
        align-items: center;
    }
    .order-summary i {
        margin-right: 8px;
        color: #555;
    }
    .no-orders {
        text-align: center;
        margin-top: 50px;
    }
    /* Adjust the profile items under My Account */
    .sidebar ul li.profile-item {
        padding-left: 30px;
    }
</style>
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
</script>
</head>
<body>
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
            <li class="profile-item"><i class="fa fa-id-card"></i> My Profile</li>
            <li class="profile-item"><i class="fa fa-edit"></i> Edit Profile</li>
            <li class="profile-item"><i class="fa fa-lock"></i> Change Password</li>
            <!-- My Orders -->
            <li><i class="fa fa-box"></i> My Orders</li>
        </ul>
    </div>

    <!-- Content Area -->
    <div class="content">
        <h1>My Orders</h1>
        <!-- Tab Buttons -->
        <div class="tabs">
            <button id="All-tab" onclick="showTab('All')" class="active">All</button>
            <button id="Processing-tab" onclick="showTab('Processing')">Processing</button>
            <button id="Shipping-tab" onclick="showTab('Shipping')">To Ship</button>
            <button id="Complete-tab" onclick="showTab('Complete')">Completed</button>
        </div>

        <!-- Order Containers for Each Status -->
        <?php
        function renderOrders($orders) {
            if ($orders->num_rows > 0) {
                while ($order = $orders->fetch_assoc()) {
                    echo '
                    <div class="order-summary" onclick="window.location.href=\'orderdetails.php?order_id=' . $order['order_id'] . '\'">
                        <img src="images/' . $order['product_image'] . '" alt="Product Image">
                        <div>
                            <h3><i class="fa fa-box"></i> Order #' . $order['order_id'] . '</h3>
                            <p><i class="fa fa-calendar-alt"></i> Date: ' . date("Y-m-d", strtotime($order['order_date'])) . '</p>
                            <p><i class="fa fa-tag"></i> Products: ' . $order['products'] . '</p>
                            <p><i class="fa fa-dollar-sign"></i> Total Price: $' . $order['final_amount'] . '</p>
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
        </div>

        <!-- Processing Orders -->
        <div class="order-container" id="Processing" style="display: none;">
            <?php renderOrders($processing_orders); ?>
        </div>

        <!-- Shipping Orders -->
        <div class="order-container" id="Shipping" style="display: none;">
            <?php renderOrders($shipping_orders); ?>
        </div>

        <!-- Completed Orders -->
        <div class="order-container" id="Complete" style="display: none;">
            <?php renderOrders($completed_orders); ?>
        </div>
    </div>
</body>
</html>



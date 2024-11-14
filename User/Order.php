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

// Fetch orders with all products for each order
function fetchOrdersWithProducts($conn, $status) {
    $sql = "
        SELECT o.order_id, o.order_date, o.final_amount, o.order_status, 
               GROUP_CONCAT(p.product_name SEPARATOR ', ') AS products, 
               MIN(p.product_image) AS product_image
        FROM orders o
        JOIN order_details od ON o.order_id = od.order_id
        JOIN product p ON od.product_id = p.product_id
        WHERE o.order_status = '$status'
        GROUP BY o.order_id 
        ORDER BY o.order_date DESC";
    return $conn->query($sql);
}

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
d<style>
    /* General layout styling */
    body {
        font-family: Arial, sans-serif;
        display: flex;
    }
    .sidebar {
        width: 250px;
        background-color: #f2f2f2;
        padding: 20px;
        height: 100vh;
        position: fixed;
    }
    .sidebar ul {
        list-style-type: none;
        padding: 0;
    }
    .sidebar ul li {
        padding: 10px;
        cursor: pointer;
    }
    .sidebar ul li:hover {
        background-color: #ddd;
    }
    #account-submenu {
        display: none;
        padding-left: 20px;
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

    function toggleSubmenu(id) {
        const submenu = document.getElementById(id);
        submenu.style.display = submenu.style.display === 'none' ? 'block' : 'none';
    }
</script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <ul>
            <li onclick="toggleSubmenu('account-submenu')">My account</li>
            <ul id="account-submenu">
                <li>My profile</li>
                <li>Edit profile</li>
                <li>Change password</li>
            </ul>
            <li>My orders</li>
        </ul>
    </div>

    <!-- Content Area -->
    <div class="content">
        <h1>My Orders</h1>
        <!-- Tab Buttons -->
        <div class="tabs">
            <button id="Processing-tab" onclick="showTab('Processing')" class="active">Processing</button>
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

        <!-- Processing Orders -->
        <div class="order-container" id="Processing" style="display: block;">
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

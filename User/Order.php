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

// Fetch orders based on their statuses
function fetchOrders($conn, $status) {
    $sql = "SELECT * FROM orders WHERE order_status = '$status' ORDER BY order_date DESC";
    return $conn->query($sql);
}

$processing_orders = fetchOrders($conn, 'Processing');
$shipping_orders = fetchOrders($conn, 'Shipping');
$completed_orders = fetchOrders($conn, 'Complete');
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
    .content {
        margin-left: 270px;
        padding: 20px;
        flex: 1;
    }
    /* Styling for order status progress */
    .progress-bar {
        display: flex;
        align-items: center;
        margin-top: 20px;
        font-weight: bold;
    }
    .progress-bar .completed {
        background-color: #4caf50;
    }
    .progress-bar .upcoming {
        background-color: #e0e0e0;
    }
    /* Styling for no order message */
    .no-orders {
        text-align: center;
        margin-top: 50px;
    }
    /* Tab style */
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
        <ul>
            <li>My account
                <ul>
                    <li>My profile</li>
                    <li>My address</li>
                    <li>Change password</li>
                </ul>
            </li>
            <li>My orders</li>
            <li>My voucher</li>
            <li>My reward point</li>
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

        <!-- Processing Orders -->
        <div class="order-container" id="Processing" style="display: block;">
            <?php if ($processing_orders->num_rows > 0) { ?>
                <?php while ($order = $processing_orders->fetch_assoc()) { ?>
                    <div class="order-summary">
                        <h3>Order #<?php echo $order['order_id']; ?></h3>
                        <p>Status: <?php echo $order['order_status']; ?></p>
                        <p>Date: <?php echo $order['order_date']; ?></p>
                        <button onclick="window.location.href='order_details.php?order_id=<?php echo $order['order_id']; ?>'">View Details</button>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="no-orders">
                    <p><i class="fa fa-ice-cream"></i> Nothing to show here.</p>
                    <button onclick="window.location.href='shop.php'">Continue Shopping</button>
                </div>
            <?php } ?>
        </div>

        <!-- Shipping Orders -->
        <div class="order-container" id="Shipping" style="display: none;">
            <?php if ($shipping_orders->num_rows > 0) { ?>
                <?php while ($order = $shipping_orders->fetch_assoc()) { ?>
                    <div class="order-summary">
                        <h3>Order #<?php echo $order['order_id']; ?></h3>
                        <p>Status: <?php echo $order['order_status']; ?></p>
                        <p>Date: <?php echo $order['order_date']; ?></p>
                        <button onclick="window.location.href='order_details.php?order_id=<?php echo $order['order_id']; ?>'">View Details</button>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="no-orders">
                    <p><i class="fa fa-ice-cream"></i> Nothing to show here.</p>
                    <button onclick="window.location.href='shop.php'">Continue Shopping</button>
                </div>
            <?php } ?>
        </div>

        <!-- Completed Orders -->
        <div class="order-container" id="Complete" style="display: none;">
            <?php if ($completed_orders->num_rows > 0) { ?>
                <?php while ($order = $completed_orders->fetch_assoc()) { ?>
                    <div class="order-summary">
                        <h3>Order #<?php echo $order['order_id']; ?></h3>
                        <p>Status: <?php echo $order['order_status']; ?></p>
                        <p>Date: <?php echo $order['order_date']; ?></p>
                        <button onclick="window.location.href='order_details.php?order_id=<?php echo $order['order_id']; ?>'">View Details</button>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="no-orders">
                    <p><i class="fa fa-ice-cream"></i> Nothing to show here.</p>
                    <button onclick="window.location.href='shop.php'">Continue Shopping</button>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php $conn->close(); ?>
</body>
</html>

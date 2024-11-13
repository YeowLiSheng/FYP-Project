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
    $sql = "SELECT o.order_id, o.order_status, o.order_date, oi.product_id, oi.quantity, p.product_name, p.product_price, p.product_image
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN products p ON oi.product_id = p.product_id
            WHERE o.order_status = '$status'
            ORDER BY o.order_date DESC";
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
    .content {
        margin-left: 270px;
        padding: 20px;
        flex: 1;
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
    /* Order summary style */
    .order-summary {
        display: flex;
        border: 1px solid #e0e0e0;
        padding: 15px;
        margin-bottom: 15px;
        align-items: center;
    }
    .order-summary img {
        width: 80px;
        height: 80px;
        margin-right: 15px;
        cursor: pointer;
    }
    .order-details {
        flex: 1;
    }
    .order-details h3 {
        font-size: 16px;
        margin: 0;
    }
    .order-details p {
        margin: 5px 0;
    }
    .order-status {
        font-weight: bold;
        color: #4caf50;
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
                        <img src="<?php echo $order['product_image']; ?>" alt="Product Image" onclick="window.location.href='order_details.php?order_id=<?php echo $order['order_id']; ?>'">
                        <div class="order-details">
                            <h3><?php echo $order['product_name']; ?></h3>
                            <p>Quantity: <?php echo $order['quantity']; ?></p>
                            <p>Status: <span class="order-status"><?php echo $order['order_status']; ?></span></p>
                            <p>Date: <?php echo $order['order_date']; ?></p>
                            <p>Price: RM<?php echo number_format($order['product_price'], 2); ?></p>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="no-orders">
                    <p><i class="fa fa-ice-cream"></i> Nothing to show here.</p>
                    <button onclick="window.location.href='shop.php'">Continue Shopping</button>
                </div>
            <?php } ?>
        </div>

        <!-- Repeat similar sections for Shipping and Completed orders -->
        
    </div>

    <?php $conn->close(); ?>
</body>
</html>

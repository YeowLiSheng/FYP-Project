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

// Fetch orders based on their statuses with first product info
function fetchOrdersWithFirstProduct($conn, $status) {
    $sql = "
        SELECT o.order_id, o.order_date, o.final_amount, o.order_status, 
               p.product_name, p.product_image 
        FROM orders o
        JOIN order_details od ON o.order_id = od.order_id
        JOIN product p ON od.product_id = p.product_id
        WHERE o.order_status = '$status'
        GROUP BY o.order_id 
        ORDER BY o.order_date DESC";
    return $conn->query($sql);
}

$processing_orders = fetchOrdersWithFirstProduct($conn, 'Processing');
$shipping_orders = fetchOrdersWithFirstProduct($conn, 'Shipping');
$completed_orders = fetchOrdersWithFirstProduct($conn, 'Complete');
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
        width: 50px;
        height: 50px;
        object-fit: cover;
        margin-right: 15px;
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
                    <div class="order-summary" onclick="window.location.href='order_details.php?order_id=<?php echo $order['order_id']; ?>'">
                        <img src="images/<?php echo $order['product_image']; ?>" alt="<?php echo $order['product_name']; ?>">
                        <div>
                            <h3>Order #<?php echo $order['order_id']; ?></h3>
                            <p>Product: <?php echo $order['product_name']; ?></p>
                            <p>Total Price: $<?php echo $order['final_amount']; ?></p>
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

        <!-- Shipping Orders -->
        <div class="order-container" id="Shipping" style="display: none;">
            <?php if ($shipping_orders->num_rows > 0) { ?>
                <?php while ($order = $shipping_orders->fetch_assoc()) { ?>
                    <div class="order-summary" onclick="window.location.href='order_details.php?order_id=<?php echo $order['order_id']; ?>'">
                        <img src="images/<?php echo $order['product_image']; ?>" alt="<?php echo $order['product_name']; ?>">
                        <div>
                            <h3>Order #<?php echo $order['order_id']; ?></h3>
                            <p>Product: <?php echo $order['product_name']; ?></p>
                            <p>Total Price: $<?php echo $order['final_amount']; ?></p>
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

        <!-- Completed Orders -->
        <div class="order-container" id="Complete" style="display: none;">
            <?php if ($completed_orders->num_rows > 0) { ?>
                <?php while ($order = $completed_orders->fetch_assoc()) { ?>
                    <div class="order-summary" onclick="window.location.href='order_details.php?order_id=<?php echo $order['order_id']; ?>'">
                        <img src="images/<?php echo $order['product_image']; ?>" alt="<?php echo $order['product_name']; ?>">
                        <div>
                            <h3>Order #<?php echo $order['order_id']; ?></h3>
                            <p>Product: <?php echo $order['product_name']; ?></p>
                            <p>Total Price: $<?php echo $order['final_amount']; ?></p>
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
    </div>

    <?php $conn->close(); ?>
</body>
</html>

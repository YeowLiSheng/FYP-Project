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

// Get order data
$order_sql = "SELECT * FROM orders ORDER BY order_date DESC";
$order_result = $conn->query($order_sql);
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
    .progress-bar div {
        flex: 1;
        height: 4px;
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
</style>
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
        <!-- Progress bar -->
        <div class="progress-bar">
            <div class="completed" style="flex: 2;"></div>
            <div class="upcoming" style="flex: 1;"></div>
            <div class="upcoming" style="flex: 1;"></div>
        </div>
        <div class="progress-labels" style="display: flex; justify-content: space-between; margin-top: 5px;">
            <span>Processing</span>
            <span>To ship</span>
            <span>Completed</span>
        </div>

        <?php
        if ($order_result->num_rows > 0) {
            // Loop through each order
            while ($order = $order_result->fetch_assoc()) {
                $order_id = $order["order_id"];
                $order_status = $order["order_status"];
                echo "<div class='order-summary'>";
                echo "<h3>Order #" . $order["order_id"] . "</h3>";
                echo "<p>Status: $order_status</p>";
                echo "</div>";
            }
        } else {
            echo "<div class='no-orders'>";
            echo "<p><i class='fa fa-ice-cream'></i> Nothing to show here.</p>";
            echo "<button onclick=\"window.location.href='shop.php'\">Continue Shopping</button>";
            echo "</div>";
        }
        $conn->close();
        ?>
    </div>
</body>
</html>

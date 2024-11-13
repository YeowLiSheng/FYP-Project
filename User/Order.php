<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <style>
        /* Basic styling for layout */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            margin: 0;
        }
        .sidebar {
            width: 200px;
            background-color: #f2f2f2;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin-bottom: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .sidebar ul li img {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
        .order-container {
            flex: 1;
            padding: 20px;
        }
        .order-summary {
            border: 1px solid #ddd;
            margin: 15px 0;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            cursor: pointer;
            background-color: #f9f9f9;
        }
        .order-summary img {
            width: 80px;
            height: auto;
            margin-right: 15px;
        }
        .order-info {
            flex: 1;
        }
        .order-total {
            font-size: 18px;
            color: #ff5722;
            font-weight: bold;
        }
        .order-status {
            color: green;
            font-weight: bold;
        }
        .details-container {
            display: none;
            padding: 15px;
            margin-top: 10px;
            border-top: 1px solid #ddd;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table th, .details-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .details-table th {
            background-color: #f2f2f2;
        }
    </style>
    <script>
        function toggleDetails(orderId) {
            const details = document.getElementById('details-' + orderId);
            details.style.display = (details.style.display === 'none' || details.style.display === '') ? 'block' : 'none';
        }
        function toggleSubMenu(id) {
            const submenu = document.getElementById(id);
            submenu.style.display = (submenu.style.display === 'none' || submenu.style.display === '') ? 'block' : 'none';
        }
    </script>
</head>
<body>

<div class="sidebar">
    <h3>Menu</h3>
    <ul>
        <li onclick="toggleSubMenu('account-menu')">
            <img src="path/to/account-icon.png" alt="Account Icon"> My Account
        </li>
        <ul id="account-menu" style="display:none;">
            <li><img src="path/to/profile-icon.png" alt="Profile Icon"> My Profile</li>
            <li><img src="path/to/edit-icon.png" alt="Edit Icon"> Edit Profile</li>
            <li><img src="path/to/password-icon.png" alt="Password Icon"> Change Password</li>
        </ul>
        <li onclick="toggleSubMenu('order-menu')">
            <img src="path/to/order-icon.png" alt="Order Icon"> My Order
        </li>
        <ul id="order-menu" style="display:none;">
            <li><img src="path/to/status-icon.png" alt="Status Icon"> Order Status</li>
            <li><img src="path/to/history-icon.png" alt="History Icon"> Purchase History</li>
        </ul>
    </ul>
</div>

<div class="order-container">
    <h1>Order History</h1>

    <?php
session_start();

// 假设登录时将用户 ID 存储在 $_SESSION 中
if (!isset($_SESSION['user_id'])) {
    // 如果用户未登录，重定向到登录页面
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // 使用当前登录用户的 ID

// 连接数据库
$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 仅查询当前登录用户的订单
$order_sql = "SELECT * FROM orders WHERE user_id = $user_id";
$order_result = $conn->query($order_sql);

if ($order_result->num_rows > 0) {
    while ($order = $order_result->fetch_assoc()) {
        $order_id = $order["order_id"];
        $order_status = ($order["order_status"] == 'Complete') ? 'Completed' : 'Processing';

        // 从 order_details 表中获取产品图片
        $detail_sql = "SELECT product_name, quantity, unit_price, total_price, product_image_path FROM order_details WHERE order_id = $order_id";
        $detail_result = $conn->query($detail_sql);
        $product_image = "";
        if ($detail_result->num_rows > 0) {
            $detail_row = $detail_result->fetch_assoc();
            $product_image = $detail_row['product_image_path'];
        }

        echo "<div class='order-summary' onclick='toggleDetails($order_id)'>";
        echo "<img src='$product_image' alt='Product Image'>"; // 从数据库显示产品图片
        echo "<div class='order-info'>";
        echo "<h3>Order #" . $order["order_id"] . "</h3>";
        echo "<p>Order Date: " . $order["order_date"] . "</p>";
        echo "<p class='order-status'>$order_status</p>";
        echo "<p class='order-total'>RM" . $order["final_amount"] . "</p>";
        echo "</div>";
        echo "</div>";

        echo "<div id='details-$order_id' class='details-container'>";
        if ($detail_result->num_rows > 0) {
            echo "<table class='details-table'>";
            echo "<tr><th>Product Name</th><th>Quantity</th><th>Unit Price</th><th>Total Price</th></tr>";
            do {
                echo "<tr>";
                echo "<td>" . $detail_row["product_name"] . "</td>";
                echo "<td>" . $detail_row["quantity"] . "</td>";
                echo "<td>RM" . $detail_row["unit_price"] . "</td>";
                echo "<td>RM" . $detail_row["total_price"] . "</td>";
                echo "</tr>";
            } while ($detail_row = $detail_result->fetch_assoc());
            echo "</table>";
        }
        echo "</div>";
    }
} else {
    echo "<p>No orders found.</p>";
}

$conn->close();
?>

</div>

</body>
</html>

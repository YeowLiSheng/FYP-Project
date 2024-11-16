<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

// 连接数据库
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 检查是否登录
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
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

// 获取订单 ID
if (!isset($_GET['order_id'])) {
    echo "Invalid order ID.";
    exit;
}



$order_id = intval($_GET['order_id']);

// 使用预处理语句获取订单信息
$order_stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.Grand_total, o.discount_amount, o.delivery_charge,
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
    SELECT od.product_id, od.product_name, od.quantity, od.unit_price, od.total_price, p.product_image
    FROM order_details od
    JOIN product p ON od.product_id = p.product_id
    WHERE od.order_id = ?
");
$details_stmt->bind_param("i", $order_id);
$details_stmt->execute();
$details_result = $details_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Details</title>
<!-- 引入 Font Awesome 图标库 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    /* 全局样式 */
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f4f9;
        color: #333;
        margin: 0;
        display: flex;
    }
    .container {
        display: flex;
        flex-direction: row;
        width: 100%;
    }

    /* Sidebar 样式 */
    .sidebar {
        width: 250px;
        padding: 20px;
        height: 100vh;
        background-color: #fff;
        border-right: 1px solid #e0e0e0;
        overflow-y: auto;
        flex-shrink: 0;
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

    /* Order Details 样式 */
    .order-details-container {
        flex-grow: 1;
        padding: 20px;
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
    }
    .back-button, .print-button {
        display: inline-block;
        padding: 10px 25px;
        color: #fff;
        text-decoration: none;
        border-radius: 8px;
        margin-top: 20px;
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
</style>
</head>
<body>
<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- User Info -->
        <div class="user-info">
            <img src="<?= $current_user['user_image'] ?>" alt="User Image">
            <h3><?= $current_user['user_name'] ?></h3>
        </div>
        <ul>
            <li><i class="fa fa-user"></i> My Account</li>
            <li class="profile-item"><i class="fa fa-id-card"></i> My Profile</li>
            <li class="profile-item"><i class="fa fa-edit"></i> Edit Profile</li>
            <li class="profile-item"><i class="fa fa-lock"></i> Change Password</li>
            <li><i class="fa fa-box"></i> My Orders</li>
        </ul>
    </div>

    <!-- Order Details -->
    <div class="order-details-container">
        <!-- Order Summary -->
        <div class="card">
            <h2><span class="icon">📋</span>Order Summary</h2>
            <div class="summary-item"><strong>User:</strong> <span><?= $order['user_name'] ?></span></div>
            <div class="summary-item"><strong>Order Date:</strong> <span><?= date("Y-m-d H:i:s", strtotime($order['order_date'])) ?></span></div>
            <div class="summary-item"><strong>Status:</strong> <span><?= $order['order_status'] ?></span></div>
            <div class="summary-item"><strong>Shipping Address:</strong> <span><?= $order['shipping_address'] ?></span></div>
        </div>

        <!-- Product Details -->
        <div class="card">
            <h2><span class="icon">🛒</span>Product Details</h2>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($detail = $details_result->fetch_assoc()) { ?>
                    <tr>
                        <td><img src="images/<?= $detail['product_image'] ?>" class="product-image"></td>
                        <td><?= $detail['product_name'] ?></td>
                        <td><?= $detail['quantity'] ?></td>
                        <td>RM <?= number_format($detail['unit_price'], 2) ?></td>
                        <td>RM <?= number_format($detail['total_price'], 2) ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Pricing Details -->
        <div class="card">
            <h2><span class="icon">💰</span>Pricing Details</h2>
            <div class="pricing-item"><span>Grand Total:</span><span>RM <?= number_format($order['Grand_total'], 2) ?></span></div>
        </div>

        <!-- 操作按钮 -->
        <a href="myaccount.php" class="back-button">Back</a>
        <a href="receipt.php?order_id=<?= $order['order_id'] ?>" class="print-button">Print</a>
    </div>
</div>
</body>
</html>

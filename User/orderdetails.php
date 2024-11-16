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
<link rel="stylesheet" href="styles.css">
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5;
        color: #333;
        margin: 0;
        padding: 20px;
    }
    .order-details-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .card {
        background-color: #fff;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .card h2 {
        font-size: 1.5em;
        margin-bottom: 10px;
    }
    .summary-item {
        display: flex;
        justify-content: space-between;
        margin: 5px 0;
    }
    .product-table {
        width: 100%;
        border-collapse: collapse;
    }
    .product-table th, .product-table td {
        padding: 10px;
        text-align: left;
    }
    .product-table th {
        background-color: #fafafa;
    }
    .product-table tr {
        border-bottom: 1px solid #ddd;
    }
    .product-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 5px;
    }
    .back-button {
        display: inline-block;
        margin-top: 20px;
        padding: 10px 20px;
        background-color: #007bff;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        text-align: center;
        cursor: pointer;
    }
    .back-button:hover {
        background-color: #0056b3;
    }
    .print-button {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #28a745;
        color: #fff;
        padding: 15px;
        border: none;
        border-radius: 50%;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        cursor: pointer;
        font-size: 18px;
    }
    .print-button:hover {
        background-color: #218838;
    }
</style>
</head>
<body>
<div class="order-details-container">
    <!-- 订单概要 -->
    <div class="card">
        <h2>Order Summary</h2>
        <div class="summary-item"><strong>User:</strong> <span><?= $order['user_name'] ?></span></div>
        <div class="summary-item"><strong>Order Date:</strong> <span><?= date("Y-m-d H:i:s", strtotime($order['order_date'])) ?></span></div>
        <div class="summary-item"><strong>Status:</strong> <span><?= $order['order_status'] ?></span></div>
        <div class="summary-item"><strong>Shipping Address:</strong> <span><?= $order['shipping_address'] ?></span></div>
        <div class="summary-item"><strong>Shipping Method:</strong> <span><?= $order['shipping_method'] ?></span></div>
    </div>

    <!-- 价格明细 -->
    <div class="card">
        <h2>Pricing Details</h2>
        <div class="summary-item"><strong>Grand Total:</strong> <span>RM <?= number_format($order['Grand_total'], 2) ?></span></div>
        <div class="summary-item"><strong>Discount:</strong> <span>RM <?= number_format($order['discount_amount'], 2) ?></span></div>
        <div class="summary-item"><strong>Delivery Charge:</strong> <span>RM <?= number_format($order['delivery_charge'], 2) ?></span></div>
        <div class="summary-item"><strong>Final Amount:</strong> <span>RM <?= number_format($order['final_amount'], 2) ?></span></div>
    </div>

    <!-- 产品明细 -->
    <div class="card">
        <h2>Product Details</h2>
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
                    <td><img src="images/<?= $detail['product_image'] ?>" alt="<?= $detail['product_name'] ?>" class="product-image"></td>
                    <td><?= $detail['product_name'] ?></td>
                    <td><?= $detail['quantity'] ?></td>
                    <td>RM <?= number_format($detail['unit_price'], 2) ?></td>
                    <td>RM <?= number_format($detail['total_price'], 2) ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- 返回订单按钮 -->
    <a href="myaccount.php" class="back-button">Back to Orders</a>
</div>

<!-- 打印收据按钮 -->
<button class="print-button" onclick="window.location.href='receipt.php?order_id=<?= $order['order_id'] ?>'">
    🖨️ Print Receipt
</button>
</body>
</html>
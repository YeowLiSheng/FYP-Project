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
</head>
<body>
<div class="order-details-container">
    <h1>Order Details #<?= $order['order_id'] ?></h1>
    <div class="order-summary">
        <h2>Order Summary</h2>
        <p><strong>User:</strong> <?= $order['user_name'] ?></p>
        <p><strong>Order Date:</strong> <?= date("Y-m-d H:i:s", strtotime($order['order_date'])) ?></p>
        <p><strong>Order Status:</strong> <?= $order['order_status'] ?></p>
        <p><strong>Shipping Address:</strong> <?= $order['shipping_address'] ?></p>
        <p><strong>Shipping Method:</strong> <?= $order['shipping_method'] ?></p>
        <p><strong>User Message:</strong> <?= $order['user_message'] ? $order['user_message'] : 'N/A' ?></p>
    </div>

    <h2>Pricing Details</h2>
    <p><strong>Grand Total:</strong> RM <?= number_format($order['Grand_total'], 2) ?></p>
    <p><strong>Discount:</strong> RM <?= number_format($order['discount_amount'], 2) ?></p>
    <p><strong>Delivery Charge:</strong> RM <?= number_format($order['delivery_charge'], 2) ?></p>
    <p><strong>Final Amount:</strong> RM <?= number_format($order['final_amount'], 2) ?></p>

    <h2>Product Details</h2>
    <table>
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
                <td><img src="images/<?= $detail['product_image'] ?>" alt="<?= $detail['product_name'] ?>" width="50"></td>
                <td><?= $detail['product_name'] ?></td>
                <td><?= $detail['quantity'] ?></td>
                <td>RM <?= number_format($detail['unit_price'], 2) ?></td>
                <td>RM <?= number_format($detail['total_price'], 2) ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <button onclick="window.location.href='myaccount.php'">Back to Orders</button>
</div>
</body>
</html>

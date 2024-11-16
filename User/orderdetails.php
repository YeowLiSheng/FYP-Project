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
<!-- 引入 Bootstrap 5 和 Font Awesome -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Arial', sans-serif;
    }
    .order-details-container {
        max-width: 900px;
        margin: 20px auto;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .order-summary, .pricing-details, .product-details {
        margin-bottom: 20px;
    }
    .order-summary h2, .pricing-details h2, .product-details h2 {
        font-size: 1.5rem;
        color: #333;
        border-bottom: 2px solid #007bff;
        padding-bottom: 8px;
    }
    .product-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 5px;
    }
    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }
    .table th {
        background-color: #f1f3f5;
    }
    .back-button {
        margin-top: 20px;
        text-align: center;
    }
</style>
</head>
<body>
<div class="order-details-container">
    <h1 class="text-center">Order Details <span class="text-muted">#<?= $order['order_id'] ?></span></h1>

    <!-- Order Summary -->
    <div class="order-summary">
        <h2><i class="fa fa-info-circle"></i> Order Summary</h2>
        <div class="row">
            <div class="col-md-6">
                <p><strong><i class="fa fa-user"></i> User:</strong> <?= $order['user_name'] ?></p>
                <p><strong><i class="fa fa-calendar"></i> Order Date:</strong> <?= date("Y-m-d H:i:s", strtotime($order['order_date'])) ?></p>
                <p><strong><i class="fa fa-shipping-fast"></i> Shipping Method:</strong> <?= $order['shipping_method'] ?></p>
            </div>
            <div class="col-md-6">
                <p><strong><i class="fa fa-map-marker-alt"></i> Shipping Address:</strong> <?= $order['shipping_address'] ?></p>
                <p><strong><i class="fa fa-comment"></i> User Message:</strong> <?= $order['user_message'] ? $order['user_message'] : 'N/A' ?></p>
                <p><strong><i class="fa fa-flag"></i> Order Status:</strong> <span class="badge bg-info"><?= $order['order_status'] ?></span></p>
            </div>
        </div>
    </div>

    <!-- Pricing Details -->
    <div class="pricing-details">
        <h2><i class="fa fa-tags"></i> Pricing Details</h2>
        <div class="row">
            <div class="col-md-4"><strong>Grand Total:</strong> RM <?= number_format($order['Grand_total'], 2) ?></div>
            <div class="col-md-4"><strong>Discount:</strong> RM <?= number_format($order['discount_amount'], 2) ?></div>
            <div class="col-md-4"><strong>Delivery Charge:</strong> RM <?= number_format($order['delivery_charge'], 2) ?></div>
            <div class="col-md-4"><strong>Final Amount:</strong> <span class="text-success">RM <?= number_format($order['final_amount'], 2) ?></span></div>
        </div>
    </div>

    <!-- Product Details -->
    <div class="product-details">
        <h2><i class="fa fa-box"></i> Product Details</h2>
        <table class="table table-striped">
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

    <!-- Back Button -->
    <div class="back-button">
        <button class="btn btn-primary" onclick="window.location.href='myaccount.php'"><i class="fa fa-arrow-left"></i> Back to Orders</button>
    </div>
</div>

<!-- 引入 Bootstrap 5 的 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
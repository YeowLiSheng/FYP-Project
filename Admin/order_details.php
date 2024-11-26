<?php
include 'dataconnection.php'; // 连接数据库
include 'admin_sidebar.php'; // 引入管理员侧边栏

if (isset($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id'];

    // 查询订单信息
    $order_query = "
        SELECT o.*, u.user_name, u.user_email, u.user_contact_number
        FROM orders o
        JOIN user u ON o.user_id = u.user_id
        WHERE o.order_id = $order_id";
    $order_result = mysqli_query($connect, $order_query);
    $order_data = mysqli_fetch_assoc($order_result);

    // 查询订单详情信息
    $order_details_query = "
        SELECT od.*, p.product_name, p.product_image
        FROM order_details od
        JOIN product p ON od.product_id = p.product_id
        WHERE od.order_id = $order_id";
    $order_details_result = mysqli_query($connect, $order_details_query);
} else {
    echo "<script>alert('Order ID is missing.'); window.location.href='admin_orders.php';</script>";
    exit();
}

// 处理订单状态更新
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_status = mysqli_real_escape_string($connect, $_POST['order_status']);
    $update_query = "UPDATE orders SET order_status = '$new_status' WHERE order_id = $order_id";
    if (mysqli_query($connect, $update_query)) {
        echo "<script>alert('Order status updated successfully.'); window.location.href='?order_id=$order_id';</script>";
    } else {
        echo "<script>alert('Failed to update order status.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- Font Awesome CDN -->
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #f7f8fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding-top: 30px;
        }
        .header {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 1.8rem;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h3 {
            margin-bottom: 15px;
            color: #444;
            border-left: 5px solid #2575fc;
            padding-left: 10px;
            font-weight: bold;
        }
        .section h3 .icon {
            color: #6a11cb;
            margin-right: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table img {
            max-width: 100px; /* 限制图片的最大宽度 */
            max-height: 100px; /* 限制图片的最大高度 */
            object-fit: cover; /* 保持图片比例且裁剪溢出的部分 */
            border-radius: 5px; /* 可选：为图片添加圆角 */
        }
        table th, table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table th {
            background-color: #f4f6f8;
            color: #333;
            font-weight: bold;
        }
        .order-summary {
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
            margin-top: 20px;
        }
        .order-summary h4 {
            margin-bottom: 10px;
            color: #444;
            font-weight: bold;
        }
        .order-summary h4 .icon {
            color: #2575fc;
            margin-right: 8px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .summary-item span {
            font-weight: 500;
        }
        .summary-item .value {
            color: #444;
        }
        .status-section {
            margin-top: 30px;
            background: #f4f6f8;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-section label .icon {
            color: #2575fc;
            margin-right: 8px;
        }
        .status-section select {
            padding: 10px;
            font-size: 14px;
            margin-left: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .status-section button {
            background: #2575fc;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .status-section button:hover {
            background: #1a5bb5;
        }
        .buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        .print-button, .back-button {
            background: #ff6b6b;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            text-align: center;
        }
        .print-button:hover, .back-button:hover {
            background: #e55b5b;
        }
        .back-button {
            background: #6c757d;
        }
        .back-button:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Order Details</h1>
    </div>
    <div class="content">
        <!-- 用户信息 -->
        <div class="section">
            <h3><i class="fas fa-user icon"></i>User Information</h3>
            <table>
                <tr><th>Name</th><td><?= $order_data['user_name'] ?></td></tr>
                <tr><th>Email</th><td><?= $order_data['user_email'] ?></td></tr>
                <tr><th>Contact</th><td><?= $order_data['user_contact_number'] ?></td></tr>
            </table>
        </div>

        <!-- 订单信息 -->
        <div class="section">
            <h3><i class="fas fa-box icon"></i>Order Information</h3>
            <table>
                <tr><th>Order ID</th><td><?= $order_data['order_id'] ?></td></tr>
                <tr><th>Date</th><td><?= $order_data['order_date'] ?></td></tr>
                <tr><th>Shipping Address</th><td><?= $order_data['shipping_address'] ?></td></tr>
                <tr><th>Shipping Method</th><td><?= $order_data['shipping_method'] ?></td></tr>
                <tr><th>User Message</th>
                    <td class="user-message"><?= $order_data['user_message'] ?? 'No message provided.' ?></td></tr>
            </table>
        </div>

        <!-- 订单详情 -->
        <div class="section">
            <h3><i class="fas fa-list-ul icon"></i>Order Items</h3>
            <table>
                <tr><th>Product</th><th>Product Name</th><th>Quantity</th><th>Unit Price</th><th>Total Price</th></tr>
                <?php while ($row = mysqli_fetch_assoc($order_details_result)): ?>
                    <tr>
                        <td><img src="../User/images/<?= $row['product_image'] ?>" alt="<?= $row['product_name'] ?>" class="product-image"></td>
                        <td><?= $row['product_name'] ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td>RM <?= number_format($row['unit_price'], 2) ?></td>
                        <td>RM <?= number_format($row['total_price'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- 订单汇总 -->
        <div class="order-summary">
            <h4><i class="fas fa-calculator icon"></i>Order Summary</h4>
            <div class="summary-item"><span>Grand Total:</span><span class="value">RM <?= number_format($order_data['grand_total'], 2) ?></span></div>
            <div class="summary-item"><span>Discount:</span><span class="value">RM <?= number_format($order_data['discount'], 2) ?></span></div>
            <div class="summary-item"><span>Final Amount:</span><span class="value">RM <?= number_format($order_data['final_amount'], 2) ?></span></div>
        </div>

        <!-- 状态更新 -->
        <div class="status-section">
            <form action="" method="POST">
                <label for="order_status"><i class="fas fa-edit icon"></i>Update Order Status:</label>
                <select name="order_status" id="order_status" required>
                    <option value="Processing" <?= $order_data['order_status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="Shipped" <?= $order_data['order_status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="Completed" <?= $order_data['order_status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                </select>
                <button type="submit">Update Status</button>
            </form>
        </div>

        <!-- 按钮 -->
        <div class="buttons">
            <a href="#" class="print-button" onclick="window.print();">Print</a>
            <a href="admin_orders.php" class="back-button">Back to Orders</a>
        </div>
    </div>
</div>

</body>
</html>

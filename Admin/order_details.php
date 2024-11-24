<?php
include 'dataconnection.php'; // 连接数据库
include 'admin_sidebar.php'; // 引入管理员侧边栏

if (isset($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id']; // 强制转换为整数以避免 SQL 注入

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
    <title>Admin - Order Details</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 20px;
            max-width: 1200px;
            margin: auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }
        h2, h3 {
            color: #444;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #f8f8f8;
            color: #444;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
        .btn:hover {
            background: #45a049;
        }
        .order-status {
            margin-top: 20px;
            text-align: right;
        }
        .order-status select {
            padding: 8px;
            margin-right: 10px;
        }
        .order-status button {
            background: #007BFF;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }
        .order-status button:hover {
            background: #0056b3;
        }
        .user-message {
            font-style: italic;
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
        }
        .total-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .total-summary h4 {
            margin: 0;
            color: #333;
        }
        .total-summary .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .total-summary .summary-row:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Order Details</h2>

        <!-- 用户信息 -->
        <h3>User Information</h3>
        <table>
            <tr>
                <th>Name</th>
                <td><?= $order_data['user_name'] ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?= $order_data['user_email'] ?></td>
            </tr>
            <tr>
                <th>Contact</th>
                <td><?= $order_data['user_contact_number'] ?></td>
            </tr>
        </table>

        <!-- 订单信息 -->
        <h3>Order Information</h3>
        <table>
            <tr>
                <th>Order ID</th>
                <td><?= $order_data['order_id'] ?></td>
            </tr>
            <tr>
                <th>Order Date</th>
                <td><?= $order_data['order_date'] ?></td>
            </tr>
            <tr>
                <th>Shipping Address</th>
                <td><?= $order_data['shipping_address'] ?></td>
            </tr>
            <tr>
                <th>Shipping Method</th>
                <td><?= $order_data['shipping_method'] ?></td>
            </tr>
            <tr>
                <th>User Message</th>
                <td class="user-message"><?= $order_data['user_message'] ?? 'No message provided.' ?></td>
            </tr>
        </table>

        <!-- 订单详情 -->
        <h3>Order Details</h3>
        <table>
            <tr>
                <th>Image</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total Price</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($order_details_result)): ?>
            <tr>
                <td><img src="../User/images/<?= $row['product_image'] ?>" alt="<?= $row['product_name'] ?>" style="width: 50px; border-radius: 4px;"></td>
                <td><?= $row['product_name'] ?></td>
                <td><?= $row['quantity'] ?></td>
                <td>RM <?= number_format($row['unit_price'], 2) ?></td>
                <td>RM <?= number_format($row['total_price'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <!-- 总计信息 -->
        <div class="total-summary">
            <h4>Order Summary</h4>
            <div class="summary-row">
                <span>Grand Total:</span>
                <span>RM <?= number_format($order_data['Grand_total'], 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Discount Amount:</span>
                <span>- RM <?= number_format($order_data['discount_amount'], 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Delivery Charge:</span>
                <span>+ RM <?= number_format($order_data['delivery_charge'], 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Total Payment:</span>
                <span>RM <?= number_format($order_data['final_amount'], 2) ?></span>
            </div>
        </div>

        <!-- 更新订单状态 -->
        <div class="order-status">
            <form method="post">
                <label for="order_status">Update Order Status:</label>
                <select name="order_status" id="order_status">
                    <option value="Processing" <?= $order_data['order_status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="Shipping" <?= $order_data['order_status'] == 'Shipping' ? 'selected' : '' ?>>Shipping</option>
                    <option value="Complete" <?= $order_data['order_status'] == 'Complete' ? 'selected' : '' ?>>Complete</option>
                </select>
                <button type="submit">Update</button>
            </form>
        </div>
    </div>
</body>
</html>

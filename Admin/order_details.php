<?php
include 'dataconnection.php'; // 连接数据库
include 'admin_sidebar.php'; // 引入管理员侧边栏

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // 查询订单信息
    $order_query = "
        SELECT o.*, u.user_name, u.user_email, u.user_contact_number
        FROM orders o
        JOIN user u ON o.user_id = u.user_id
        WHERE o.order_id = $order_id";
    $order_result = mysqli_query($conn, $order_query);
    $order_data = mysqli_fetch_assoc($order_result);

    // 查询订单详情信息
    $order_details_query = "
        SELECT od.*, p.product_name, p.product_image, p.product_price
        FROM order_details od
        JOIN product p ON od.product_id = p.product_id
        WHERE od.order_id = $order_id";
    $order_details_result = mysqli_query($conn, $order_details_query);
} else {
    echo "<script>alert('Order ID is missing.'); window.location.href='admin_orders.php';</script>";
    exit();
}

// 处理订单状态更新
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_status = $_POST['order_status'];
    $update_query = "UPDATE orders SET order_status = '$new_status' WHERE order_id = $order_id";
    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('Order status updated successfully.'); window.location.reload();</script>";
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
        /* 全局样式 */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            margin: 20px auto;
            max-width: 1200px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h2, h3 {
            color: #333;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background: #f4f4f4;
        }
        .product-image {
            width: 60px;
            height: auto;
            border-radius: 5px;
        }
        .status-update {
            text-align: right;
            margin-top: 20px;
        }
        .status-update select, .status-update button {
            padding: 8px 12px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .status-update button {
            background-color: #007BFF;
            color: white;
            cursor: pointer;
        }
        .status-update button:hover {
            background-color: #0056b3;
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
                <th>Total Amount</th>
                <td>RM <?= number_format($order_data['final_amount'], 2) ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><?= $order_data['order_status'] ?></td>
            </tr>
        </table>

        <!-- 产品详情 -->
        <h3>Products</h3>
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
                <td><img src="images/<?= $row['product_image'] ?>" alt="Product Image" class="product-image"></td>
                <td><?= $row['product_name'] ?></td>
                <td><?= $row['quantity'] ?></td>
                <td>RM <?= number_format($row['product_price'], 2) ?></td>
                <td>RM <?= number_format($row['quantity'] * $row['product_price'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <!-- 更新订单状态 -->
        <div class="status-update">
            <form method="post">
                <label for="order_status">Update Status:</label>
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

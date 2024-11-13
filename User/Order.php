<?php
// 连接数据库
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 获取所有订单
$order_sql = "SELECT * FROM orders ORDER BY order_date DESC";
$order_result = $conn->query($order_sql);

// 开始HTML输出
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order List</title>
<!-- 使用 Font Awesome CDN 引入图标 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    body {
        font-family: Arial, sans-serif;
    }
    .order-summary {
        display: flex;
        align-items: center;
        border: 1px solid #ddd;
        padding: 10px;
        margin-bottom: 10px;
        cursor: pointer;
    }
    .order-summary img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        margin-right: 10px;
    }
    .order-info {
        flex-grow: 1;
    }
    .order-status {
        font-weight: bold;
        color: #007bff;
    }
    .order-total {
        font-weight: bold;
        color: #28a745;
    }
    .details-container {
        display: none;
        margin-left: 20px;
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
// JavaScript 用于显示/隐藏订单详情
function toggleDetails(orderId) {
    const detailsContainer = document.getElementById('details-' + orderId);
    detailsContainer.style.display = detailsContainer.style.display === 'none' ? 'block' : 'none';
}
</script>
</head>
<body>
<h1>Order List</h1>
<?php
// 显示订单列表
if ($order_result->num_rows > 0) {
    while ($order = $order_result->fetch_assoc()) {
        $order_id = $order["order_id"];
        $order_status = $order["order_status"];
        
        // 获取订单详情，包含产品图片
        $detail_sql = "SELECT od.product_name, od.quantity, od.unit_price, od.total_price, p.product_image 
                       FROM order_details od
                       JOIN product p ON od.product_id = p.product_id
                       WHERE od.order_id = $order_id";
        $detail_result = $conn->query($detail_sql);

        echo "<div class='order-summary' onclick='toggleDetails($order_id)'>";
        
        // 显示订单中的第一张产品图片
        if ($detail_result->num_rows > 0) {
            $detail_row = $detail_result->fetch_assoc();
            $product_image = $detail_row['product_image'];
            echo "<img src='images/$product_image' alt='Product Image'>";
        } else {
            echo "<img src='images/default.png' alt='Default Image'>";
        }

        echo "<div class='order-info'>";
        echo "<h3>Order #" . $order["order_id"] . "</h3>";
        echo "<p><i class='fa fa-calendar'></i> Order Date: " . $order["order_date"] . "</p>";
        echo "<p class='order-status'><i class='fa fa-info-circle'></i> Status: $order_status</p>";
        echo "<p class='order-total'><i class='fa fa-money-bill'></i> Total: RM" . $order["final_amount"] . "</p>";
        echo "</div>";
        echo "</div>";

        // 订单详情部分
        echo "<div id='details-$order_id' class='details-container'>";
        if ($detail_result->num_rows > 0) {
            echo "<table class='details-table'>";
            echo "<tr><th>Product Name</th><th>Quantity</th><th>Unit Price</th><th>Total Price</th></tr>";
            
            // 重新遍历结果集以显示完整的订单详情
            $detail_result->data_seek(0);
            while ($detail_row = $detail_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $detail_row["product_name"] . "</td>";
                echo "<td>" . $detail_row["quantity"] . "</td>";
                echo "<td>RM" . $detail_row["unit_price"] . "</td>";
                echo "<td>RM" . $detail_row["total_price"] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No details available for this order.</p>";
        }
        echo "</div>";
    }
} else {
    echo "<p>No orders found.</p>";
}

// 关闭数据库连接
$conn->close();
?>
</body>
</html>

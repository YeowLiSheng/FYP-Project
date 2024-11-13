<?php
// 连接数据库
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 获取订单数据
$order_sql = "SELECT * FROM orders ORDER BY order_date DESC";
$order_result = $conn->query($order_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order History</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    body {
        font-family: Arial, sans-serif;
        display: flex;
    }
    /* 侧边栏样式 */
    .sidebar {
        width: 250px;
        background-color: #f2f2f2;
        padding: 20px;
        height: 100vh;
        position: fixed;
    }
    .sidebar h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }
    .sidebar ul {
        list-style-type: none;
        padding: 0;
    }
    .sidebar ul li {
        padding: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        position: relative;
    }
    .sidebar ul li:hover {
        background-color: #ddd;
    }
    .sidebar ul li i {
        margin-right: 10px;
    }
    .submenu {
        list-style-type: none;
        padding-left: 20px;
        display: none;
        flex-direction: column;
    }
    .submenu li {
        padding: 5px 10px;
        cursor: pointer;
    }
    .submenu li:hover {
        background-color: #e0e0e0;
    }
    .content {
        margin-left: 270px;
        padding: 20px;
        flex: 1;
    }
    /* 订单详情 */
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
// 切换子菜单的显示和隐藏
function toggleSubmenu(id) {
    const submenu = document.getElementById(id);
    if (submenu.style.display === 'block') {
        submenu.style.display = 'none';
    } else {
        submenu.style.display = 'block';
    }
}

// 切换订单详情的显示和隐藏
function toggleDetails(orderId) {
    const detailsContainer = document.getElementById('details-' + orderId);
    detailsContainer.style.display = detailsContainer.style.display === 'none' ? 'block' : 'none';
}
</script>
</head>
<body>
    <!-- 侧边栏 -->
    <div class="sidebar">
        <h2>Dashboard</h2>
        <ul>
            <li onclick="toggleSubmenu('account-submenu')">
                <i class="fa fa-user"></i> My Account
                <ul id="account-submenu" class="submenu">
                    <li onclick="alert('My Profile Clicked')"><i class="fa fa-id-badge"></i> My Profile</li>
                    <li onclick="alert('Edit Profile Clicked')"><i class="fa fa-edit"></i> Edit Profile</li>
                    <li onclick="alert('Change Password Clicked')"><i class="fa fa-key"></i> Change Password</li>
                </ul>
            </li>
            <li onclick="toggleSubmenu('order-submenu')">
                <i class="fa fa-box"></i> My Order
                <ul id="order-submenu" class="submenu">
                    <li onclick="alert('Order Status Clicked')"><i class="fa fa-clipboard"></i> Order Status</li>
                    <li onclick="alert('Purchase History Clicked')"><i class="fa fa-history"></i> Purchase History</li>
                </ul>
            </li>
        </ul>
    </div>
    <!-- 内容部分 -->
    <div class="content">
        <h1>Order History</h1>
        <?php
        if ($order_result->num_rows > 0) {
            while ($order = $order_result->fetch_assoc()) {
                $order_id = $order["order_id"];
                $order_status = $order["order_status"];

                $detail_sql = "SELECT od.product_name, od.quantity, od.unit_price, od.total_price, p.product_image 
                               FROM order_details od
                               JOIN product p ON od.product_id = p.product_id
                               WHERE od.order_id = $order_id";
                $detail_result = $conn->query($detail_sql);

                echo "<div class='order-summary' onclick='toggleDetails($order_id)'>";
                if ($detail_result->num_rows > 0) {
                    $detail_row = $detail_result->fetch_assoc();
                    echo "<img src='images/" . $detail_row['product_image'] . "' alt='Product Image'>";
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

                echo "<div id='details-$order_id' class='details-container'>";
                if ($detail_result->num_rows > 0) {
                    $detail_result->data_seek(0);
                    echo "<table class='details-table'>";
                    echo "<tr><th>Product Name</th><th>Quantity</th><th>Unit Price</th><th>Total Price</th></tr>";
                    while ($detail_row = $detail_result->fetch_assoc()) {
                        echo "<tr><td>" . $detail_row["product_name"] . "</td><td>" . $detail_row["quantity"] . "</td>";
                        echo "<td>RM" . $detail_row["unit_price"] . "</td><td>RM" . $detail_row["total_price"] . "</td></tr>";
                    }
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

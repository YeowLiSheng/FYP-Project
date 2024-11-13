<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            margin: 0;
            padding: 0;
        }
        /* Sidebar styling */
        .sidebar {
            width: 250px;
            background-color: #f4f4f4; /* 非黑色背景 */
            padding: 15px;
            height: 100vh;
            box-shadow: 2px 0px 5px rgba(0, 0, 0, 0.1);
        }
        .sidebar h2 {
            margin-top: 0;
        }
        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }
        .sidebar ul li {
            padding: 10px;
            cursor: pointer;
            color: #333;
        }
        .sidebar ul li:hover {
            background-color: #ddd;
        }
        /* Content styling */
        .content {
            flex: 1;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Order History</h2>
    <ul>
        <li onclick="showSection('account')">My Account</li>
        <ul id="account" style="display: none;">
            <li>My Profile</li>
            <li>Edit Profile</li>
            <li>Change Password</li>
        </ul>
        <li onclick="showSection('order')">My Order</li>
        <ul id="order" style="display: none;">
            <li>Order Status</li>
            <li>Purchase History</li>
        </ul>
    </ul>
</div>

<div class="content">
    <h1>Order History</h1>
    <?php
    // 连接数据库
    $conn = new mysqli("localhost", "root", "", "fyp");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // 查询订单记录
    $user_id = 36; // 假设用户ID为36，可以根据实际情况动态传递
    $order_sql = "SELECT * FROM orders WHERE user_id = $user_id";
    $order_result = $conn->query($order_sql);

    if ($order_result->num_rows > 0) {
        while ($order = $order_result->fetch_assoc()) {
            echo "<h3>Order ID: " . $order["order_id"] . "</h3>";
            echo "<p>Order Date: " . $order["order_date"] . "</p>";
            echo "<p>Grand Total: RM" . $order["Grand_total"] . "</p>";
            echo "<p>Discount: RM" . $order["discount_amount"] . "</p>";
            echo "<p>Delivery Charge: RM" . $order["delivery_charge"] . "</p>";
            echo "<p>Final Amount: RM" . $order["final_amount"] . "</p>";
            echo "<p>Order Status: " . $order["order_status"] . "</p>";
            echo "<p>Shipping Address: " . $order["shipping_address"] . "</p>";
            echo "<p>Shipping Method: " . $order["shipping_method"] . "</p>";

            // 查询订单详情
            $order_id = $order["order_id"];
            $detail_sql = "SELECT * FROM order_details WHERE order_id = $order_id";
            $detail_result = $conn->query($detail_sql);

            if ($detail_result->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Product Name</th><th>Quantity</th><th>Unit Price</th><th>Total Price</th></tr>";
                while ($detail = $detail_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $detail["product_name"] . "</td>";
                    echo "<td>" . $detail["quantity"] . "</td>";
                    echo "<td>RM" . $detail["unit_price"] . "</td>";
                    echo "<td>RM" . $detail["total_price"] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No order details found.</p>";
            }
            echo "<hr>";
        }
    } else {
        echo "<p>No orders found.</p>";
    }

    $conn->close();
    ?>
</div>

<script>
    // 显示和隐藏子菜单
    function showSection(section) {
        var element = document.getElementById(section);
        if (element.style.display === "none") {
            element.style.display = "block";
        } else {
            element.style.display = "none";
        }
    }
</script>

</body>
</html>

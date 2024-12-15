<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

$product= "SELECT COUNT(*) AS product_count FROM product";
$product_result = $connect->query($product);

$product_count = 0;
if ($product_result->num_rows > 0) {
    $row = $product_result->fetch_assoc();
    $product_count = $row['product_count'];
}

$staff= "SELECT COUNT(*) AS staff_count FROM admin";
$staff_result = $connect->query($staff);

$staff_count = 0;
if ($staff_result->num_rows > 0) {
    $row = $staff_result->fetch_assoc();
    $staff_count = $row['staff_count'];
}

$user = "SELECT COUNT(*) AS user_count FROM `user`";
$user_result = $connect->query($user);

$user_count = 0;
if ($user_result->num_rows > 0) {
    $row = $user_result->fetch_assoc();
    $user_count = $row['user_count'];
}

$order = "SELECT COUNT(*) AS order_count FROM `orders`";
$order_result = $connect->query($order);

$order_count = 0;
if ($order_result->num_rows > 0) {
    $row = $order_result->fetch_assoc();
    $order_count = $row['order_count'];
}

function getTotalSales($connect) {
    $query = "SELECT SUM(final_amount) AS total_sales FROM orders";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_assoc($result)['total_sales'];
}
$totalSales = getTotalSales($connect);

function getTopProducts($connect) {
    $query = "SELECT product_name, SUM(quantity) AS total_sold 
              FROM order_details 
              GROUP BY product_name 
              ORDER BY total_sold DESC LIMIT 5";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
$topProducts = getTopProducts($connect);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <title>Admin Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #ffffff;
        }
        .container {
            display: flex;
            flex-wrap: nowrap;
            justify-content: space-between;
            margin: 20px 260px;
            gap: 20px;
        }
        .box {
            background: #1e1e1e;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.4);
            padding: 20px;
            width: 18%;
            text-align: center;
        }
        .box i {
            font-size: 40px;
            color: #4CAF50;
            margin-bottom: 10px;
        }
        .box h2 {
            font-size: 20px;
            margin: 10px 0;
            color: #ffffff;
        }
        .box p {
            font-size: 18px;
            color: #bbbbbb;
        }
        .product-sales, .new-customers {
            margin: 20px 260px;
            font-size: 24px;
            color: #ffffff;
            font-weight: bold;
        }
        table {
            width: calc(100% - 320px);
            margin: 0 auto 20px auto;
            border-collapse: collapse;
            background: #1e1e1e;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.4);
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #333;
            color: #ffffff;
        }
        th {
            background: #4CAF50;
            color: white;
        }
        tr:hover {
            background: #333;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="box">
        <i class="fas fa-tags"></i>
        <h2>Product Categories</h2>
        <p><?php echo $product_count; ?></p>
    </div>
    <div class="box">
        <i class="fas fa-users"></i>
        <h2>Staff Members</h2>
        <p><?php echo $staff_count; ?></p>        
    </div>
    <div class="box">
        <i class="fas fa-shopping-cart"></i>
        <h2>Orders</h2>
        <p><?php echo $order_count; ?></p>
    </div>
    <div class="box">
        <i class="fas fa-users"></i>
        <h2>Customers</h2>
        <p><?php echo $user_count; ?></p>       
    </div>
    <div class="box">
        <i class="fas fa-dollar-sign"></i>
        <h2>Total Profit</h2>
        <p>RM<?php echo number_format($totalSales, 2); ?></p>
    </div>
</div>

<div class="product-sales">Top 5 Products by Sales</div>
<table>
    <thead>
        <tr>
            <th>Product Name</th>
            <th>Units Sold</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($topProducts as $product): ?>
            <tr>
                <td><?php echo $product['product_name']; ?></td>
                <td><?php echo $product['total_sold']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="new-customers">Recent Users</div>
<!-- Add recent user data here -->

</body>
</html>

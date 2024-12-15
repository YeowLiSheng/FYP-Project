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
    $query = "SELECT p.product_name, p.product_image, SUM(od.quantity) AS total_sold 
              FROM order_details od
              INNER JOIN product p ON od.product_id = p.product_id
              GROUP BY p.product_name, p.product_image
              ORDER BY total_sold DESC LIMIT 5";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
$topProducts = getTopProducts($connect)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- External CSS for cleaner code -->
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .container {
            padding: 20px;
            margin-left: 260px; /* Adjust to align next to sidebar */
            margin-top: 80px; /* Add margin to push content below sidebar header */
        }

        .cards {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(5, 1fr); /* Ensure 5 cards in one row */
            grid-gap: 20px;
        }

        .ccard {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #DFF5DB;
            border-radius: 20px;
            box-shadow: 0 7px 25px 0 rgba(0, 0, 0, 0.08);
            transition: background-color 0.3s;
        }

        .ccard:hover {
            background-color: #97EA88;
            cursor: pointer;
        }

        .icon {
            font-size: 55px;
            color: #32871F;
        }

        .number {
            font-size: 45px;
            color: #32871F;
        }

        .name {
            font-size: 25px;
            color: #555;
        }

        .product-sales {
            margin-top: 30px;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .table th, .table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .table th {
            background-color: #f4f4f4;
            color: #333;
        }

        .table-striped tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        .table-striped tbody tr:nth-child(even) {
            background-color: #fff;
        }

        .recent {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="cards">
            <div class="ccard">
                <div>
                    <p class="number"><?php echo $product_count; ?></p>
                    <p class="name">Products</p>
                </div>
                <i class="fas fa-tags icon"></i>
            </div>

            <div class="ccard">
                <div>
                    <p class="number"><?php echo $staff_count; ?></p>
                    <p class="name">Staff</p>
                </div>
                <i class="fas fa-users icon"></i>
            </div>

            <div class="ccard">
                <div>
                    <p class="number"><?php echo $order_count; ?></p>
                    <p class="name">Orders</p>
                </div>
                <i class="fas fa-shopping-cart icon"></i>
            </div>

            <div class="ccard">
                <div>
                    <p class="number"><?php echo $user_count; ?></p>
                    <p class="name">Customers</p>
                </div>
                <i class="fas fa-users icon"></i>
            </div>

            <div class="ccard">
                <div>
                    <p class="number">RM<?php echo number_format($totalSales, 2); ?></p>
                    <p class="name">Total Profit</p>
                </div>
                <i class="fas fa-dollar-sign icon"></i>
            </div>
        </div>

        <div class="product-sales">Top 5 Products by Sales</div>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Product Image</th>
            <th>Product Name</th>
            <th>Units Sold</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($topProducts as $product): ?>
            <tr>
                <td><img src="<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_name']; ?>" style="width: 80px; height: 80px; object-fit: cover;"></td>
                <td><?php echo $product['product_name']; ?></td>
                <td><?php echo $product['total_sold']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    </div>
</body>
</html>

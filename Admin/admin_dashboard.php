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
$topProducts = getTopProducts($connect);

function getWeeklySalesWithDates($connect) {
    $query = "
        SELECT 
            CONCAT(DATE_FORMAT(order_date, '%Y-%m-%d'), ' ~ ', DATE_FORMAT(order_date + INTERVAL (6 - WEEKDAY(order_date)) DAY, '%Y-%m-%d')) AS week_range, 
            SUM(final_amount) AS total_sales
        FROM orders
        WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) -- 最近一个月
        GROUP BY YEAR(order_date), WEEK(order_date)";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
$weeklySales = getWeeklySalesWithDates($connect);


function getRecentUsers($connect) {
    $query = "SELECT user_name, user_image, user_email, user_join_time 
              FROM `user`
              ORDER BY user_join_time DESC
              LIMIT 5";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
$recentUsers = getRecentUsers($connect);

function getGenderDistribution($connect) {
    $query = "SELECT user_gender, COUNT(*) AS count 
              FROM `user`
              GROUP BY user_gender";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
$genderDistribution = getGenderDistribution($connect);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            background-color: #f9fafb;
            color: #333;
        }
        .container {
            margin: 20px auto;
            padding: 20px;
            max-width: 1200px;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .card .icon {
            font-size: 2.5rem;
            color: #17a2b8;
            margin-bottom: 10px;
        }
        .card .number {
            font-size: 1.8rem;
            font-weight: 700;
        }
        .card .name {
            font-size: 1rem;
            color: #777;
        }
        .chart-container, .table-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f1f1f1;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .table img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }
        h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            border-bottom: 2px solid #f1f1f1;
            display: inline-block;
        }
        .legend {
            display: flex;
            justify-content: center;
            margin-top: 10px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 卡片区域 -->
        <div class="cards">
            <div class="card">
                <i class="fas fa-tags icon"></i>
                <div class="number"><?php echo $product_count; ?></div>
                <div class="name">Products</div>
            </div>
            <div class="card">
                <i class="fas fa-users icon"></i>
                <div class="number"><?php echo $staff_count; ?></div>
                <div class="name">Staff</div>
            </div>
            <div class="card">
                <i class="fas fa-shopping-cart icon"></i>
                <div class="number"><?php echo $order_count; ?></div>
                <div class="name">Orders</div>
            </div>
            <div class="card">
                <i class="fas fa-dollar-sign icon"></i>
                <div class="number">RM<?php echo number_format($totalSales, 2); ?></div>
                <div class="name">Total Profit</div>
            </div>
        </div>

        <!-- Top 5 Products -->
        <div class="table-container">
            <h2>Top Selling Product</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Orders</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($topProducts, 0, 5) as $product): ?>
                        <tr>
                            <td>
                                <img src="../User/images/<?php echo $product['product_image']; ?>" alt="Image">
                                <?php echo $product['product_name']; ?>
                            </td>
                            <td><?php echo $product['total_sold']; ?></td>
                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Weekly Sales Chart -->
        <div class="chart-container">
            <h2>Weekly Sales Comparison</h2>
            <canvas id="weeklySalesChart"></canvas>
        </div>

        <!-- Gender Distribution Chart -->
        <div class="chart-container">
            <h2>Gender Distribution</h2>
            <canvas id="genderChart"></canvas>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script>
        // Weekly Sales Chart
        const weeklySalesData = <?php echo json_encode($weeklySales); ?>;
        const weeklyLabels = weeklySalesData.map(data => data.week_range);
        const weeklySales = weeklySalesData.map(data => data.total_sales);
        const ctxWeekly = document.getElementById('weeklySalesChart').getContext('2d');

        new Chart(ctxWeekly, {
            type: 'line',
            data: {
                labels: weeklyLabels,
                datasets: [{
                    label: 'Sales (RM)',
                    data: weeklySales,
                    fill: true,
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.2)',
                    tension: 0.4,
                }]
            }
        });

        // Gender Chart
        const genderData = <?php echo json_encode($genderDistribution); ?>;
        const genderLabels = genderData.map(g => g.user_gender);
        const genderCounts = genderData.map(g => g.count);

        const ctxGender = document.getElementById('genderChart').getContext('2d');
        new Chart(ctxGender, {
            type: 'pie',
            data: {
                labels: genderLabels,
                datasets: [{
                    data: genderCounts,
                    backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56']
                }]
            },
            options: {
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let total = genderCounts.reduce((a, b) => a + b, 0);
                                let value = genderCounts[context.dataIndex];
                                let percentage = ((value / total) * 100).toFixed(2);
                                return `${genderLabels[context.dataIndex]}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>


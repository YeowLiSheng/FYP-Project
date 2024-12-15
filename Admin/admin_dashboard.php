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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            background-color: #f9f9fb;
            color: #333;
        }

        .dashboard {
            display: flex;
            flex-wrap: wrap;
        }

        .main-content {
            width: 70%;
            padding: 20px;
        }

        .sidebar {
            width: 30%;
            background-color: #fdfdfd;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
        }

        .top-section, .card-section, .table-section {
            margin-bottom: 20px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .top-section h2 {
            margin: 0 0 10px;
            font-weight: 700;
        }

        .card-container {
            display: flex;
            justify-content: space-around;
        }

        .card {
            text-align: center;
            background-color: #fdfdfd;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .card i {
            font-size: 30px;
            margin-bottom: 10px;
            color: #4a90e2;
        }

        .table-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-section th, .table-section td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .table-section th {
            text-align: left;
            color: #555;
        }

        .sidebar .user-list, .sidebar .pie-chart {
            margin-bottom: 30px;
        }

        .user-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .user-item img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .chart-container {
            height: 300px;
        }
    </style>
</head>
<body>
<div class="dashboard">
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Section -->
        <div class="top-section">
            <h2>Top Selling Product</h2>
            <div class="card-container">
                <div class="card">
                    <i class="fas fa-shoe-prints"></i>
                    <p>Nike v22</p>
                    <span>8000 Orders</span>
                </div>
                <div class="card">
                    <i class="fas fa-camera"></i>
                    <p>Instax Camera</p>
                    <span>3000 Orders</span>
                </div>
                <div class="card">
                    <i class="fas fa-chair"></i>
                    <p>Chair</p>
                    <span>6000 Orders</span>
                </div>
                <div class="card">
                    <i class="fas fa-laptop"></i>
                    <p>Laptop</p>
                    <span>4000 Orders</span>
                </div>
                <div class="card">
                    <i class="fas fa-clock"></i>
                    <p>Watch</p>
                    <span>2000 Orders</span>
                </div>
            </div>
        </div>

        <!-- Product Table -->
        <div class="table-section">
            <h2>Top 5 Products by Sales</h2>
            <table>
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
                        <td><img src="../User/images/<?php echo $product['product_image']; ?>" alt="" style="width: 60px; border-radius: 5px;"></td>
                        <td><?php echo $product['product_name']; ?></td>
                        <td><?php echo $product['total_sold']; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Sales Chart -->
        <div class="chart-container">
            <canvas id="weeklySalesChart"></canvas>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="user-list">
            <h3>New Customers</h3>
            <?php foreach ($recentUsers as $user): ?>
                <div class="user-item">
                    <img src="../User/<?php echo $user['user_image'] ?? 'default.png'; ?>" alt="User">
                    <div>
                        <p><strong><?php echo htmlspecialchars($user['user_name']); ?></strong></p>
                        <p><?php echo htmlspecialchars($user['user_email']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Gender Pie Chart -->
        <div class="pie-chart">
            <h3>Buyers Profile</h3>
            <canvas id="genderPieChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Weekly Sales Chart
    const salesData = <?php echo json_encode($weeklySales); ?>;
    const labels = salesData.map(data => data.week_range);
    const sales = salesData.map(data => data.total_sales);

    new Chart(document.getElementById('weeklySalesChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Weekly Sales',
                data: sales,
                borderColor: '#4a90e2',
                backgroundColor: 'rgba(74, 144, 226, 0.1)',
                borderWidth: 2,
                tension: 0.4,
            }]
        },
        options: {
            responsive: true,
        }
    });

    // Gender Pie Chart
    const genderLabels = <?php echo json_encode(array_column($genderDistribution, 'user_gender')); ?>;
    const genderCounts = <?php echo json_encode(array_column($genderDistribution, 'count')); ?>;

    new Chart(document.getElementById('genderPieChart'), {
        type: 'pie',
        data: {
            labels: genderLabels,
            datasets: [{
                data: genderCounts,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                hoverOffset: 4
            }]
        }
    });
</script>
</body>
</html>


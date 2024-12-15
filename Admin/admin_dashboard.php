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

function getLowStockProducts($connect) {
    $query = "SELECT product_name, product_image, product_stock 
              FROM product 
              ORDER BY product_stock ASC 
              LIMIT 5";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$lowStockProducts = getLowStockProducts($connect);
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
    padding: 0;
    background-color: #f8f9fa;
    color: #333;
}

.container {
    padding: 20px;
    margin-left: 260px;
    margin-top: 80px;
}

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.ccard {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

.ccard:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.icon {
    font-size: 50px;
    color: #17a2b8;
    margin-bottom: 10px;
}

.number {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
}

.name {
    font-size: 1rem;
    font-weight: 500;
    color: #555;
}

.section-header {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 15px;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 5px;
}

.table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
}

.table th, .table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
    font-size: 0.9rem;
}

.table th {
    background-color: #f1f3f5;
    font-weight: 700;
    color: #495057;
}

.table-striped tbody tr:nth-child(odd) {
    background-color: #f8f9fa;
}

.chart-container {
    margin-top: 30px;
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .container {
        margin-left: 0;
        padding: 15px;
    }

    .cards {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
}

/* 新增CSS: 实现截图类似布局 */
.cards-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
    background: #fff;
    border-radius: 15px;
    padding: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    max-width: 400px;
    margin-top: 30px; /* 与其他内容分隔 */
}

.card {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #f1f1f1;
}

.card:last-child {
    border-bottom: none;
}

.card .avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin-right: 15px;
    object-fit: cover;
}

.card .info {
    flex: 1;
}

.card .info .name {
    font-weight: bold;
    font-size: 1rem;
    margin-bottom: 5px;
}

.card .info .location {
    color: #666;
    font-size: 0.9rem;
}

.card .action {
    background-color: #f1f1f1;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.3s;
}

.card .action:hover {
    background-color: #ffc107;
}
    </style>
</head>
<body>
    <div class="container">
        <!-- Cards -->
        <div class="cards">
            <div class="ccard">
                <i class="fas fa-tags icon"></i>
                <p class="number"><?php echo $product_count; ?></p>
                <p class="name">Products</p>
            </div>
            <div class="ccard">
                <i class="fas fa-users icon"></i>
                <p class="number"><?php echo $staff_count; ?></p>
                <p class="name">Staff</p>
            </div>
            <div class="ccard">
                <i class="fas fa-shopping-cart icon"></i>
                <p class="number"><?php echo $order_count; ?></p>
                <p class="name">Orders</p>
            </div>
            <div class="ccard">
                <i class="fas fa-users icon"></i>
                <p class="number"><?php echo $user_count; ?></p>
                <p class="name">Customers</p>
            </div>
            <div class="ccard">
                <i class="fas fa-dollar-sign icon"></i>
                <p class="number">RM<?php echo number_format($totalSales, 2); ?></p>
                <p class="name">Total Profit</p>
            </div>
        </div>

        <!-- Weekly Sales Chart -->
        <div class="chart-container">
            <h2 style="text-align: center;">Weekly Sales Comparison</h2>
            <canvas id="weeklySalesChart"></canvas>
        </div>

        <!-- Top 5 Products -->
        <div>
            <div class="section-header">Top 5 Products by Sales</div>
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
                            <td><img src="../User/images/<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_name']; ?>" style="width: 60px; height: 60px; border-radius: 8px;"></td>
                            <td><?php echo $product['product_name']; ?></td>
                            <td><?php echo $product['total_sold']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Users -->
        <div class="section-header">Recent Users</div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Join Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentUsers as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['user_email']); ?></td>
                        <td><?php echo htmlspecialchars($user['user_join_time']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="low-stock-products">
    <h2>Products with Low Stock</h2>
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Image</th>
                <th>Stock Quantity</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lowStockProducts as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                    <td>
                        <img src="../User/images/<?php echo htmlspecialchars($product['product_image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                             style="width: 100px; height: auto;">
                    </td>
                    <td><?php echo htmlspecialchars($product['product_stock']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

        <!-- Gender Chart -->
        <div class="chart-container">
            <h2 style="text-align: center;">Gender Distribution</h2>
            <canvas id="genderPieChart"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const weeklySalesData = <?php echo json_encode($weeklySales); ?>;
        const labels = weeklySalesData.map(data => data.week_range);
        const sales = weeklySalesData.map(data => data.total_sales);

        const ctx = document.getElementById('weeklySalesChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(75, 192, 192, 0.6)');
        gradient.addColorStop(1, 'rgba(75, 192, 192, 0.1)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Sales (Weekly)',
                    data: sales,
                    fill: true,
                    backgroundColor: gradient,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(75, 192, 192, 1)',
                    pointBorderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { title: { display: true, text: 'Week Range' } },
                    y: { beginAtZero: true, title: { display: true, text: 'Total Sales (RM)' } }
                }
            }
        });

        const genderLabels = <?php echo json_encode(array_column($genderDistribution, 'user_gender')); ?>;
        const genderCounts = <?php echo json_encode(array_column($genderDistribution, 'count')); ?>;

        const genderCtx = document.getElementById('genderPieChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: genderLabels,
                datasets: [{
                    label: 'Customer Gender Distribution',
                    data: genderCounts,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                const percentage = (genderCounts[tooltipItem.dataIndex] /
                                                    genderCounts.reduce((a, b) => a + b, 0) * 100).toFixed(2);
                                return `${genderLabels[tooltipItem.dataIndex]}: ${percentage}%`;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>


<?php
include 'dataconnection.php'; // 数据库连接
include 'admin_sidebar.php'; // 管理员侧边栏

// 获取总订单数、总客户数、总销售额
$totalOrdersQuery = "SELECT COUNT(order_id) AS total_orders FROM orders";
$totalOrdersResult = mysqli_query($connect, $totalOrdersQuery);
$totalOrders = mysqli_fetch_assoc($totalOrdersResult)['total_orders'];

$totalCustomersQuery = "SELECT COUNT(DISTINCT user_id) AS total_customers FROM orders";
$totalCustomersResult = mysqli_query($connect, $totalCustomersQuery);
$totalCustomers = mysqli_fetch_assoc($totalCustomersResult)['total_customers'];

$totalSalesQuery = "SELECT SUM(final_amount) AS total_sales FROM orders";
$totalSalesResult = mysqli_query($connect, $totalSalesQuery);
$totalSales = mysqli_fetch_assoc($totalSalesResult)['total_sales'];

// 获取分类的销售占比数据
$categorySalesQuery = "
    SELECT c.category_name, SUM(od.total_price) AS total_sales 
    FROM order_details od 
    JOIN product p ON od.product_id = p.product_id 
    JOIN category c ON p.category_id = c.category_id 
    GROUP BY c.category_id
";
$categorySalesResult = mysqli_query($connect, $categorySalesQuery);
$categorySales = [];
while ($row = mysqli_fetch_assoc($categorySalesResult)) {
    $categorySales[] = $row;
}

// 获取畅销商品数据
$topProductsQuery = "
    SELECT od.product_name, SUM(od.quantity) AS total_sold 
    FROM order_details od 
    GROUP BY od.product_id 
    ORDER BY total_sold DESC 
    LIMIT 5
";
$topProductsResult = mysqli_query($connect, $topProductsQuery);
$topProducts = [];
while ($row = mysqli_fetch_assoc($topProductsResult)) {
    $topProducts[] = $row;
}

// 获取每日销售趋势数据
$salesTrendQuery = "
    SELECT DATE(order_date) AS sale_date, SUM(final_amount) AS daily_sales 
    FROM orders 
    WHERE order_date BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() 
    GROUP BY sale_date
";
$salesTrendResult = mysqli_query($connect, $salesTrendQuery);
$salesTrend = [];
while ($row = mysqli_fetch_assoc($salesTrendResult)) {
    $salesTrend[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .summary-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .summary-box h5 {
            font-size: 18px;
            color: #555;
        }
        .summary-box p {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        canvas {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<div class="container my-4">
    <h1 class="mb-4 text-center">Sales Report</h1>

    <!-- Summary Section -->
    <div class="row">
        <div class="col-md-4">
            <div class="summary-box">
                <h5>Total Orders</h5>
                <p><?= $totalOrders ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-box">
                <h5>Total Customers</h5>
                <p><?= $totalCustomers ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-box">
                <h5>Total Sales</h5>
                <p>RM <?= number_format($totalSales, 2) ?></p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <!-- Pie Chart -->
        <div class="col-md-6">
            <h5 class="text-center">Category Sales Distribution</h5>
            <canvas id="categorySalesChart"></canvas>
        </div>

        <!-- Line Chart -->
        <div class="col-md-6">
            <h5 class="text-center">Sales Trend (Last 30 Days)</h5>
            <canvas id="salesTrendChart"></canvas>
        </div>
    </div>

    <!-- Top Products Section -->
    <div class="mt-5">
        <h5 class="text-center">Top 5 Best-Selling Products</h5>
        <table class="table table-bordered">
            <thead class="table-dark">
            <tr>
                <th>Product Name</th>
                <th>Total Sold</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($topProducts as $product): ?>
                <tr>
                    <td><?= $product['product_name'] ?></td>
                    <td><?= $product['total_sold'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- JavaScript for Charts -->
<script>
    // Category Sales Pie Chart
    const categorySalesData = <?= json_encode(array_column($categorySales, 'total_sales')) ?>;
    const categorySalesLabels = <?= json_encode(array_column($categorySales, 'category_name')) ?>;
    new Chart(document.getElementById('categorySalesChart'), {
        type: 'pie',
        data: {
            labels: categorySalesLabels,
            datasets: [{
                data: categorySalesData,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
            }]
        }
    });

    // Sales Trend Line Chart
    const salesTrendData = <?= json_encode(array_column($salesTrend, 'daily_sales')) ?>;
    const salesTrendLabels = <?= json_encode(array_column($salesTrend, 'sale_date')) ?>;
    new Chart(document.getElementById('salesTrendChart'), {
        type: 'line',
        data: {
            labels: salesTrendLabels,
            datasets: [{
                label: 'Daily Sales (RM)',
                data: salesTrendData,
                borderColor: '#36A2EB',
                fill: false
            }]
        }
    });
</script>
</body>
</html>

<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

// 数据库查询功能
function getTotalOrders($connect) {
    $query = "SELECT COUNT(order_id) AS total_orders FROM orders";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_assoc($result)['total_orders'];
}

function getTotalCustomers($connect) {
    $query = "SELECT COUNT(DISTINCT user_id) AS total_customers FROM orders";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_assoc($result)['total_customers'];
}

function getTotalSales($connect) {
    $query = "SELECT SUM(final_amount) AS total_sales FROM orders";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_assoc($result)['total_sales'];
}

function getCategorySales($connect) {
    $query = "SELECT c.category_name, SUM(od.total_price) AS category_sales 
              FROM order_details od 
              JOIN product p ON od.product_id = p.product_id 
              JOIN category c ON p.category_id = c.category_id 
              GROUP BY c.category_name";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getTopProducts($connect) {
    $query = "SELECT product_name, SUM(quantity) AS total_sold, SUM(total_price) AS total_revenue 
              FROM order_details 
              GROUP BY product_name 
              ORDER BY total_sold DESC LIMIT 5";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getTopCustomers($connect) {
    $query = "SELECT u.user_name, u.user_email, SUM(o.final_amount) AS total_spent 
    FROM orders o
    JOIN user u ON o.user_id = u.user_id
    GROUP BY o.user_id
    ORDER BY total_spent DESC LIMIT 5";
$result = mysqli_query($connect, $query);
return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// 获取销售趋势数据，根据日期范围过滤
function getSalesTrend($connect, $startDate, $endDate) {
    $query = "SELECT DATE(order_date) AS date, SUM(final_amount) AS daily_sales 
              FROM orders 
              WHERE DATE(order_date) BETWEEN '$startDate' AND '$endDate'
              GROUP BY DATE(order_date) 
              ORDER BY DATE(order_date)";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// 获取表单数据（如果有的话）
$startDate = isset($_POST['start_date']) ? date('Y-m-d', strtotime($_POST['start_date'])) : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_POST['end_date']) ? date('Y-m-d', strtotime($_POST['end_date'])) : date('Y-m-d');

// 数据获取
$totalOrders = getTotalOrders($connect);
$totalCustomers = getTotalCustomers($connect);
$totalSales = getTotalSales($connect);
$categorySales = getCategorySales($connect);
$topProducts = getTopProducts($connect);
$topCustomers= getTopCustomers(connect:$connect);
$salesTrend = getSalesTrend($connect, $startDate, $endDate);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sales Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .content-wrapper {
            margin-left: 250px;
            padding: 20px;
            padding-top: 80px;
        }
        .dashboard-row {
            display: flex;
    gap: 20px; /* 增加间距 */
    justify-content: space-between;
    flex-wrap: wrap; /* 响应式布局 */
        }
        .dashboard-card, .chart-container, .table-card {
            flex: 1;
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .dashboard-card {
    flex-grow: 1;
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 30px; /* 增加内边距 */
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease; /* 添加过渡效果 */
}

.dashboard-card h5 {
    font-size: 1.2rem;
    margin-bottom: 10px;
    font-weight: 600;
}

.dashboard-card h2 {
    font-size: 2.5rem;
    font-weight: 700;
}

.dashboard-card:hover {
    transform: translateY(-5px); /* 悬停提升效果 */
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
}
        .chart-wrapper {
            position: relative;
            width: 100%;
            height: 300px;
        }
        .date-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        .date-filter input[type="date"] {
            max-width: 45%;
        }
        .table-card .card-header {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .table-container {
            overflow-x: auto;
        }
        @media screen and (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
                padding-top: 20px;
            }
            .date-filter input[type="date"] {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="mb-4">
            <h1 class="display-4">Sales Report</h1>
        </div>

        <!-- Overview Section -->
        <div class="row mb-4 dashboard-row">

    <div class="dashboard-card">
        <h5>Total Orders</h5>
        <h2><?php echo $totalOrders; ?></h2>
    </div>
    <div class="dashboard-card">
        <h5>Total Customers</h5>
        <h2><?php echo $totalCustomers; ?></h2>
    </div>
    <div class="dashboard-card">
        <h5>Total Sales</h5>
        <h2>RM <?php echo number_format($totalSales, 2); ?></h2>
    </div>
    <div class="dashboard-card">
        <h5>Top Category</h5>
        <h2><?php echo $categorySales[0]['category_name'] ?? 'N/A'; ?></h2>
    </div>
</div>

        <!-- Charts Section -->
        <div class="row mb-4 dashboard-row">
            <div class="chart-container">
                <h3 class="card-header">Category Sales Distribution</h3>
                <div class="chart-wrapper">
                    <canvas id="categoryPieChart"></canvas>
                </div>
            </div>
            <div class="chart-container">
                <h3 class="card-header">Sales Trend (Last 30 Days)</h3>
                <form method="POST" class="date-filter">
                    <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>" onchange="this.form.submit()">
                    <input type="date" class="form-control" name="end_date" value="<?php echo $endDate; ?>" onchange="this.form.submit()">
                </form>
                <div class="chart-wrapper">
                    <canvas id="salesTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="row dashboard-row">
            <div class="table-card">
                <div class="card-header">Top 5 Products by Sales</div>
                <div class="table-container">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Units Sold</th>
                                <th>Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topProducts as $Customers): ?>
                                <tr>
                                    <td><?php echo $Customers['product_name']; ?></td>
                                    <td><?php echo $Customers['total_sold']; ?></td>
                                    <td>RM <?php echo number_format($Customers['total_spent'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="table-card">
                <div class="card-header">Top 5 Customers</div>
                <div class="table-container">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Customers Name</th>
                                <th>Email</th>
                                <th>Total Spending</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topCustomers as $product): ?>
                                <tr>
                                    <td><?php echo $product['product_name']; ?></td>
                                    <td><?php echo $product['total_sold']; ?></td>
                                    <td>RM <?php echo number_format($product['total_revenue'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Category Pie Chart
        const categoryData = <?php echo json_encode(array_column($categorySales, 'category_sales')); ?>;
        const categoryLabels = <?php echo json_encode(array_column($categorySales, 'category_name')); ?>;
        new Chart(document.getElementById('categoryPieChart'), {
            type: 'pie',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryData,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            },
            options: {
                maintainAspectRatio: false
            }
        });

        // Sales Trend Line Chart
        const salesTrendData = <?php echo json_encode(array_column($salesTrend, 'daily_sales')); ?>;
        const salesTrendLabels = <?php echo json_encode(array_column($salesTrend, 'date')); ?>;
        new Chart(document.getElementById('salesTrendChart'), {
            type: 'line',
            data: {
                labels: salesTrendLabels,
                datasets: [{
                    label: 'Daily Sales',
                    data: salesTrendData,
                    borderColor: '#4BC0C0',
                    fill: false
                }]
            },
            options: {
                maintainAspectRatio: false
            }
        });
    </script>
</body>
</html>

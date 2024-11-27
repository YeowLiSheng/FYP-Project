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

// 获取每月订单数量
function getMonthlyOrderCount($connect, $startDate, $endDate) {
    $query = "SELECT MONTH(order_date) AS month, COUNT(order_id) AS order_count 
              FROM orders 
              WHERE DATE(order_date) BETWEEN '$startDate' AND '$endDate'
              GROUP BY MONTH(order_date)
              ORDER BY MONTH(order_date)";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// 获取最活跃客户数据
function getActiveCustomers($connect) {
    $query = "SELECT u.user_id, u.name, SUM(od.total_price) AS total_spent
              FROM orders o
              JOIN order_details od ON o.order_id = od.order_id
              JOIN users u ON o.user_id = u.user_id
              GROUP BY u.user_id
              ORDER BY total_spent DESC LIMIT 5";
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
$salesTrend = getSalesTrend($connect, $startDate, $endDate);
$monthlyOrderCount = getMonthlyOrderCount($connect, $startDate, $endDate);
$activeCustomers = getActiveCustomers($connect);
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
        .dashboard-card {
            color: #fff;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            min-height: 150px;
        }
        .chart-container, .table-container {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .chart-container {
            height: 400px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .chart-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
        }
        .table-container {
            overflow-x: auto;
        }
        .card-header {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .table thead th {
            color: #333;
            font-weight: bold;
        }
        @media screen and (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
                padding-top: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="mb-4">
            <h1 class="display-4">Sales Dashboard</h1>
            <p class="lead">View a comprehensive overview of your sales performance with detailed insights on orders, customers, and product categories.</p>
        </div>

        <!-- Overview Section -->
        <div class="row mb-4">
            <!-- Cards -->
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h5>Total Orders</h5>
                    <h2><?php echo $totalOrders; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h5>Total Customers</h5>
                    <h2><?php echo $totalCustomers; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h5>Total Sales</h5>
                    <h2>RM <?php echo number_format($totalSales, 2); ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h5>Top Category</h5>
                    <h2><?php echo $categorySales[0]['category_name'] ?? 'N/A'; ?></h2>
                </div>
            </div>
        </div>

        <!-- Date Picker -->
        <form method="POST" class="mb-4" id="dateForm">
            <div class="row">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>" onchange="this.form.submit()">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>" onchange="this.form.submit()">
                </div>
            </div>
        </form>

        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="card-header">Sales Trend (Last 30 Days)</h3>
                    <div class="chart-wrapper">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="card-header">Monthly Order Count</h3>
                    <div class="chart-wrapper">
                        <canvas id="monthlyOrderChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="row">
            <div class="col-md-6">
                <div class="table-container">
                    <div class="card-header">Top 5 Products by Sales</div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Units Sold</th>
                                <th>Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topProducts as $product): ?>
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
            <div class="col-md-6">
                <div class="table-container">
                    <div class="card-header">Active Customers (Top 5)</div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th>Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeCustomers as $customer): ?>
                                <tr>
                                    <td><?php echo $customer['name']; ?></td>
                                    <td>RM <?php echo number_format($customer['total_spent'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script>
        // Sales Trend Chart
        const salesTrendData = <?php echo json_encode($salesTrend); ?>;
        const salesTrendChart = new Chart(document.getElementById('salesTrendChart'), {
            type: 'line',
            data: {
                labels: salesTrendData.map(data => data.date),
                datasets: [{
                    label: 'Daily Sales',
                    data: salesTrendData.map(data => data.daily_sales),
                    borderColor: '#007bff',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: { display: true, text: 'Sales Trend' },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: { 
                        title: { display: true, text: 'Date' },
                        ticks: { autoSkip: true, maxTicksLimit: 7 }
                    },
                    y: { title: { display: true, text: 'Sales (RM)' } }
                }
            }
        });

        // Monthly Order Count Chart
        const monthlyOrderData = <?php echo json_encode($monthlyOrderCount); ?>;
        const monthlyOrderChart = new Chart(document.getElementById('monthlyOrderChart'), {
            type: 'bar',
            data: {
                labels: monthlyOrderData.map(data => data.month),
                datasets: [{
                    label: 'Orders per Month',
                    data: monthlyOrderData.map(data => data.order_count),
                    backgroundColor: '#28a745'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: { display: true, text: 'Orders per Month' }
                },
                scales: {
                    x: { title: { display: true, text: 'Month' } },
                    y: { title: { display: true, text: 'Order Count' } }
                }
            }
        });
    </script>
</body>
</html>

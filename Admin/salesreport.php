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
            background-color: #f4f5f7;
            font-family: 'Poppins', sans-serif;
        }
        .content-wrapper {
            padding: 20px;
        }
        .dashboard-card {
            color: #fff;
            background: linear-gradient(135deg, #4e73df, #1cc88a);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        .chart-container, .table-container {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .chart-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
        }
        .card-header {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 15px;
            color: #4e73df;
        }
        .date-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .table {
            margin: 0;
            border-collapse: collapse;
            border-spacing: 0;
        }
        .table th {
            background-color: #4e73df;
            color: #fff;
            text-align: center;
        }
        .table tbody tr:nth-child(odd) {
            background-color: #f8f9fc;
        }
        .table tbody tr:hover {
            background-color: #e3e6f0;
        }
        @media screen and (max-width: 768px) {
            .content-wrapper {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="mb-4">
            <h1 class="display-5 text-center text-primary">Sales Dashboard</h1>
        </div>

        <!-- Overview Section -->
        <div class="row mb-4 g-4">
            <!-- Cards -->
            <div class="col-md-3 col-sm-6">
                <div class="dashboard-card">
                    <h6>Total Orders</h6>
                    <h2><?php echo $totalOrders; ?></h2>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="dashboard-card">
                    <h6>Total Customers</h6>
                    <h2><?php echo $totalCustomers; ?></h2>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="dashboard-card">
                    <h6>Total Sales</h6>
                    <h2>RM <?php echo number_format($totalSales, 2); ?></h2>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="dashboard-card">
                    <h6>Top Category</h6>
                    <h2><?php echo $categorySales[0]['category_name'] ?? 'N/A'; ?></h2>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mb-4 g-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="card-header">Category Sales Distribution</h3>
                    <div class="chart-wrapper">
                        <canvas id="categoryPieChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="card-header">Sales Trend</h3>
                    <div class="date-filter">
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>" onchange="document.getElementById('dateForm').submit();">
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>" onchange="document.getElementById('dateForm').submit();">
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="table-container">
                    <div class="card-header">Top 5 Products by Sales</div>
                    <table class="table table-hover text-center">
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

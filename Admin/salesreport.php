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
            background-color: #eef2f3;
            font-family: 'Poppins', sans-serif;
        }
        .content-wrapper {
            margin-left: 250px;
            padding: 30px;
        }
        .dashboard-card {
            color: #fff;
            background: linear-gradient(135deg, #7f00ff, #e100ff);
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
            min-height: 150px;
            position: relative;
        }
        .dashboard-card h5 {
            font-size: 1.2rem;
        }
        .dashboard-card h2 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-top: 10px;
        }
        .dashboard-card i {
            font-size: 2.5rem;
            position: absolute;
            top: 10px;
            right: 20px;
            opacity: 0.3;
        }
        .chart-container, .table-container {
            background: #fff;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        .chart-wrapper {
            position: relative;
            width: 100%;
            height: 350px;
        }
        .table-container {
            overflow-x: auto;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }
        @media screen and (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
                padding-top: 20px;
            }
            .dashboard-card i {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <h1 class="display-4 mb-4">Sales Dashboard</h1>

        <!-- Overview Section -->
        <div class="row g-4">
            <div class="col-md-3">
                <div class="dashboard-card">
                    <i class="bi bi-cart-check"></i>
                    <h5>Total Orders</h5>
                    <h2><?php echo $totalOrders; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <i class="bi bi-people-fill"></i>
                    <h5>Total Customers</h5>
                    <h2><?php echo $totalCustomers; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <i class="bi bi-cash-coin"></i>
                    <h5>Total Sales</h5>
                    <h2>RM <?php echo number_format($totalSales, 2); ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <i class="bi bi-bar-chart-fill"></i>
                    <h5>Top Category</h5>
                    <h2><?php echo $categorySales[0]['category_name'] ?? 'N/A'; ?></h2>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row g-4 mt-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="mb-3">Category Sales Distribution</h3>
                    <div class="chart-wrapper">
                        <canvas id="categoryPieChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="mb-3">Sales Trend (Last 30 Days)</h3>
                    <form method="POST" class="mb-3">
                        <div class="row g-2">
                            <div class="col">
                                <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>">
                            </div>
                            <div class="col">
                                <input type="date" class="form-control" name="end_date" value="<?php echo $endDate; ?>">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                    </form>
                    <div class="chart-wrapper">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="row g-4 mt-4">
            <div class="col-md-6">
                <div class="table-container">
                    <h3 class="mb-3">Top 5 Products by Sales</h3>
                    <table class="table table-striped table-hover">
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
        // Charts
        new Chart(document.getElementById('categoryPieChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($categorySales, 'category_name')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($categorySales, 'category_sales')); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            }
        });

        new Chart(document.getElementById('salesTrendChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($salesTrend, 'date')); ?>,
                datasets: [{
                    label: 'Daily Sales',
                    data: <?php echo json_encode(array_column($salesTrend, 'daily_sales')); ?>,
                    borderColor: '#4BC0C0',
                    fill: false
                }]
            }
        });
    </script>
</body>
</html>

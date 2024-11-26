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
            background-color: #f3f6f9;
            font-family: 'Roboto', sans-serif;
            color: #333;
        }
        .content-wrapper {
            margin-left: 250px;
            padding: 30px;
            padding-top: 100px; /* 提高顶部间距以避免重叠 */
        }
        .dashboard-card {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: #fff;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease-in-out;
        }
        .dashboard-card:hover {
            transform: scale(1.05);
        }
        .card-header {
            font-size: 1.5rem;
            font-weight: bold;
            color: #5a5a5a;
            margin-bottom: 20px;
        }
        .table-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 30px;
        }
        .chart-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 30px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .form-control {
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #6a11cb;
            border-color: #6a11cb;
        }
        .btn-primary:hover {
            background-color: #2575fc;
            border-color: #2575fc;
        }
        .section-title {
            font-size: 2rem;
            color: #4c4c4c;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .lead {
            font-size: 1.2rem;
            color: #5a5a5a;
        }
        .sidebar {
            background-color: #333;
            color: white;
            height: 100vh;
        }
        .sidebar a {
            color: white;
        }
        .sidebar a:hover {
            background-color: #444;
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="mb-5">
            <h1 class="section-title">Sales Dashboard</h1>
            <p class="lead">Analyze the sales performance with an overview of total orders, revenue, top-selling categories, and products.</p>
        </div>

        <!-- Overview Section -->
        <div class="row mb-5">
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

        <!-- Date Picker Form -->
        <form method="POST" class="mb-4" id="dateForm">
            <div class="row mb-4">
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
                    <h3 class="card-header">Category Sales Distribution</h3>
                    <canvas id="categoryPieChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="card-header">Sales Trend (Last 30 Days)</h3>
                    <canvas id="salesTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Products and Sales Comparison -->
        <div class="row">
            <div class="col-md-6">
                <div class="table-container">
                    <h3 class="card-header">Top 5 Selling Products</h3>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Total Sold</th>
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
        // Category Sales Pie Chart
        const categoryLabels = <?php echo json_encode(array_column($categorySales, 'category_name')); ?>;
        const categorySalesData = <?php echo json_encode(array_column($categorySales, 'category_sales')); ?>;
        new Chart(document.getElementById('categoryPieChart'), {
            type: 'pie',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categorySalesData,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
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
            }
        });
    </script>
</body>
</html>

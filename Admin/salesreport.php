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

function getOrderStatusDistribution($connect) {
    $query = "SELECT order_status, COUNT(order_id) AS status_count 
              FROM orders 
              GROUP BY order_status";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getCustomerRegionDistribution($connect) {
    $query = "SELECT city, COUNT(user_id) AS customer_count 
              FROM user_address 
              GROUP BY city";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getMonthlySalesComparison($connect) {
    $query = "SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, SUM(final_amount) AS monthly_sales 
              FROM orders 
              WHERE order_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH) 
              GROUP BY DATE_FORMAT(order_date, '%Y-%m')
              ORDER BY month ASC";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// 数据获取
$totalOrders = getTotalOrders($connect);
$totalCustomers = getTotalCustomers($connect);
$totalSales = getTotalSales($connect);
$categorySales = getCategorySales($connect);
$orderStatusDistribution = getOrderStatusDistribution($connect);
$customerRegionDistribution = getCustomerRegionDistribution($connect);
$monthlySalesComparison = getMonthlySalesComparison($connect);
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
        /* 样式保持不变 */
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
            <p class="lead">View a comprehensive overview of your sales performance with detailed insights on orders, customers, and more.</p>
        </div>

        <!-- Overview Section -->
        <div class="row mb-4">
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

        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="card-header">Order Status Distribution</h3>
                    <div class="chart-wrapper">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="card-header">Monthly Sales Comparison</h3>
                    <div class="chart-wrapper">
                        <canvas id="monthlySalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="card-header">Customer Region Distribution</h3>
                    <div class="chart-wrapper">
                        <canvas id="customerRegionChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="card-header">Category Sales Comparison</h3>
                    <div class="chart-wrapper">
                        <canvas id="categoryBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Order Status Chart
        const orderStatusData = <?php echo json_encode(array_column($orderStatusDistribution, 'status_count')); ?>;
        const orderStatusLabels = <?php echo json_encode(array_column($orderStatusDistribution, 'order_status')); ?>;
        new Chart(document.getElementById('orderStatusChart'), {
            type: 'doughnut',
            data: {
                labels: orderStatusLabels,
                datasets: [{
                    data: orderStatusData,
                    backgroundColor: ['#36A2EB', '#FFCE56', '#FF6384', '#4BC0C0']
                }]
            }
        });

        // Customer Region Chart
        const regionData = <?php echo json_encode(array_column($customerRegionDistribution, 'customer_count')); ?>;
        const regionLabels = <?php echo json_encode(array_column($customerRegionDistribution, 'city')); ?>;
        new Chart(document.getElementById('customerRegionChart'), {
            type: 'bar',
            data: {
                labels: regionLabels,
                datasets: [{
                    label: 'Customer Count',
                    data: regionData,
                    backgroundColor: '#FF6384'
                }]
            }
        });

        // Monthly Sales Chart
        const monthlySalesData = <?php echo json_encode(array_column($monthlySalesComparison, 'monthly_sales')); ?>;
        const monthlySalesLabels = <?php echo json_encode(array_column($monthlySalesComparison, 'month')); ?>;
        new Chart(document.getElementById('monthlySalesChart'), {
            type: 'line',
            data: {
                labels: monthlySalesLabels,
                datasets: [{
                    label: 'Monthly Sales (RM)',
                    data: monthlySalesData,
                    borderColor: '#4BC0C0',
                    fill: false
                }]
            }
        });

        // Category Sales Chart
        const categoryBarData = <?php echo json_encode(array_column($categorySales, 'category_sales')); ?>;
        const categoryLabels = <?php echo json_encode(array_column($categorySales, 'category_name')); ?>;
        new Chart(document.getElementById('categoryBarChart'), {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Category Sales (RM)',
                    data: categoryBarData,
                    backgroundColor: '#FF6384'
                }]
            }
        });
    </script>
</body>
</html>

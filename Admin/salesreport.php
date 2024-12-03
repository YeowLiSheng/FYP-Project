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

function getSalesTrend($connect, $startDate, $endDate) {
    $query = "SELECT DATE(order_date) AS date, SUM(final_amount) AS daily_sales 
              FROM orders 
              WHERE DATE(order_date) BETWEEN '$startDate' AND '$endDate'
              GROUP BY DATE(order_date) 
              ORDER BY DATE(order_date)";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// 新增：获取 Top 5 客户（按消费总额排序）
function getTopCustomers($connect) {
    $query = "SELECT u.user_name, u.user_email, SUM(o.final_amount) AS total_spent 
              FROM orders o
              JOIN user u ON o.user_id = u.user_id
              GROUP BY o.user_id
              ORDER BY total_spent DESC LIMIT 5";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$startDate = isset($_POST['start_date']) ? date('Y-m-d', strtotime($_POST['start_date'])) : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_POST['end_date']) ? date('Y-m-d', strtotime($_POST['end_date'])) : date('Y-m-d');

$totalOrders = getTotalOrders($connect);
$totalCustomers = getTotalCustomers($connect);
$totalSales = getTotalSales($connect);
$categorySales = getCategorySales($connect);
$topProducts = getTopProducts($connect);
$salesTrend = getSalesTrend($connect, $startDate, $endDate);
$topCustomers = getTopCustomers($connect);

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $startDate = date('Y-m-d', strtotime($_GET['start_date']));
    $endDate = date('Y-m-d', strtotime($_GET['end_date']));
    $salesTrend = getSalesTrend($connect, $startDate, $endDate);
    echo json_encode($salesTrend);
    exit;
}

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
            background-color: #f0f4f8;
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
            transition: transform 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-10px);
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
            color: #333;
        }
        .table thead th {
            color: #333;
            font-weight: bold;
        }
        .no-data {
            text-align: center;
            font-size: 1.2rem;
            color: #aaa;
        }
        @media screen and (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
                padding-top: 20px;
            }
        }
        .chart-container {
            height: 450px; /* Increased chart height */
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="mb-4">
            <h1 class="display-4">Sales Dashboard</h1>
        </div>

        <!-- 统计卡片 -->
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

        <!-- 图表和表格 -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="card-header">Category Sales Distribution</h3>
                    <?php if (!empty($categorySales)): ?>
                    <div class="chart-wrapper">
                        <canvas id="categoryPieChart"></canvas>
                    </div>
                    <?php else: ?>
                        <div class="no-data">No data available</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
            <div class="chart-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="card-header">Sales Trend</h3>
        <div class="d-flex">
            <label for="start_date" class="me-2">Start Date:</label>
            <input type="date" id="start_date" class="form-control me-3" style="width: 150px;">
            <label for="end_date" class="me-2">End Date:</label>
            <input type="date" id="end_date" class="form-control" style="width: 150px;">
        </div>
    </div>
    <?php if (!empty($salesTrend)): ?>
        <div class="chart-wrapper">
            <canvas id="salesTrendChart"></canvas>
        </div>
    <?php else: ?>
        <div class="no-data">No sales data available</div>
    <?php endif; ?>
</div>
            </div>
        </div>

        <!-- Top 5 产品 -->
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
                            <?php if (!empty($topProducts)): ?>
                                <?php foreach ($topProducts as $product): ?>
                                    <tr>
                                        <td><?php echo $product['product_name']; ?></td>
                                        <td><?php echo $product['total_sold']; ?></td>
                                        <td>RM <?php echo number_format($product['total_revenue'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="no-data">No product data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top 5 客户 -->
            <div class="col-md-6">
                <div class="table-container">
                    <div class="card-header">Top 5 Customers by Spending</div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th>Email</th>
                                <th>Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($topCustomers)): ?>
                                <?php foreach ($topCustomers as $customer): ?>
                                    <tr>
                                        <td><?php echo $customer['user_name']; ?></td>
                                        <td><?php echo $customer['user_email']; ?></td>
                                        <td>RM <?php echo number_format($customer['total_spent'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="no-data">No customer data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Category Sales Pie Chart
        var categoryLabels = <?php echo json_encode(array_column($categorySales, 'category_name')); ?>;
        var categoryData = <?php echo json_encode(array_column($categorySales, 'category_sales')); ?>;

        var categoryPieChart = new Chart(document.getElementById('categoryPieChart'), {
            type: 'pie',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryData,
                    backgroundColor: ['#ff6384', '#36a2eb', '#cc65fe', '#ffce56'],
                }]
            }
        });

        // Sales Trend Line Chart
        var trendLabels = <?php echo json_encode(array_column($salesTrend, 'date')); ?>;
        var trendData = <?php echo json_encode(array_column($salesTrend, 'daily_sales')); ?>;

        var salesTrendChart = new Chart(document.getElementById('salesTrendChart'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Sales Trend',
                    data: trendData,
                    fill: true,
                    borderColor: '#2575fc',
                    backgroundColor: 'rgba(37, 117, 252, 0.2)',
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 5,
                    pointBackgroundColor: '#2575fc',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    hoverBackgroundColor: '#2575fc',
                    hoverBorderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Sales (RM)'
                        },
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#2575fc',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#2575fc',
                        borderWidth: 1
                    }
                }
            }
        });


        document.addEventListener('DOMContentLoaded', function () {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const salesTrendChartElement = document.getElementById('salesTrendChart');
    let salesTrendChartInstance;

    function updateSalesTrendChart(startDate, endDate) {
        fetch(`salesreport.php?start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                const trendLabels = data.map(item => item.date);
                const trendData = data.map(item => parseFloat(item.daily_sales));

                if (salesTrendChartInstance) {
                    salesTrendChartInstance.destroy();
                }

                salesTrendChartInstance = new Chart(salesTrendChartElement, {
                    type: 'line',
                    data: {
                        labels: trendLabels,
                        datasets: [{
                            label: 'Sales Trend',
                            data: trendData,
                            fill: true,
                            borderColor: '#2575fc',
                            backgroundColor: 'rgba(37, 117, 252, 0.2)',
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 5,
                            pointBackgroundColor: '#2575fc',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            hoverBackgroundColor: '#2575fc',
                            hoverBorderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Date'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Sales (RM)'
                                },
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#2575fc',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                borderColor: '#2575fc',
                                borderWidth: 1
                            }
                        }
                    }
                });
            });
    }

    function handleDateChange() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;

        if (startDate && endDate && new Date(startDate) <= new Date(endDate)) {
            updateSalesTrendChart(startDate, endDate);
        }
    }

    startDateInput.addEventListener('change', handleDateChange);
    endDateInput.addEventListener('change', handleDateChange);

    // Load initial data
    const defaultStartDate = new Date();
    defaultStartDate.setDate(defaultStartDate.getDate() - 30);
    startDateInput.value = defaultStartDate.toISOString().split('T')[0];
    endDateInput.value = new Date().toISOString().split('T')[0];
    updateSalesTrendChart(startDateInput.value, endDateInput.value);
});
    </script>
</body>
</html>

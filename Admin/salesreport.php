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

$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

$totalOrders = getTotalOrders($connect);
$totalCustomers = getTotalCustomers($connect);
$totalSales = getTotalSales($connect);
$categorySales = getCategorySales($connect);
$topProducts = getTopProducts($connect);
$salesTrend = getSalesTrend($connect, $startDate, $endDate);
$topCustomers = getTopCustomers($connect);
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
            flex-direction: column;
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
        .date-picker-container {
            margin-bottom: 20px;
        }
        .date-picker input {
            border-radius: 10px;
            padding: 5px 10px;
            border: 1px solid #ddd;
            margin: 0 5px;
        }
        .date-picker input:focus {
            border-color: #2575fc;
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
            <!-- ... 已省略的卡片内容 ... -->
        </div>

        <!-- 图表和表格 -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="card-header">Sales Trend (Dynamic Date Range)</h3>
                    <div class="date-picker-container">
                        <div class="date-picker">
                            <input type="date" id="start_date" value="<?php echo $startDate; ?>">
                            <input type="date" id="end_date" value="<?php echo $endDate; ?>">
                        </div>
                    </div>
                    <div id="salesTrendChartContainer">
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
            <!-- ... 其他内容省略 ... -->
        </div>
    </div>

    <script>
        // 更新销售趋势数据
        function updateSalesTrend() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (startDate && endDate) {
                window.location.href = `salesreport.php?start_date=${startDate}&end_date=${endDate}`;
            }
        }

        // 日期选择器事件监听
        document.getElementById('start_date').addEventListener('change', updateSalesTrend);
        document.getElementById('end_date').addEventListener('change', updateSalesTrend);

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
                        title: { display: true, text: 'Date' },
                        grid: { color: '#e5e5e5' }
                    },
                    y: {
                        title: { display: true, text: 'Sales' },
                        grid: { color: '#e5e5e5' }
                    }
                },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { enabled: true }
                }
            }
        });
    </script>
</body>
</html>

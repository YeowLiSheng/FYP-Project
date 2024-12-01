<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

// Database Queries
function getMonthlySales($connect) {
    $query = "SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, SUM(final_amount) AS monthly_sales 
              FROM orders 
              GROUP BY month 
              ORDER BY month";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getDailySales($connect) {
    $query = "SELECT DATE(order_date) AS date, SUM(final_amount) AS daily_sales 
              FROM orders 
              WHERE DATE(order_date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
              GROUP BY date 
              ORDER BY date";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getCategoryProfit($connect) {
    $query = "SELECT c.category_name, SUM(od.total_price) - SUM(p.cost_price * od.quantity) AS profit 
              FROM order_details od 
              JOIN product p ON od.product_id = p.product_id 
              JOIN category c ON p.category_id = c.category_id 
              GROUP BY c.category_name";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getLowSellingProducts($connect) {
    $query = "SELECT p.product_name, SUM(od.quantity) AS total_sold 
              FROM order_details od 
              JOIN product p ON od.product_id = p.product_id 
              GROUP BY p.product_id 
              ORDER BY total_sold ASC 
              LIMIT 5";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getGrowthComparison($connect) {
    $currentMonthQuery = "SELECT SUM(final_amount) AS current_month_sales 
                          FROM orders 
                          WHERE MONTH(order_date) = MONTH(CURRENT_DATE()) 
                          AND YEAR(order_date) = YEAR(CURRENT_DATE())";
    $lastMonthQuery = "SELECT SUM(final_amount) AS last_month_sales 
                       FROM orders 
                       WHERE MONTH(order_date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) 
                       AND YEAR(order_date) = YEAR(CURRENT_DATE())";
    $currentYearQuery = "SELECT SUM(final_amount) AS current_year_sales 
                         FROM orders 
                         WHERE YEAR(order_date) = YEAR(CURRENT_DATE())";
    $lastYearQuery = "SELECT SUM(final_amount) AS last_year_sales 
                      FROM orders 
                      WHERE YEAR(order_date) = YEAR(CURRENT_DATE() - INTERVAL 1 YEAR)";
    
    $currentMonth = mysqli_fetch_assoc(mysqli_query($connect, $currentMonthQuery))['current_month_sales'];
    $lastMonth = mysqli_fetch_assoc(mysqli_query($connect, $lastMonthQuery))['last_month_sales'];
    $currentYear = mysqli_fetch_assoc(mysqli_query($connect, $currentYearQuery))['current_year_sales'];
    $lastYear = mysqli_fetch_assoc(mysqli_query($connect, $lastYearQuery))['last_year_sales'];

    return [
        'month_growth' => $lastMonth ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : null,
        'year_growth' => $lastYear ? (($currentYear - $lastYear) / $lastYear) * 100 : null
    ];
}

// Data Fetching
$monthlySales = getMonthlySales($connect);
$dailySales = getDailySales($connect);
$categoryProfit = getCategoryProfit($connect);
$lowSellingProducts = getLowSellingProducts($connect);
$growthComparison = getGrowthComparison($connect);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Sales Report</title>
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
        .chart-container {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .table-container {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <h1 class="mb-4">Enhanced Sales Report</h1>

        <!-- Growth Comparison -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h5>Month-on-Month Growth</h5>
                    <h2><?php echo number_format($growthComparison['month_growth'], 2); ?>%</h2>
                </div>
            </div>
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h5>Year-on-Year Growth</h5>
                    <h2><?php echo number_format($growthComparison['year_growth'], 2); ?>%</h2>
                </div>
            </div>
        </div>

        <!-- Sales Charts -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <h3>Monthly Sales Trend</h3>
                    <canvas id="monthlySalesChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h3>Daily Sales (Last 30 Days)</h3>
                    <canvas id="dailySalesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Category Profit Table -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="table-container">
                    <h3>Category Profit</h3>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Profit (RM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categoryProfit as $category): ?>
                                <tr>
                                    <td><?php echo $category['category_name']; ?></td>
                                    <td><?php echo number_format($category['profit'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Low Selling Products Table -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="table-container">
                    <h3>Low Selling Products</h3>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Total Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowSellingProducts as $product): ?>
                                <tr>
                                    <td><?php echo $product['product_name']; ?></td>
                                    <td><?php echo $product['total_sold']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Monthly Sales Chart
        const monthlySales = <?php echo json_encode(array_column($monthlySales, 'monthly_sales')); ?>;
        const monthlyLabels = <?php echo json_encode(array_column($monthlySales, 'month')); ?>;
        new Chart(document.getElementById('monthlySalesChart'), {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Monthly Sales (RM)',
                    data: monthlySales,
                    backgroundColor: '#6a11cb'
                }]
            }
        });

        // Daily Sales Chart
        const dailySales = <?php echo json_encode(array_column($dailySales, 'daily_sales')); ?>;
        const dailyLabels = <?php echo json_encode(array_column($dailySales, 'date')); ?>;
        new Chart(document.getElementById('dailySalesChart'), {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Daily Sales (RM)',
                    data: dailySales,
                    borderColor: '#2575fc',
                    fill: false
                }]
            }
        });
    </script>
</body>
</html>

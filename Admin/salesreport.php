<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

// Default date range
$startDate = date('Y-m-d', strtotime('-30 days'));
$endDate = date('Y-m-d');

// Check if dates are submitted via POST
if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
}

// Fetch total orders, sales, customers, products sold
$order_query = "SELECT COUNT(*) AS order_count FROM `orders`";
$order_result = $connect->query($order_query);
$order_count = $order_result->num_rows > 0 ? $order_result->fetch_assoc()['order_count'] : 0;

$totalSales_query = "SELECT SUM(final_amount) AS total_sales FROM orders";
$totalSales_result = $connect->query($totalSales_query);
$totalSales = $totalSales_result->num_rows > 0 ? $totalSales_result->fetch_assoc()['total_sales'] : 0;

$totalCustomers_query = "SELECT COUNT(DISTINCT user_id) AS total_customers FROM orders";
$totalCustomers_result = $connect->query($totalCustomers_query);
$total_customers = $totalCustomers_result->num_rows > 0 ? $totalCustomers_result->fetch_assoc()['total_customers'] : 0;

$totalProducts_query = "SELECT SUM(quantity) AS total_products_sold FROM order_details";
$totalProducts_result = $connect->query($totalProducts_query);
$total_products_sold = $totalProducts_result->num_rows > 0 ? $totalProducts_result->fetch_assoc()['total_products_sold'] : 0;

$salesTrend_query = "SELECT DATE(order_date) AS date, SUM(final_amount) AS daily_sales 
                      FROM orders 
                      WHERE DATE(order_date) BETWEEN '$startDate' AND '$endDate' 
                      GROUP BY DATE(order_date) 
                      ORDER BY DATE(order_date)";
$salesTrend_result = $connect->query($salesTrend_query);
$salesTrend = $salesTrend_result->fetch_all(MYSQLI_ASSOC);

// Function to get sales data for monthly chart
function getMonthlySales($connect) {
    $query = "SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, SUM(final_amount) AS monthly_sales
              FROM orders
              GROUP BY month
              ORDER BY month";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Function to get sales data for yearly chart
function getYearlySales($connect) {
    $query = "SELECT YEAR(order_date) AS year, SUM(final_amount) AS yearly_sales
              FROM orders
              GROUP BY year
              ORDER BY year";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Function to get category sales data for pie chart
function getCategorySales($connect) {
    $query = "SELECT c.category_name, SUM(od.total_price) AS category_sales
              FROM order_details od
              JOIN product p ON od.product_id = p.product_id
              JOIN category c ON p.category_id = c.category_id
              GROUP BY c.category_name";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$monthlySales = getMonthlySales($connect);
$yearlySales = getYearlySales($connect);
$categorySales = getCategorySales($connect);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sales Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function updateEndDateLimit() {
            const startDate = document.getElementById('start_date').value;
            const endDateInput = document.getElementById('end_date');
            endDateInput.setAttribute('min', startDate);
        }

        function submitDateForm() {
            document.getElementById('dateForm').submit();
        }

        function showChart(chartType) {
            document.getElementById('salesTrendChart').style.display = (chartType === 'salesTrend') ? 'block' : 'none';
            document.getElementById('monthlySalesChart').style.display = (chartType === 'monthlySales') ? 'block' : 'none';
            document.getElementById('yearlySalesChart').style.display = (chartType === 'yearlySales') ? 'block' : 'none';
        }
    </script>
    <style>
        .container {
            margin-top: 80px;
        }
        .cards {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }
        .ccard {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            flex: 1;
            margin: 0 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        .ccard .icon {
            font-size: 36px;
            color: #6c757d;
        }
        .ccard .number {
            font-size: 24px;
            font-weight: 700;
            margin: 10px 0;
        }
        .ccard .name {
            font-size: 16px;
            color: #6c757d;
        }
        .select-container {
            margin-top: 20px;
            text-align: center;
        }
        .chart-container {
            margin-top: 40px;
            display: none;
        }
        .chart-container h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        .chart-container canvas {
            width: 100% !important;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Summary Cards -->
    <div class="cards">
        <div class="ccard">
            <i class="fas fa-shopping-cart icon"></i>
            <p class="number"><?php echo $order_count; ?></p>
            <p class="name">Orders</p>
        </div>
        <div class="ccard">
            <i class="fas fa-users icon"></i>
            <p class="number"><?php echo $total_customers; ?></p>
            <p class="name">Customers</p>
        </div>
        <div class="ccard">
            <i class="fas fa-dollar-sign icon"></i>
            <p class="number">RM<?php echo number_format($totalSales, 2); ?></p>
            <p class="name">Total Sales</p>
        </div>
        <div class="ccard">
            <i class="fas fa-tags icon"></i>
            <p class="number"><?php echo $total_products_sold; ?></p>
            <p class="name">Total Products Sold</p>
        </div>
    </div>

    <!-- Date Range Filter -->
    <form method="POST" id="dateForm">
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo $startDate; ?>" onchange="updateEndDateLimit(); submitDateForm();">
            </div>
            <div class="col-auto">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo $endDate; ?>" min="<?php echo $startDate; ?>" onchange="submitDateForm();">
            </div>
        </div>
    </form>

    <!-- Chart Type Selector -->
    <div class="select-container">
        <label for="chartType" class="form-label">Select Chart</label>
        <select id="chartType" class="form-select" onchange="showChart(this.value)">
            <option value="salesTrend">Sales Trend</option>
            <option value="monthlySales">Monthly Sales</option>
            <option value="yearlySales">Yearly Sales</option>
        </select>
    </div>

    <!-- Sales Trend Chart -->
    <div class="chart-container" id="salesTrendChart">
        <h3>Sales Trend</h3>
        <canvas id="salesTrendChartCanvas"></canvas>
    </div>

    <!-- Monthly Sales Chart -->
    <div class="chart-container" id="monthlySalesChart">
        <h3>Monthly Sales</h3>
        <canvas id="monthlySalesChartCanvas"></canvas>
    </div>

    <!-- Yearly Sales Chart -->
    <div class="chart-container" id="yearlySalesChart">
        <h3>Yearly Sales</h3>
        <canvas id="yearlySalesChartCanvas"></canvas>
    </div>
</div>

<script>
    // Sales Trend Chart
    const salesTrendData = <?php echo json_encode($salesTrend); ?>;
    const dates = salesTrendData.map(item => item.date);
    const sales = salesTrendData.map(item => parseFloat(item.daily_sales));

    new Chart(document.getElementById('salesTrendChartCanvas').getContext('2d'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Daily Sales (RM)',
                data: sales,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { title: { display: true, text: 'Date' } },
                y: { title: { display: true, text: 'Sales (RM)' }, beginAtZero: true }
            }
        }
    });

    // Monthly Sales Chart
    new Chart(document.getElementById('monthlySalesChartCanvas').getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($monthlySales, 'month')); ?>,
            datasets: [{
                label: 'Monthly Sales (RM)',
                data: <?php echo json_encode(array_column($monthlySales, 'monthly_sales')); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });

    // Yearly Sales Chart
    new Chart(document.getElementById('yearlySalesChartCanvas').getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($yearlySales, 'year')); ?>,
            datasets: [{
                label: 'Yearly Sales (RM)',
                data: <?php echo json_encode(array_column($yearlySales, 'yearly_sales')); ?>,
                backgroundColor: 'rgba(153, 102, 255, 0.6)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
</body>
</html>

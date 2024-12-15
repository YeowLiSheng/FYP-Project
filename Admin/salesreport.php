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

// Fetch total orders, sales, customers, and products sold
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

// Fetch sales trend data for dynamic chart
$salesTrend_query = "SELECT DATE(order_date) AS date, SUM(final_amount) AS daily_sales 
                      FROM orders 
                      WHERE DATE(order_date) BETWEEN '$startDate' AND '$endDate' 
                      GROUP BY DATE(order_date) 
                      ORDER BY DATE(order_date)";
$salesTrend_result = $connect->query($salesTrend_query);
$salesTrend = $salesTrend_result->fetch_all(MYSQLI_ASSOC);

// Fetch monthly and yearly sales data
function getMonthlySales($connect) {
    $query = "SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, SUM(final_amount) AS monthly_sales
              FROM orders
              GROUP BY month
              ORDER BY month";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getYearlySales($connect) {
    $query = "SELECT YEAR(order_date) AS year, SUM(final_amount) AS yearly_sales
              FROM orders
              GROUP BY year
              ORDER BY year";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$monthlySales = getMonthlySales($connect);
$yearlySales = getYearlySales($connect);
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
        .chart-container {
            margin-top: 40px;
        }
        .chart-container canvas {
            max-width: 100%;
            height: 300px;
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
    <div class="mb-3">
        <label for="chartType" class="form-label">Select Chart Type</label>
        <select id="chartType" class="form-select" onchange="changeChartType(this)">
            <option value="salesTrend">Sales Trend</option>
            <option value="monthlySales">Monthly Sales</option>
            <option value="yearlySales">Yearly Sales</option>
        </select>
    </div>

    <!-- Sales Trend Chart -->
    <div class="chart-container" id="salesTrendChartContainer">
        <canvas id="salesTrendChart"></canvas>
    </div>

    <!-- Monthly Sales Chart -->
    <div class="chart-container" id="monthlySalesChartContainer" style="display:none;">
        <canvas id="monthlySalesChart"></canvas>
    </div>

    <!-- Yearly Sales Chart -->
    <div class="chart-container" id="yearlySalesChartContainer" style="display:none;">
        <canvas id="yearlySalesChart"></canvas>
    </div>
</div>

<script>
    // Function to change the chart type based on the selection
    function changeChartType(select) {
        const chartType = select.value;
        
        // Hide all chart containers
        document.getElementById('salesTrendChartContainer').style.display = 'none';
        document.getElementById('monthlySalesChartContainer').style.display = 'none';
        document.getElementById('yearlySalesChartContainer').style.display = 'none';
        
        // Show selected chart container
        if (chartType === 'salesTrend') {
            document.getElementById('salesTrendChartContainer').style.display = 'block';
        } else if (chartType === 'monthlySales') {
            document.getElementById('monthlySalesChartContainer').style.display = 'block';
        } else if (chartType === 'yearlySales') {
            document.getElementById('yearlySalesChartContainer').style.display = 'block';
        }
    }

    // Initialize the Sales Trend Chart (default)
    const salesTrendData = <?php echo json_encode($salesTrend); ?>;
    const salesTrendDates = salesTrendData.map(item => item.date);
    const salesTrendSales = salesTrendData.map(item => parseFloat(item.daily_sales));

    const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
   // Retrieve PHP data
   const salesTrendData = <?php echo json_encode($salesTrend); ?>;

// Extract dates and sales values
const dates = salesTrendData.map(item => item.date);
const sales = salesTrendData.map(item => parseFloat(item.daily_sales));

// Configure Chart.js
const ctx = document.getElementById('salesTrendChart').getContext('2d');
new Chart(ctx, {
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
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
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
        }
    }
});

    function processMonthlyData(monthlyData) {
    const maxMonths = 5;
    // If there are less than 5 months of data, fill with 5 months
    if (monthlyData.length < maxMonths) {
        const missingMonths = maxMonths - monthlyData.length;
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth(); // 0-based, so January is 0
        
        // Fill in missing months with empty data
        for (let i = 0; i < missingMonths; i++) {
            const month = (currentMonth - i + 12) % 12; // Calculate the previous month
            const monthName = new Date(currentDate.setMonth(month)).toLocaleString('default', { month: 'short' });
            monthlyData.unshift({ month: monthName, monthly_sales: 0 });
        }
    }

    return monthlyData.slice(-maxMonths); // Show the last 5 months if there are more than 5
}

// Function to process the yearly sales data
function processYearlyData(yearlyData) {
    const maxYears = 5;
    // If there are less than 5 years of data, fill with 5 years
    if (yearlyData.length < maxYears) {
        const missingYears = maxYears - yearlyData.length;
        const currentYear = new Date().getFullYear();
        
        // Fill in missing years with empty data
        for (let i = 0; i < missingYears; i++) {
            const year = currentYear - i - 1; // Calculate the previous year
            yearlyData.unshift({ year: year, yearly_sales: 0 });
        }
    }

    return yearlyData.slice(-maxYears); // Show the last 5 years if there are more than 5
}

// Initialize the Monthly Sales Chart with processed data
const monthlySalesData = processMonthlyData(<?php echo json_encode($monthlySales); ?>);
const monthlySalesLabels = monthlySalesData.map(item => item.month);
const monthlySalesValues = monthlySalesData.map(item => parseFloat(item.monthly_sales));

const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
new Chart(monthlySalesCtx, {
    type: 'bar',
    data: {
        labels: monthlySalesLabels,
        datasets: [{
            label: 'Monthly Sales (RM)',
            data: monthlySalesValues,
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Initialize the Yearly Sales Chart with processed data
const yearlySalesData = processYearlyData(<?php echo json_encode($yearlySales); ?>);
const yearlySalesLabels = yearlySalesData.map(item => item.year);
const yearlySalesValues = yearlySalesData.map(item => parseFloat(item.yearly_sales));

const yearlySalesCtx = document.getElementById('yearlySalesChart').getContext('2d');
new Chart(yearlySalesCtx, {
    type: 'bar',
    data: {
        labels: yearlySalesLabels,
        datasets: [{
            label: 'Yearly Sales (RM)',
            data: yearlySalesValues,
            backgroundColor: 'rgba(153, 102, 255, 0.6)',
            borderColor: 'rgba(153, 102, 255, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
</body>
</html>


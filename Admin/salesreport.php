<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

// Default date range for sales trend
$startDate = date('Y-m-d', strtotime('-30 days'));
$endDate = date('Y-m-d');

// Check if dates or view mode are submitted via POST
$viewMode = isset($_POST['view_mode']) ? $_POST['view_mode'] : 'sales_trend';
if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
}

// Check for selected year in monthly sales view
$selectedYear = isset($_POST['selected_year']) ? $_POST['selected_year'] : date('Y');

// Fetch total orders
$order_query = "SELECT COUNT(*) AS order_count FROM `orders`";
$order_result = $connect->query($order_query);
$order_count = $order_result->num_rows > 0 ? $order_result->fetch_assoc()['order_count'] : 0;

// Fetch total sales
$totalSales_query = "SELECT SUM(final_amount) AS total_sales FROM orders";
$totalSales_result = $connect->query($totalSales_query);
$totalSales = $totalSales_result->num_rows > 0 ? $totalSales_result->fetch_assoc()['total_sales'] : 0;

// Fetch total customers
$totalCustomers_query = "SELECT COUNT(DISTINCT user_id) AS total_customers FROM orders";
$totalCustomers_result = $connect->query($totalCustomers_query);
$total_customers = $totalCustomers_result->num_rows > 0 ? $totalCustomers_result->fetch_assoc()['total_customers'] : 0;

// Fetch total products sold
$totalProducts_query = "SELECT SUM(quantity) AS total_products_sold FROM order_details";
$totalProducts_result = $connect->query($totalProducts_query);
$total_products_sold = $totalProducts_result->num_rows > 0 ? $totalProducts_result->fetch_assoc()['total_products_sold'] : 0;

// Fetch sales trend data
$salesTrend_query = "SELECT DATE(order_date) AS date, SUM(final_amount) AS daily_sales 
                      FROM orders 
                      WHERE DATE(order_date) BETWEEN '$startDate' AND '$endDate' 
                      GROUP BY DATE(order_date) 
                      ORDER BY DATE(order_date)";
$salesTrend_result = $connect->query($salesTrend_query);
$salesTrend = $salesTrend_result->fetch_all(MYSQLI_ASSOC);

// Fetch monthly sales data
if ($viewMode === 'monthly_sales') {
    $monthlySales_query = "
        SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, SUM(final_amount) AS monthly_sales 
        FROM orders 
        WHERE YEAR(order_date) = '$selectedYear'
        GROUP BY DATE_FORMAT(order_date, '%Y-%m') 
        ORDER BY DATE_FORMAT(order_date, '%Y-%m') ASC";
    $monthlySales_result = $connect->query($monthlySales_query);
    $monthlySales = $monthlySales_result->fetch_all(MYSQLI_ASSOC);

    // Fill empty months
    for ($i = 1; $i <= 12; $i++) {
        $month = sprintf('%s-%02d', $selectedYear, $i);
        $exists = array_filter($monthlySales, fn($data) => $data['month'] === $month);
        if (empty($exists)) {
            $monthlySales[] = ['month' => $month, 'monthly_sales' => 0];
        }
    }
    usort($monthlySales, fn($a, $b) => strcmp($a['month'], $b['month']));
}

// Fetch yearly sales data
$yearlySales_query = "
    SELECT YEAR(order_date) AS year, SUM(final_amount) AS yearly_sales 
    FROM orders 
    GROUP BY YEAR(order_date) 
    ORDER BY YEAR(order_date) DESC";
$yearlySales_result = $connect->query($yearlySales_query);
$yearlySales = $yearlySales_result->fetch_all(MYSQLI_ASSOC);

// Fill yearly sales data if less than 6 years
if (count($yearlySales) < 6) {
    $currentYear = date('Y');
    for ($i = 5; $i >= 0; $i--) {
        $year = $currentYear - $i;
        $exists = array_filter($yearlySales, fn($data) => $data['year'] == $year);
        if (empty($exists)) {
            $yearlySales[] = ['year' => $year, 'yearly_sales' => 0];
        }
    }
    usort($yearlySales, fn($a, $b) => $a['year'] - $b['year']);
}
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

        function updateViewMode() {
            document.getElementById('viewForm').submit();
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
        #chartContainer {
            margin-top: 40px;
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

    <!-- View Mode Selector -->
    <form method="POST" id="viewForm">
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                <label for="view_mode" class="form-label">View Mode</label>
                <select id="view_mode" name="view_mode" class="form-select" onchange="updateViewMode();">
                    <option value="sales_trend" <?php if ($viewMode === 'sales_trend') echo 'selected'; ?>>Sales Trend</option>
                    <option value="monthly_sales" <?php if ($viewMode === 'monthly_sales') echo 'selected'; ?>>Monthly Sales</option>
                    <option value="yearly_sales" <?php if ($viewMode === 'yearly_sales') echo 'selected'; ?>>Yearly Sales</option>
                </select>
            </div>
        </div>


    <!-- Year Selector (Visible Only for Monthly Sales) -->
    <div class="row g-3 align-items-center" id="yearSelector" style="display: <?php echo ($viewMode === 'monthly_sales') ? 'block' : 'none'; ?>;">
        <div class="col-auto">
            <label for="selected_year" class="form-label">Select Year</label>
            <select id="selected_year" name="selected_year" class="form-select" onchange="updateViewMode();">
                <?php
                $currentYear = date('Y');
                for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                    echo "<option value='$i'" . (isset($_POST['selected_year']) && $_POST['selected_year'] == $i ? ' selected' : '') . ">$i</option>";
                }
                ?>
            </select>
        </div>
    </div>
    </form>

    <!-- Sales Trend Chart -->
    <div id="chartContainer">
        <canvas id="salesChart"></canvas>
    </div>
</div>

<script>
    // Retrieve PHP data
    const viewMode = '<?php echo $viewMode; ?>';
    let chartData;

    if (viewMode === 'sales_trend') {
        chartData = <?php echo json_encode($salesTrend); ?>;
        const dates = chartData.map(item => item.date);
        const sales = chartData.map(item => parseFloat(item.daily_sales));

        createLineChart('Daily Sales (RM)', dates, sales);

    } else if (viewMode === 'monthly_sales') {
    chartData = <?php echo json_encode($monthlySales); ?>;
    const months = chartData.map(item => item.month);
    const sales = chartData.map(item => parseFloat(item.monthly_sales));

    createBarChart('Monthly Sales (RM)', months, sales);

} else if (viewMode === 'yearly_sales') {
    chartData = <?php echo json_encode($yearlySales); ?>;
    const years = chartData.map(item => item.year);
    const sales = chartData.map(item => parseFloat(item.yearly_sales));

    createBarChart('Yearly Sales (RM)', years, sales);
}

    function createLineChart(label, labels, data) {
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: data,
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
                    x: { title: { display: true, text: 'Date' } },
                    y: { title: { display: true, text: 'Sales (RM)' }, beginAtZero: true }
                }
            }
        });
    }

    function createBarChart(label, labels, data) {
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: data,
                    backgroundColor: '#007bff',
                    borderColor: '#0056b3',
                    borderWidth: 1
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
                    x: { title: { display: true, text: 'Date' } },
                    y: { title: { display: true, text: 'Sales (RM)' }, beginAtZero: true }
                }
            }
        });
    }
    function updateViewMode() {
    document.getElementById('yearSelector').style.display = document.getElementById('view_mode').value === 'monthly_sales' ? 'block' : 'none';
    document.getElementById('viewForm').submit();
}
</script>
</body>
</html>

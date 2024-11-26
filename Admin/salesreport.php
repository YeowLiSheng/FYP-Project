<?php
include 'dataconnection.php'; // Database connection
include 'admin_sidebar.php';  // Admin sidebar for navigation

// Fetch total sales data by product
$sales_query = "
    SELECT p.product_name, SUM(od.total_price) as total_sales
    FROM order_details od
    JOIN product p ON od.product_id = p.product_id
    GROUP BY p.product_name
    ORDER BY total_sales DESC
";
$sales_result = mysqli_query($connect, $sales_query);

// Fetch total revenue by month (for the chart)
$monthly_sales_query = "
    SELECT DATE_FORMAT(o.order_date, '%Y-%m') AS month, SUM(o.final_amount) AS revenue
    FROM orders o
    WHERE o.order_status = 'Complete'
    GROUP BY month
    ORDER BY month DESC
";
$monthly_sales_result = mysqli_query($connect, $monthly_sales_query);
$monthly_sales = [];
while ($row = mysqli_fetch_assoc($monthly_sales_result)) {
    $monthly_sales[] = $row;
}

// Prepare data for the chart
$months = array_map(function($row) { return $row['month']; }, $monthly_sales);
$revenue = array_map(function($row) { return $row['revenue']; }, $monthly_sales);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container {
            margin-top: 50px;
        }
        .chart-container {
            width: 80%;
            margin: auto;
            padding: 20px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
        }
        .table-container {
            margin-top: 30px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="chart-container">
        <div class="card">
            <div class="card-header">
                <h5>Sales Revenue by Month</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>
    </div>

    <div class="table-container">
        <div class="card">
            <div class="card-header">
                <h5>Total Sales by Product</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Total Sales (RM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($sales_result)): ?>
                            <tr>
                                <td><?php echo $row['product_name']; ?></td>
                                <td><?php echo number_format($row['total_sales'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Monthly Sales Chart
    var ctx = document.getElementById('monthlySalesChart').getContext('2d');
    var monthlySalesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Revenue (RM)',
                data: <?php echo json_encode($revenue); ?>,
                borderColor: 'rgba(75, 192, 192, 1)',
                fill: false,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Total Revenue by Month'
                }
            }
        });
</script>

</body>
</html>

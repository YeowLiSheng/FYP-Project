<?php
// Include database connection and sidebar
include 'dataconnection.php'; 
include 'admin_sidebar.php'; 

// Get the current date or a range of dates for filtering sales
$dateFrom = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01'); // Default to the start of the current month
$dateTo = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d'); // Default to today

// Query to get total sales, total orders, and total customers
$query_total_sales = "SELECT SUM(final_amount) AS total_sales, COUNT(DISTINCT order_id) AS total_orders, COUNT(DISTINCT user_id) AS total_customers 
                      FROM orders WHERE order_date BETWEEN '$dateFrom' AND '$dateTo'";
$result_sales = mysqli_query($connect, $query_total_sales);
$data_sales = mysqli_fetch_assoc($result_sales);

// Query to get category-wise sales for the pie chart
$query_category_sales = "SELECT c.category_name, SUM(od.total_price) AS category_sales 
                         FROM order_details od 
                         JOIN product p ON od.product_id = p.product_id 
                         JOIN category c ON p.category_id = c.category_id
                         WHERE od.order_id IN (SELECT order_id FROM orders WHERE order_date BETWEEN '$dateFrom' AND '$dateTo')
                         GROUP BY c.category_name";
$result_category_sales = mysqli_query($connect, $query_category_sales);

// Query for top 5 best-selling products
$query_top_products = "SELECT od.product_name, SUM(od.quantity) AS total_sold 
                       FROM order_details od
                       JOIN orders o ON od.order_id = o.order_id
                       WHERE o.order_date BETWEEN '$dateFrom' AND '$dateTo'
                       GROUP BY od.product_name
                       ORDER BY total_sold DESC LIMIT 5";
$result_top_products = mysqli_query($connect, $query_top_products);

// Query to get sales data for the line chart (by date)
$query_sales_by_date = "SELECT DATE(order_date) AS sale_date, SUM(final_amount) AS daily_sales
                        FROM orders
                        WHERE order_date BETWEEN '$dateFrom' AND '$dateTo'
                        GROUP BY sale_date ORDER BY sale_date";
$result_sales_by_date = mysqli_query($connect, $query_sales_by_date);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Include Bootstrap for styling -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Sales Report</h2>
        
        <!-- Filter Form -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <input type="date" name="from_date" class="form-control" value="<?= $dateFrom ?>">
                </div>
                <div class="col-md-3">
                    <input type="date" name="to_date" class="form-control" value="<?= $dateTo ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <!-- Total Stats -->
        <div class="row mb-5">
            <div class="col-md-4">
                <div class="card p-3">
                    <h5>Total Orders</h5>
                    <p><?= $data_sales['total_orders'] ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3">
                    <h5>Total Customers</h5>
                    <p><?= $data_sales['total_customers'] ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3">
                    <h5>Total Sales</h5>
                    <p>$<?= number_format($data_sales['total_sales'], 2) ?></p>
                </div>
            </div>
        </div>

        <!-- Pie Chart for Category Sales -->
        <div class="card mb-4">
            <div class="card-header">Sales by Category</div>
            <div class="card-body">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Top 5 Products -->
        <div class="card mb-4">
            <div class="card-header">Top 5 Best-Selling Products</div>
            <div class="card-body">
                <ul class="list-group">
                    <?php while ($row = mysqli_fetch_assoc($result_top_products)): ?>
                        <li class="list-group-item">
                            <?= $row['product_name'] ?> - <?= $row['total_sold'] ?> sold
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>

        <!-- Line Chart for Daily Sales -->
        <div class="card mb-4">
            <div class="card-header">Sales Trend</div>
            <div class="card-body">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Pie chart for category sales
        var ctx1 = document.getElementById('categoryChart').getContext('2d');
        var categoryChart = new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: [<?php while($row = mysqli_fetch_assoc($result_category_sales)) { echo "'".$row['category_name']."', "; } ?>],
                datasets: [{
                    data: [<?php mysqli_data_seek($result_category_sales, 0); while($row = mysqli_fetch_assoc($result_category_sales)) { echo $row['category_sales'].", "; } ?>],
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            }
        });

        // Line chart for sales trend
        var ctx2 = document.getElementById('salesTrendChart').getContext('2d');
        var salesTrendChart = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: [<?php while ($row = mysqli_fetch_assoc($result_sales_by_date)) { echo "'".$row['sale_date']."', "; } ?>],
                datasets: [{
                    label: 'Sales',
                    data: [<?php mysqli_data_seek($result_sales_by_date, 0); while ($row = mysqli_fetch_assoc($result_sales_by_date)) { echo $row['daily_sales'].", "; } ?>],
                    borderColor: '#4BC0C0',
                    fill: false,
                    tension: 0.1
                }]
            }
        });
    </script>
</body>
</html>

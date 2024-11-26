<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

// 获取总订单、总客户、总销售额和分类数据
$totalOrdersQuery = "SELECT COUNT(order_id) AS total_orders FROM orders";
$totalOrdersResult = mysqli_query($connect, $totalOrdersQuery);
$totalOrders = mysqli_fetch_assoc($totalOrdersResult)['total_orders'];

$totalCustomersQuery = "SELECT COUNT(DISTINCT user_id) AS total_customers FROM orders";
$totalCustomersResult = mysqli_query($connect, $totalCustomersQuery);
$totalCustomers = mysqli_fetch_assoc($totalCustomersResult)['total_customers'];

$totalSalesQuery = "SELECT SUM(final_amount) AS total_sales FROM orders";
$totalSalesResult = mysqli_query($connect, $totalSalesQuery);
$totalSales = mysqli_fetch_assoc($totalSalesResult)['total_sales'];

$categorySalesQuery = "SELECT c.category_name, SUM(od.total_price) AS category_sales 
                       FROM order_details od 
                       JOIN product p ON od.product_id = p.product_id 
                       JOIN category c ON p.category_id = c.category_id 
                       GROUP BY c.category_name";
$categorySalesResult = mysqli_query($connect, $categorySalesQuery);

$topProductsQuery = "SELECT product_name, SUM(quantity) AS total_sold, SUM(total_price) AS total_revenue 
                     FROM order_details 
                     GROUP BY product_name 
                     ORDER BY total_sold DESC LIMIT 5";
$topProductsResult = mysqli_query($connect, $topProductsQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .dashboard-card {
            color: #fff;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 15px;
            padding: 20px;
        }
        .chart-container {
            padding: 20px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .table-container {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
        }
        .table thead th {
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container my-4">
        <!-- Top Overview Section -->
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
                    <h2>
                        <?php
                        $topCategory = mysqli_fetch_assoc($categorySalesResult);
                        echo $topCategory ? $topCategory['category_name'] : 'N/A';
                        ?>
                    </h2>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="categoryPieChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="salesTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tables Section -->
        <div class="row">
            <div class="col-md-6">
                <div class="table-container">
                    <h5 class="card-header">Top 5 Products</h5>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Units Sold</th>
                                <th>Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($topProductsResult)): ?>
                                <tr>
                                    <td><?php echo $row['product_name']; ?></td>
                                    <td><?php echo $row['total_sold']; ?></td>
                                    <td>RM <?php echo number_format($row['total_revenue'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="categoryBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Category Pie Chart
        const categoryLabels = <?php echo json_encode(array_column(mysqli_fetch_all($categorySalesResult, MYSQLI_ASSOC), 'category_name')); ?>;
        const categoryData = <?php echo json_encode(array_column(mysqli_fetch_all($categorySalesResult, MYSQLI_ASSOC), 'category_sales')); ?>;
        const categoryPieCtx = document.getElementById('categoryPieChart').getContext('2d');
        new Chart(categoryPieCtx, {
            type: 'pie',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryData,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            }
        });

        // Sales Trend Line Chart
        const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
        new Chart(salesTrendCtx, {
            type: 'line',
            data: {
                labels: ['2024-11-01', '2024-11-02', '2024-11-03'], // Replace with dynamic dates
                datasets: [{
                    label: 'Sales (RM)',
                    data: [1000, 1500, 2000], // Replace with dynamic sales data
                    borderColor: '#36A2EB',
                    fill: false
                }]
            }
        });

        // Category Bar Chart
        const categoryBarCtx = document.getElementById('categoryBarChart').getContext('2d');
        new Chart(categoryBarCtx, {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Sales (RM)',
                    data: categoryData,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            }
        });
    </script>
</body>
</html>

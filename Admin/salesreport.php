<?php
include 'dataconnection.php'; // 连接数据库
include 'admin_sidebar.php'; // 引入管理员侧边栏

// 获取总订单数
$total_orders_query = "SELECT COUNT(*) as total_orders FROM orders WHERE order_status = 'Complete'";
$total_orders_result = mysqli_query($connect, $total_orders_query);
$total_orders = mysqli_fetch_assoc($total_orders_result)['total_orders'];

// 获取总客户数
$total_customers_query = "SELECT COUNT(DISTINCT user_id) as total_customers FROM orders";
$total_customers_result = mysqli_query($connect, $total_customers_query);
$total_customers = mysqli_fetch_assoc($total_customers_result)['total_customers'];

// 获取总销售额
$total_sales_query = "SELECT SUM(final_amount) as total_sales FROM orders WHERE order_status = 'Complete'";
$total_sales_result = mysqli_query($connect, $total_sales_query);
$total_sales = mysqli_fetch_assoc($total_sales_result)['total_sales'];

// 获取销售类别占比
$category_sales_query = "
    SELECT c.category_name, SUM(od.total_price) as total_sales
    FROM order_details od
    JOIN product p ON od.product_id = p.product_id
    JOIN category c ON p.category_id = c.category_id
    GROUP BY c.category_name
";
$category_sales_result = mysqli_query($connect, $category_sales_query);

// 获取畅销产品
$top_sales_query = "
    SELECT p.product_name, SUM(od.quantity) as total_sold
    FROM order_details od
    JOIN product p ON od.product_id = p.product_id
    GROUP BY p.product_name
    ORDER BY total_sold DESC LIMIT 5
";
$top_sales_result = mysqli_query($connect, $top_sales_query);

// 选择日期范围
$start_date = $_GET['start_date'] ?? date('Y-m-01');  // 默认本月第一天
$end_date = $_GET['end_date'] ?? date('Y-m-t');  // 默认本月最后一天

// 获取日期范围内的销售数据
$sales_by_date_query = "
    SELECT DATE(order_date) as order_date, SUM(final_amount) as daily_sales
    FROM orders
    WHERE order_status = 'Complete' AND order_date BETWEEN '$start_date' AND '$end_date'
    GROUP BY DATE(order_date)
";
$sales_by_date_result = mysqli_query($connect, $sales_by_date_query);
$sales_data = [];
while ($row = mysqli_fetch_assoc($sales_by_date_result)) {
    $sales_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
        }
        .card-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .container-fluid {
            padding-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Top Stats -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Total Orders</div>
                    <div class="card-body">
                        <h3 class="text-center"><?php echo $total_orders; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Total Customers</div>
                    <div class="card-body">
                        <h3 class="text-center"><?php echo $total_customers; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Total Sales</div>
                    <div class="card-body">
                        <h3 class="text-center">$<?php echo number_format($total_sales, 2); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Sales Pie Chart -->
        <div class="card">
            <div class="card-header">Category Sales Distribution</div>
            <div class="card-body">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Top 5 Selling Products -->
        <div class="card">
            <div class="card-header">Top 5 Selling Products</div>
            <div class="card-body">
                <ul class="list-group">
                    <?php while ($row = mysqli_fetch_assoc($top_sales_result)): ?>
                        <li class="list-group-item">
                            <?php echo $row['product_name']; ?> - <?php echo $row['total_sold']; ?> sold
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>

        <!-- Sales Over Time Line Chart -->
        <div class="card">
            <div class="card-header">Sales Over Time</div>
            <div class="card-body">
                <canvas id="salesLineChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Category Sales Pie Chart
        var ctx1 = document.getElementById('categoryChart').getContext('2d');
        var categoryData = {
            labels: [<?php while($row = mysqli_fetch_assoc($category_sales_result)): echo "'" . $row['category_name'] . "',"; endwhile; ?>],
            datasets: [{
                data: [<?php mysqli_data_seek($category_sales_result, 0); while($row = mysqli_fetch_assoc($category_sales_result)): echo $row['total_sales'] . ","; endwhile; ?>],
                backgroundColor: ['#FF5733', '#33FF57', '#3357FF', '#FFD700', '#FF69B4'],
            }]
        };
        var categoryChart = new Chart(ctx1, {
            type: 'pie',
            data: categoryData,
        });

        // Sales Over Time Line Chart
        var ctx2 = document.getElementById('salesLineChart').getContext('2d');
        var salesDates = [<?php foreach ($sales_data as $data) { echo "'" . $data['order_date'] . "',"; } ?>];
        var salesValues = [<?php foreach ($sales_data as $data) { echo $data['daily_sales'] . ","; } ?>];

        var salesChart = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: salesDates,
                datasets: [{
                    label: 'Sales ($)',
                    data: salesValues,
                    borderColor: '#42A5F5',
                    fill: false,
                    tension: 0.1,
                }]
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

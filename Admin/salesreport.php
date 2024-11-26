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
            background-color: #2a2a3d;
            color: #fff;
            font-family: 'Arial', sans-serif;
        }
        .dashboard-header {
            text-align: center;
            padding: 20px 0;
        }
        .card {
            background-color: #3c3f54;
            border: none;
            color: #fff;
        }
        .card-header {
            background-color: #505273;
            border-bottom: 2px solid #676891;
            font-size: 1.2rem;
            text-align: center;
        }
        .chart-container {
            position: relative;
            height: 50vh;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="dashboard-header">E-Commerce Sales Dashboard</h1>

        <!-- Top Summary Section -->
        <div class="row mb-4">
            <?php
            include 'dataconnection.php'; // 替换为您的数据库连接文件

            $conn = connectDatabase();

            // 获取订单总金额、总数量、折扣金额
            $summaryQuery = "SELECT 
                COUNT(*) as totalOrders, 
                SUM(final_amount) as totalRevenue, 
                SUM(discount_amount) as totalDiscount 
                FROM orders";
            $summaryResult = mysqli_fetch_assoc(mysqli_query($conn, $summaryQuery));

            // 数据展示卡片
            $summaryData = [
                "Total Orders" => $summaryResult['totalOrders'],
                "Total Revenue" => number_format($summaryResult['totalRevenue'], 2),
                "Total Discount" => number_format($summaryResult['totalDiscount'], 2)
            ];
            foreach ($summaryData as $title => $value) {
                echo "<div class='col-md-4'>
                        <div class='card'>
                            <div class='card-header'>$title</div>
                            <div class='card-body text-center'>
                                <h3>$value</h3>
                            </div>
                        </div>
                      </div>";
            }
            ?>
        </div>

        <!-- Chart Section -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Revenue by Month</div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Orders by Shipping Method</div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="shippingChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // 获取按月收入数据
    $monthlyRevenueQuery = "SELECT 
        MONTH(order_date) as month, 
        SUM(final_amount) as revenue 
        FROM orders GROUP BY MONTH(order_date)";
    $monthlyRevenueResult = mysqli_query($conn, $monthlyRevenueQuery);

    $months = [];
    $revenues = [];
    while ($row = mysqli_fetch_assoc($monthlyRevenueResult)) {
        $months[] = date("F", mktime(0, 0, 0, $row['month'], 1));
        $revenues[] = $row['revenue'];
    }

    // 获取按配送方式的订单数量
    $shippingQuery = "SELECT 
        shipping_method, 
        COUNT(*) as count 
        FROM orders GROUP BY shipping_method";
    $shippingResult = mysqli_query($conn, $shippingQuery);

    $shippingMethods = [];
    $shippingCounts = [];
    while ($row = mysqli_fetch_assoc($shippingResult)) {
        $shippingMethods[] = $row['shipping_method'];
        $shippingCounts[] = $row['count'];
    }

    // 关闭数据库连接
    mysqli_close($conn);
    ?>

    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Monthly Revenue',
                    data: <?php echo json_encode($revenues); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Shipping Chart
        const shippingCtx = document.getElementById('shippingChart').getContext('2d');
        new Chart(shippingCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($shippingMethods); ?>,
                datasets: [{
                    label: 'Shipping Methods',
                    data: <?php echo json_encode($shippingCounts); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>

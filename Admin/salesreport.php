<?php
include('dataconnection.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f1f1f1;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            margin-top: 30px;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            text-align: center;
            border-radius: 12px 12px 0 0;
        }
        .chart-container {
            position: relative;
            height: 400px;
        }
        .table thead {
            background-color: #343a40;
            color: white;
        }
        .table-striped tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        .table-striped tbody tr:nth-child(even) {
            background-color: #ffffff;
        }
        .table tbody td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-5 text-success">Sales Report</h1>

        <!-- Total Sales Overview -->
        <div class="card mb-4">
            <div class="card-header">Total Sales Overview</div>
            <div class="card-body">
                <?php
                $result = mysqli_query($connect, "SELECT SUM(final_amount) AS total_sales, COUNT(order_id) AS total_orders FROM orders");
                $data = mysqli_fetch_assoc($result);
                $total_sales = $data['total_sales'] ?? 0;
                $total_orders = $data['total_orders'] ?? 0;
                ?>
                <div class="row text-center">
                    <div class="col-6">
                        <p>Total Sales</p>
                        <h3 class="text-success">$<?= number_format($total_sales, 2) ?></h3>
                    </div>
                    <div class="col-6">
                        <p>Total Orders</p>
                        <h3 class="text-primary"><?= $total_orders ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales by Category Chart -->
        <div class="card mb-4">
            <div class="card-header">Sales by Category</div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
                <?php
                $categories = [];
                $sales_by_category = [];
                $result = mysqli_query($connect, "
                    SELECT c.category_name, SUM(od.total_price) AS total
                    FROM order_details od
                    JOIN product p ON od.product_id = p.product_id
                    JOIN category c ON p.category_id = c.category_id
                    GROUP BY c.category_name
                ");
                while ($row = mysqli_fetch_assoc($result)) {
                    $categories[] = $row['category_name'];
                    $sales_by_category[] = $row['total'];
                }
                ?>
                <script>
                    const categoryLabels = <?= json_encode($categories) ?>;
                    const categoryData = <?= json_encode($sales_by_category) ?>;
                    const ctx1 = document.getElementById('categoryChart').getContext('2d');
                    new Chart(ctx1, {
                        type: 'pie',
                        data: {
                            labels: categoryLabels,
                            datasets: [{
                                label: 'Sales by Category ($)',
                                data: categoryData,
                                backgroundColor: ['#f39c12', '#3498db', '#e74c3c', '#2ecc71'],
                                hoverOffset: 4
                            }]
                        },
                    });
                </script>
            </div>
        </div>

        <!-- Monthly Sales Line Chart -->
        <div class="card mb-4">
            <div class="card-header">Monthly Sales Overview</div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
                <?php
                $months = [];
                $monthly_sales = [];
                $result = mysqli_query($connect, "
                    SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, SUM(final_amount) AS total
                    FROM orders
                    GROUP BY month
                ");
                while ($row = mysqli_fetch_assoc($result)) {
                    $months[] = $row['month'];
                    $monthly_sales[] = $row['total'];
                }
                ?>
                <script>
                    const monthlyLabels = <?= json_encode($months) ?>;
                    const monthlyData = <?= json_encode($monthly_sales) ?>;
                    const ctx2 = document.getElementById('monthlyChart').getContext('2d');
                    new Chart(ctx2, {
                        type: 'line',
                        data: {
                            labels: monthlyLabels,
                            datasets: [{
                                label: 'Monthly Sales ($)',
                                data: monthlyData,
                                borderColor: '#1abc9c',
                                fill: false,
                                tension: 0.4,
                                pointRadius: 5
                            }]
                        },
                    });
                </script>
            </div>
        </div>

        <!-- Top 5 Best-Selling Products -->
        <div class="card mb-4">
            <div class="card-header">Top 5 Best-Selling Products</div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product Name</th>
                            <th>Total Quantity Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = mysqli_query($connect, "
                            SELECT p.product_name, SUM(od.quantity) AS total_quantity
                            FROM order_details od
                            JOIN product p ON od.product_id = p.product_id
                            GROUP BY p.product_name
                            ORDER BY total_quantity DESC
                            LIMIT 5
                        ");
                        $rank = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                                <td>{$rank}</td>
                                <td>{$row['product_name']}</td>
                                <td>{$row['total_quantity']}</td>
                              </tr>";
                            $rank++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS & Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js"></script>
</body>
</html>

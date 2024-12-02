<?php
include('dataconnection.php');

// Fetch total sales
$total_sales_query = "SELECT SUM(final_amount) AS total_sales FROM orders";
$total_sales_result = mysqli_query($connect, $total_sales_query);
$total_sales = mysqli_fetch_assoc($total_sales_result)['total_sales'];

// Fetch sales by category
$sales_by_category_query = "
    SELECT c.category_name, SUM(od.total_price) AS category_sales
    FROM order_details od
    JOIN product p ON od.product_id = p.product_id
    JOIN category c ON p.category_id = c.category_id
    GROUP BY c.category_name
";
$sales_by_category_result = mysqli_query($connect, $sales_by_category_query);

// Fetch sales by order status
$sales_by_status_query = "
    SELECT order_status, COUNT(order_id) AS total_orders, SUM(final_amount) AS total_sales
    FROM orders
    GROUP BY order_status
";
$sales_by_status_result = mysqli_query($connect, $sales_by_status_query);

// Fetch monthly sales
$monthly_sales_query = "
    SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, SUM(final_amount) AS monthly_sales
    FROM orders
    GROUP BY DATE_FORMAT(order_date, '%Y-%m')
    ORDER BY month
";
$monthly_sales_result = mysqli_query($connect, $monthly_sales_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container my-5">
    <h1 class="text-center">Sales Report</h1>

    <!-- Total Sales -->
    <div class="row my-4">
        <div class="col-md-4 offset-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h4>Total Sales</h4>
                    <p class="display-4">$<?= number_format($total_sales, 2) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales by Category -->
    <div class="my-4">
        <h3>Sales by Category</h3>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Category</th>
                <th>Sales ($)</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($sales_by_category_result)) { ?>
                <tr>
                    <td><?= $row['category_name'] ?></td>
                    <td><?= number_format($row['category_sales'], 2) ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Sales by Order Status -->
    <div class="my-4">
        <h3>Sales by Order Status</h3>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Status</th>
                <th>Total Orders</th>
                <th>Sales ($)</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($sales_by_status_result)) { ?>
                <tr>
                    <td><?= $row['order_status'] ?></td>
                    <td><?= $row['total_orders'] ?></td>
                    <td><?= number_format($row['total_sales'], 2) ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Monthly Sales -->
    <div class="my-4">
        <h3>Monthly Sales</h3>
        <canvas id="monthlySalesChart"></canvas>
        <script>
            <?php
            $months = [];
            $monthly_sales = [];
            while ($row = mysqli_fetch_assoc($monthly_sales_result)) {
                $months[] = $row['month'];
                $monthly_sales[] = $row['monthly_sales'];
            }
            ?>
            const months = <?= json_encode($months) ?>;
            const sales = <?= json_encode($monthly_sales) ?>;

            const ctx = document.getElementById('monthlySalesChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Monthly Sales ($)',
                        data: sales,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    }
                }
            });
        </script>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

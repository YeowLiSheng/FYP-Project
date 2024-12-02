<?php
include("dataconnection.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            margin: 20px auto;
        }
        .chart-container {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 10px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sales Report</h1>

        <!-- Chart 1: Daily Sales Overview -->
        <div class="chart-container">
            <h2>每日销售总览</h2>
            <canvas id="dailySalesChart"></canvas>
        </div>

        <!-- Chart 2: Category Sales Share -->
        <div class="chart-container">
            <h2>产品类别销售占比</h2>
            <canvas id="categorySalesChart"></canvas>
        </div>

        <!-- Chart 3: Top Products Sales -->
        <div class="chart-container">
            <h2>热门商品销售情况</h2>
            <canvas id="topProductsChart"></canvas>
        </div>

        <!-- Chart 4: Voucher Usage -->
        <div class="chart-container">
            <h2>折扣与优惠券使用情况</h2>
            <canvas id="voucherUsageChart"></canvas>
        </div>
    </div>

    <script>
        <?php
        // Daily Sales Data
        $daily_sales_query = "SELECT DATE(order_date) as date, SUM(final_amount) as total FROM orders GROUP BY DATE(order_date)";
        $daily_sales_result = mysqli_query($connect, $daily_sales_query);
        $daily_sales_data = [];
        while ($row = mysqli_fetch_assoc($daily_sales_result)) {
            $daily_sales_data[] = $row;
        }
        ?>

        const dailySalesLabels = <?php echo json_encode(array_column($daily_sales_data, 'date')); ?>;
        const dailySalesData = <?php echo json_encode(array_column($daily_sales_data, 'total')); ?>;

        new Chart(document.getElementById('dailySalesChart'), {
            type: 'line',
            data: {
                labels: dailySalesLabels,
                datasets: [{
                    label: '销售金额',
                    data: dailySalesData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    tension: 0.2,
                    fill: false
                }]
            }
        });

        <?php
        // Category Sales Data
        $category_sales_query = "
            SELECT c.category_name, SUM(o.final_amount) as total
            FROM orders o
            JOIN order_details od ON o.order_id = od.order_id
            JOIN product p ON od.product_id = p.product_id
            JOIN category c ON p.category_id = c.category_id
            GROUP BY c.category_name
        ";
        $category_sales_result = mysqli_query($connect, $category_sales_query);
        $category_sales_data = [];
        while ($row = mysqli_fetch_assoc($category_sales_result)) {
            $category_sales_data[] = $row;
        }
        ?>

        const categorySalesLabels = <?php echo json_encode(array_column($category_sales_data, 'category_name')); ?>;
        const categorySalesData = <?php echo json_encode(array_column($category_sales_data, 'total')); ?>;

        new Chart(document.getElementById('categorySalesChart'), {
            type: 'pie',
            data: {
                labels: categorySalesLabels,
                datasets: [{
                    label: '销售占比',
                    data: categorySalesData,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            }
        });

        <?php
        // Top Products Data
        $top_products_query = "
            SELECT p.product_name, SUM(od.quantity) as total_quantity
            FROM order_details od
            JOIN product p ON od.product_id = p.product_id
            GROUP BY p.product_name
            ORDER BY total_quantity DESC
            LIMIT 5
        ";
        $top_products_result = mysqli_query($connect, $top_products_query);
        $top_products_data = [];
        while ($row = mysqli_fetch_assoc($top_products_result)) {
            $top_products_data[] = $row;
        }
        ?>

        const topProductsLabels = <?php echo json_encode(array_column($top_products_data, 'product_name')); ?>;
        const topProductsData = <?php echo json_encode(array_column($top_products_data, 'total_quantity')); ?>;

        new Chart(document.getElementById('topProductsChart'), {
            type: 'bar',
            data: {
                labels: topProductsLabels,
                datasets: [{
                    label: '销售数量',
                    data: topProductsData,
                    backgroundColor: 'rgba(153, 102, 255, 0.5)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            }
        });

        <?php
        // Voucher Usage Data
        $voucher_usage_query = "
            SELECT v.voucher_code, SUM(vu.usage_num) as total_usage
            FROM voucher_usage vu
            JOIN voucher v ON vu.voucher_id = v.voucher_id
            GROUP BY v.voucher_code
        ";
        $voucher_usage_result = mysqli_query($connect, $voucher_usage_query);
        $voucher_usage_data = [];
        while ($row = mysqli_fetch_assoc($voucher_usage_result)) {
            $voucher_usage_data[] = $row;
        }
        ?>

        const voucherUsageLabels = <?php echo json_encode(array_column($voucher_usage_data, 'voucher_code')); ?>;
        const voucherUsageData = <?php echo json_encode(array_column($voucher_usage_data, 'total_usage')); ?>;

        new Chart(document.getElementById('voucherUsageChart'), {
            type: 'bar',
            data: {
                labels: voucherUsageLabels,
                datasets: [{
                    label: '使用次数',
                    data: voucherUsageData,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            }
        });
    </script>
</body>
</html>

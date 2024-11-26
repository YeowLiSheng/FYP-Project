<?php
include 'dataconnection.php'; // Connect to database
include 'admin_sidebar.php'; // Include admin sidebar

// Fetch the sales data for charts and tables
$sql = "SELECT 
            o.order_date, 
            o.Grand_total, 
            o.discount_amount, 
            o.final_amount, 
            p.product_name, 
            od.quantity, 
            od.total_price 
        FROM orders o 
        JOIN order_details od ON o.order_id = od.order_id 
        JOIN product p ON od.product_id = p.product_id 
        WHERE o.order_status = 'Complete'
        ORDER BY o.order_date DESC";
$result = mysqli_query($conn, $sql);

// Prepare data for chart (Total sales per month)
$sales_per_month = [];
while ($row = mysqli_fetch_assoc($result)) {
    $month = date("Y-m", strtotime($row['order_date']));
    if (!isset($sales_per_month[$month])) {
        $sales_per_month[$month] = 0;
    }
    $sales_per_month[$month] += $row['final_amount'];
}

// Get total sales, total orders, and other statistics
$total_sales = array_sum($sales_per_month);
$total_orders = mysqli_num_rows($result);

// Close the result set
mysqli_free_result($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link rel="stylesheet" href="path/to/bootstrap.css"> <!-- Use Bootstrap for styling -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js for charts -->
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .chart-container {
            width: 80%;
            margin: 0 auto;
        }
        .stats-card {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 10px;
            margin: 15px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .stats-card h4 {
            margin-bottom: 20px;
            font-size: 20px;
        }
        .table-container {
            width: 100%;
            margin-top: 30px;
        }
        .table-container table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table-container th, .table-container td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .table-container th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mt-5">Sales Report</h2>
    
    <!-- Stats Section -->
    <div class="row">
        <div class="col-md-3">
            <div class="stats-card">
                <h4>Total Sales</h4>
                <p><?php echo "$" . number_format($total_sales, 2); ?></p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h4>Total Orders</h4>
                <p><?php echo $total_orders; ?></p>
            </div>
        </div>
        <!-- Add more stats cards as needed -->
    </div>
    
    <!-- Chart Section -->
    <div class="chart-container">
        <canvas id="salesChart"></canvas>
    </div>

    <!-- Orders Table Section -->
    <div class="table-container">
        <h4>Latest Orders</h4>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Order Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>{$row['order_id']}</td>
                            <td>{$row['product_name']}</td>
                            <td>{$row['quantity']}</td>
                            <td>\${$row['total_price']}</td>
                            <td>{$row['order_date']}</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</div>

<script>
    // Data for Chart.js
    const salesPerMonth = <?php echo json_encode($sales_per_month); ?>;
    const months = Object.keys(salesPerMonth);
    const salesValues = Object.values(salesPerMonth);

    // Create the chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line', // Line chart
        data: {
            labels: months,
            datasets: [{
                label: 'Total Sales',
                data: salesValues,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return "$" + tooltipItem.raw.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: function(value) {
                            return "$" + value.toFixed(2);
                        }
                    }
                }
            }
        }
    });
</script>

</body>
</html>

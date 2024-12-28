<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

$product= "SELECT COUNT(*) AS product_count FROM product";
$product_result = $connect->query($product);

$product_count = 0;
if ($product_result->num_rows > 0) {
    $row = $product_result->fetch_assoc();
    $product_count = $row['product_count'];
}

$staff= "SELECT COUNT(*) AS staff_count FROM admin";
$staff_result = $connect->query($staff);

$staff_count = 0;
if ($staff_result->num_rows > 0) {
    $row = $staff_result->fetch_assoc();
    $staff_count = $row['staff_count'];
}

$user = "SELECT COUNT(*) AS user_count FROM `user`";
$user_result = $connect->query($user);

$user_count = 0;
if ($user_result->num_rows > 0) {
    $row = $user_result->fetch_assoc();
    $user_count = $row['user_count'];
}

$order = "SELECT COUNT(*) AS order_count FROM `orders`";
$order_result = $connect->query($order);

$order_count = 0;
if ($order_result->num_rows > 0) {
    $row = $order_result->fetch_assoc();
    $order_count = $row['order_count'];
}

function getTotalSales($connect) {
    $query = "SELECT SUM(final_amount) AS total_sales FROM orders";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_assoc($result)['total_sales'];
}
$totalSales = getTotalSales($connect);

function getTopProducts($connect) {
    $query = "SELECT p.product_name, p.product_image, SUM(od.quantity) AS total_sold 
              FROM order_details od
              INNER JOIN product p ON od.product_id = p.product_id
              GROUP BY p.product_name, p.product_image
              ORDER BY total_sold DESC LIMIT 5";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
$topProducts = getTopProducts($connect);

function getWeeklySalesWithDates($connect) {
    // Create an array to hold the results, ensuring all weeks in the past month are included
    $weeks = [];

    // Calculate the start and end dates of the last 4 weeks (1 month)
    $start_date = date('Y-m-d', strtotime('monday this week -3 weeks'));
    $end_date = date('Y-m-d', strtotime('sunday this week'));

    // Populate all week ranges for the past month
    for ($i = 0; $i < 4; $i++) {
        $week_start = date('Y-m-d', strtotime($start_date . " +" . ($i * 7) . " days"));
        $week_end = date('Y-m-d', strtotime($week_start . " +6 days"));
        $weeks[] = [
            'week_range' => "$week_start ~ $week_end",
            'total_sales' => 0
        ];
    }

    // Query to get sales grouped by week
    $query = "
        SELECT 
            CONCAT(DATE_FORMAT(order_date - INTERVAL WEEKDAY(order_date) DAY, '%Y-%m-%d'), ' ~ ', 
                   DATE_FORMAT(order_date + INTERVAL (6 - WEEKDAY(order_date)) DAY, '%Y-%m-%d')) AS week_range,
            SUM(final_amount) AS total_sales
        FROM orders
        WHERE order_date BETWEEN '$start_date' AND '$end_date'
        GROUP BY YEAR(order_date), WEEK(order_date)
    ";

    $result = mysqli_query($connect, $query);
    $sales_data = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Merge the query result with the full week list
    foreach ($weeks as &$week) {
        foreach ($sales_data as $data) {
            if ($week['week_range'] === $data['week_range']) {
                $week['total_sales'] = $data['total_sales'];
                break;
            }
        }
    }

    return $weeks;
}

$weeklySales = getWeeklySalesWithDates($connect);



function getRecentUsers($connect) {
    $query = "SELECT user_name, user_image, user_email, user_join_time 
              FROM `user`
              ORDER BY user_join_time DESC
              LIMIT 5";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
$recentUsers = getRecentUsers($connect);

function getGenderDistribution($connect) {
    $query = "SELECT user_gender, COUNT(*) AS count 
              FROM `user`
              GROUP BY user_gender";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
$genderDistribution = getGenderDistribution($connect);

function getLowStockProducts($connect) {
    $query = "SELECT product_name, product_image, product_stock 
              FROM product 
              ORDER BY product_stock ASC 
              LIMIT 5";
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$lowStockProducts = getLowStockProducts($connect);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <title>Admin Dashboard</title>
    <style>
        body {
    font-family: 'Roboto', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f8f9fa;
    color: #333;
}

.container {
    padding: 20px;
    margin-left: 105px;
    margin-top: 70px;

}
.home-icon-container {
            
            display: flex;
            align-items: center;
            margin-bottom: 20px; /* Space between home icon and cards */
        }

        .home-icon-container i {
            font-size: 30px;
            margin-right: 10px;
        }

        .home-icon-container p {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin: 0;
        }
/* Card styles */
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.ccard {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

.ccard:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.icon {
    font-size: 50px;
    color: #17a2b8;
    margin-bottom: 10px;
}

.number {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
}

.name {
    font-size: 1rem;
    font-weight: 500;
    color: #555;
}

/* Section header */
.section-header {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 15px;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 5px;
}

/* Table container for aligning tables in a row */
.table-container {
    display: flex;
    gap: 20px; /* Space between tables */
    margin-top: 20px;
}

/* Individual table styles */
.table-small {
    width: 50%; /* Each table takes half of the width */
    max-width: 600px; /* Cap maximum width */
    flex: 1; /* Adjust width dynamically */
}

/* Table styling */
.table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
}

.table th,
.table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
    font-size: 0.9rem;
}

.table th {
    background-color: #f1f3f5;
    font-weight: 700;
    color: #495057;
}

.table-striped tbody tr:nth-child(odd) {
    background-color: #f8f9fa;
}

/* User image styling for Recent Users */
.user-image {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

/* Chart container */
.chart-container {
    margin-top: 30px;
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}
.low-stock-gender-container {
    display: flex;
    gap: 20px; /* Space between sections */
    margin-top: 20px;
}

/* Low Stock Products Section */
.low-stock-products {
    flex: 1; /* Equal width */
    max-width: 600px; /* Cap maximum width */
}

/* Gender Chart Section */
.gender-chart-container {
    flex: 1; /* Equal width */
    max-width: 600px; /* Match the Recent Users table width */
    background: white; /* Match the same background color */
    border-radius: 10px; /* Same rounded corners */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Same shadow effect */
    padding: 20px; /* Inner spacing */
    margin-top: 20px; /* Consistent spacing */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

    .gender-chart-container h2 {
        font-size: 1.5rem; /* Larger font size for title */
        font-weight: bold;
        margin-bottom: 20px; /* Space below title */
        color: #333; /* Darker text color */
        text-align: center;
    }

    #genderPieChart {
        width: 100%;
        height: 400px; /* Set fixed height */
    }
/* Responsive adjustments */
@media (max-width: 768px) {
    .container {
        margin-left: 0;
        padding: 15px;
    }

    .cards {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }

    .table-container {
        flex-direction: column; /* Stack tables vertically */
    }
}
.card-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr); 
    gap: 20px; 
    margin-top: 20px;
}


.card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}



.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.ccard .icon {
            font-size: 36px;
            color: #6c757d;
        }
        
.card-header {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 15px;
    text-align: center;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 10px;
}


.card-content {
    overflow: auto; 
}


.card table {
    width: 100%;
    border-collapse: collapse;
}

.card table th,
.card table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
}

.card table th {
    background-color: #f1f3f5;
    font-weight: bold;
}


@media (max-width: 768px) {
    .card-container {
        grid-template-columns: 1fr; 
    }
}
    </style>
</head>
<body>
    <div class="container">
    <div class="home-icon-container">
            <i class="fas fa-home"></i>
            <p>Homepage</p>
        </div>
        <!-- Cards -->
        <div class="cards">
            <div class="ccard">
                <i class="fas fa-tags icon"></i>
                <p class="number"><?php echo $product_count; ?></p>
                <p class="name">Products</p>
            </div>
            <div class="ccard">
                <i class="fas fa-users icon"></i>
                <p class="number"><?php echo $staff_count; ?></p>
                <p class="name">Staff</p>
            </div>
            <div class="ccard">
                <i class="fas fa-shopping-cart icon"></i>
                <p class="number"><?php echo $order_count; ?></p>
                <p class="name">Orders</p>
            </div>
            <div class="ccard">
                <i class="fas fa-users icon"></i>
                <p class="number"><?php echo $user_count; ?></p>
                <p class="name">User</p>
            </div>
            <div class="ccard">
                <i class="fas fa-dollar-sign icon"></i>
                <p class="number">RM<?php echo number_format($totalSales, 2); ?></p>
                <p class="name">Total Sales</p>
            </div>
        </div>

        <!-- Weekly Sales Chart -->
        <div class="chart-container">
            <h2 style="text-align: center;">Weekly Sales Comparison</h2>
            <canvas id="weeklySalesChart"></canvas>
        </div>

        <div class="card-container">
    <!-- Top 5 Products -->
    <div class="card">
        <div class="card-header">Top 5 Products by Sales</div>
        <div class="card-content">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product Image</th>
                        <th>Product Name</th>
                        <th>Units Sold</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topProducts as $product): ?>
                        <tr>
                            <td><img src="../User/images/<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_name']; ?>" style="width: 60px; height: 60px; border-radius: 8px;"></td>
                            <td><?php echo $product['product_name']; ?></td>
                            <td><?php echo $product['total_sold']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
                        
    <!-- Recent Users -->
    <div class="card">
        <div class="card-header">Recent Users</div>
        <div class="card-content">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Join Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $user): ?>
                        <tr>
                        <td>
                            <img class="user-image" 
                                src="<?php 
                                    $imagePath = "../User/" . htmlspecialchars($user['user_image']); 
                                    echo (empty($user['user_image']) || !file_exists($imagePath)) ? "../User/images/User-image.png" : $imagePath; ?>" alt="<?php echo htmlspecialchars($user['user_name']); ?>"></td>       
                            <td><?php echo htmlspecialchars($user['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['user_email']); ?></td>
                            <td><?php echo htmlspecialchars($user['user_join_time']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low Stock Products -->
    <div class="card">
        <div class="card-header">Products with Low Stock</div>
        <div class="card-content">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Image</th>
                        <th>Stock Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lowStockProducts as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td><img src="../User/images/<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="width: 60px; height: 60px; border-radius: 8px;"></td>
                            <td><?php echo htmlspecialchars($product['product_stock']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Gender Distribution -->
    <div class="card">
        <div class="card-header">Gender Distribution</div>
        <div class="card-content">
            <div id="genderPieChart" style="width: 100%; height: 400px;"></div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const weeklySalesData = <?php echo json_encode($weeklySales); ?>;
        const labels = weeklySalesData.map(data => data.week_range);
        const sales = weeklySalesData.map(data => data.total_sales);

        const ctx = document.getElementById('weeklySalesChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(75, 192, 192, 0.6)');
        gradient.addColorStop(1, 'rgba(75, 192, 192, 0.1)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Sales (Weekly)',
                    data: sales,
                    fill: true,
                    backgroundColor: gradient,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(75, 192, 192, 1)',
                    pointBorderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { title: { display: true, text: 'Week Range' } },
                    y: { beginAtZero: true, title: { display: true, text: 'Total Sales (RM)' } }
                }
            }
        });

        
    </script>
    <script src="https://www.gstatic.com/charts/loader.js"></script>
<script>
    google.charts.load('current', { packages: ['corechart'] });
    google.charts.setOnLoadCallback(drawGenderChart);

    function drawGenderChart() {
        var genderData = google.visualization.arrayToDataTable([
            ['Gender', 'Count'],
            <?php
            foreach ($genderDistribution as $gender) {
                echo "['" . $gender['user_gender'] . "', " . $gender['count'] . "],";
            }
            ?>
        ]);

        var genderChartOptions = {
            title: 'Customer Gender Distribution',
            titleTextStyle: {
                fontSize: 18, // Increase font size
                bold: true, // Make it bold
                color: '#333' // Darker title color
            },
            pieHole: 0.4, // Donut chart
            chartArea: { width: '85%', height: '75%' }, // Adjust chart area
            colors: ['#FF6384', '#36A2EB', '#FFCE56', '#8BC34A'], // Updated color scheme
            legend: { position: 'bottom', textStyle: { fontSize: 14 } }, // Position legend at the bottom
            pieSliceTextStyle: { fontSize: 12 } // Size of text inside slices
        };

        var genderPieChart = new google.visualization.PieChart(document.getElementById('genderPieChart'));
        genderPieChart.draw(genderData, genderChartOptions);
    }
</script>
</body>
</html>


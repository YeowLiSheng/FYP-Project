<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Sales Report</h1>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="filter-form" class="row g-3">
                    <div class="col-md-4">
                        <label for="startDate" class="form-label">Start Date</label>
                        <input type="date" id="startDate" name="startDate" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="endDate" class="form-label">End Date</label>
                        <input type="date" id="endDate" name="endDate" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="category" class="form-label">Category</label>
                        <select id="category" name="category" class="form-select">
                            <option value="">All</option>
                            <?php
                            // Fetch categories from the database
                            $conn = new mysqli('localhost', 'root', '', 'ecommerce'); // Adjust DB details
                            $result = $conn->query("SELECT category_id, category_name FROM category");
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['category_id']}'>{$row['category_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Sales Trends</div>
                    <div class="card-body">
                        <canvas id="salesTrends"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Sales by Category</div>
                    <div class="card-body">
                        <canvas id="categorySales"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Status Distribution -->
        <div class="card mt-4">
            <div class="card-header">Order Status Distribution</div>
            <div class="card-body">
                <canvas id="orderStatus"></canvas>
            </div>
        </div>

        <!-- Sales Summary Table -->
        <div class="card mt-4">
            <div class="card-header">Sales Summary</div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User ID</th>
                            <th>Order Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT order_id, user_id, order_date, final_amount, order_status FROM orders ORDER BY order_date DESC LIMIT 10";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['order_id']}</td>
                                <td>{$row['user_id']}</td>
                                <td>{$row['order_date']}</td>
                                <td>\$" . number_format($row['final_amount'], 2) . "</td>
                                <td>{$row['order_status']}</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Example Chart Data (Replace with dynamic PHP data if needed)
        const salesTrendsCtx = document.getElementById('salesTrends').getContext('2d');
        new Chart(salesTrendsCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                datasets: [{
                    label: 'Sales Amount',
                    data: [5000, 7000, 8000, 6000, 9000],
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                }]
            },
        });

        const categorySalesCtx = document.getElementById('categorySales').getContext('2d');
        new Chart(categorySalesCtx, {
            type: 'pie',
            data: {
                labels: ['Electronics', 'Clothing', 'Home & Kitchen', 'Books'],
                datasets: [{
                    data: [40, 30, 20, 10],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)'
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
        });

        const orderStatusCtx = document.getElementById('orderStatus').getContext('2d');
        new Chart(orderStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Processing', 'Shipped', 'Delivered', 'Cancelled'],
                datasets: [{
                    data: [20, 15, 50, 5],
                    backgroundColor: [
                        'rgba(255, 205, 86, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 99, 132, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 205, 86, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
        });
    </script>
</body>
</html>

<?php
// 连接数据库
$conn = new mysqli("localhost", "username", "password", "fyp");

// 检查连接
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 获取订单数据
$sql = "SELECT 
            o.order_id, 
            o.order_date, 
            o.Grand_total, 
            o.discount_amount, 
            o.delivery_charge, 
            o.final_amount, 
            o.order_status,
            p.payment_status, 
            p.payment_date 
        FROM orders o
        LEFT JOIN payment p ON o.order_id = p.order_id
        ORDER BY o.order_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f9;
        }
        .container {
            margin-top: 30px;
        }
        .table-container {
            background: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .table thead th {
            background-color: #007bff;
            color: white;
            text-align: center;
        }
        .table tbody td {
            text-align: center;
        }
        .status-processing {
            color: #ffc107;
        }
        .status-shipping {
            color: #17a2b8;
        }
        .status-complete {
            color: #28a745;
        }
        .payment-pending {
            color: #dc3545;
        }
        .payment-completed {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Sales Report</h2>
        <div class="table-container">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Grand Total</th>
                        <th>Discount</th>
                        <th>Delivery Charge</th>
                        <th>Final Amount</th>
                        <th>Order Status</th>
                        <th>Payment Status</th>
                        <th>Payment Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['order_id']}</td>
                                <td>" . date("Y-m-d H:i:s", strtotime($row['order_date'])) . "</td>
                                <td>$" . number_format($row['Grand_total'], 2) . "</td>
                                <td>$" . number_format($row['discount_amount'], 2) . "</td>
                                <td>$" . number_format($row['delivery_charge'], 2) . "</td>
                                <td>$" . number_format($row['final_amount'], 2) . "</td>
                                <td class='status-{$row['order_status']}'>{$row['order_status']}</td>
                                <td class='payment-{$row['payment_status']}'>{$row['payment_status']}</td>
                                <td>" . ($row['payment_date'] ? date("Y-m-d H:i:s", strtotime($row['payment_date'])) : 'N/A') . "</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9'>No records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>

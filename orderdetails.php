<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the order_id from the URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Fetch order information
$order_sql = "SELECT * FROM orders WHERE order_id = $order_id";
$order_result = $conn->query($order_sql);
$order = $order_result->fetch_assoc();

// Fetch order details
$order_details_sql = "
    SELECT od.product_name, od.quantity, od.unit_price, od.total_price 
    FROM order_details od 
    WHERE od.order_id = $order_id";
$order_details_result = $conn->query($order_details_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Details</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
    }
    .order-header, .order-details {
        border: 1px solid #e0e0e0;
        padding: 15px;
        margin-bottom: 20px;
    }
    .order-details table {
        width: 100%;
        border-collapse: collapse;
    }
    .order-details th, .order-details td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .order-details th {
        background-color: #f2f2f2;
    }
    .back-button {
        background-color: #4caf50;
        color: white;
        border: none;
        padding: 10px 15px;
        cursor: pointer;
        margin-top: 20px;
    }
    .back-button:hover {
        background-color: #45a049;
    }
</style>
</head>
<body>

<?php if ($order): ?>
    <div class="order-header">
        <h2>Order #<?php echo $order['order_id']; ?></h2>
        <p><strong>Order Date:</strong> <?php echo date("Y-m-d", strtotime($order['order_date'])); ?></p>
        <p><strong>Order Status:</strong> <?php echo $order['order_status']; ?></p>
        <p><strong>Total Amount:</strong> $<?php echo $order['final_amount']; ?></p>
    </div>

    <div class="order-details">
        <h3>Order Details</h3>
        <table>
            <tr>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total Price</th>
            </tr>
            <?php while ($row = $order_details_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['product_name']; ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td>$<?php echo number_format($row['unit_price'], 2); ?></td>
                    <td>$<?php echo number_format($row['total_price'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <button class="back-button" onclick="window.location.href='order.php'">Back to Orders</button>
<?php else: ?>
    <p>Order not found.</p>
<?php endif; ?>

</body>
</html>

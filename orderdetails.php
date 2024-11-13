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
</style>
</head>
<body>

<?php if ($order): ?>
    <div class="order-header">
        <h2>Order #<?php echo $order['order_id']; ?></h2>
        <p><strong>Order Date:</strong> <?php echo date("Y-m-d H:i:s", strtotime($order['order_date'])); ?></p>
        <p><strong>Final Amount:</strong> $<?php echo number_format($order['final_amount'], 2); ?></p>
        <p><strong>Order Status:</strong> <?php echo $order['order_status']; ?></p>
        <p><strong>Shipping Address:</strong> <?php echo $order['shipping_address']; ?></p>
        <p><strong>Shipping Method:</strong> <?php echo $order['shipping_method']; ?></p>
    </div>
    
    <div class="order-details">
        <h3>Order Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($detail = $order_details_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $detail['product_name']; ?></td>
                        <td><?php echo $detail['quantity']; ?></td>
                        <td>$<?php echo number_format($detail['unit_price'], 2); ?></td>
                        <td>$<?php echo number_format($detail['total_price'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>Order not found.</p>
<?php endif; ?>

</body>
</html>

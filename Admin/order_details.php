<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

// Fetch order data
$order_id = $_GET['order_id'];  // Get order ID from the URL
$query_order = "SELECT o.*, u.user_name, u.user_email, u.user_contact_number 
                FROM orders o 
                JOIN user u ON o.user_id = u.user_id
                WHERE o.order_id = $order_id";
$result_order = mysqli_query($conn, $query_order);
$order = mysqli_fetch_assoc($result_order);

// Fetch order details
$query_order_details = "SELECT od.*, p.product_name, p.product_image 
                        FROM order_details od
                        JOIN product p ON od.product_id = p.product_id
                        WHERE od.order_id = $order_id";
$result_order_details = mysqli_query($conn, $query_order_details);

// Update order status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['order_status'];
    $update_status_query = "UPDATE orders SET order_status = '$new_status' WHERE order_id = $order_id";
    mysqli_query($conn, $update_status_query);
    header("Location: order_details.php?order_id=$order_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include your CSS file for modern styling -->
</head>
<body>
    <div class="container">
        <h1>Order Details for Order #<?php echo $order['order_id']; ?></h1>
        
        <!-- User Information -->
        <div class="user-info">
            <h2>User Information</h2>
            <p><strong>Name:</strong> <?php echo $order['user_name']; ?></p>
            <p><strong>Email:</strong> <?php echo $order['user_email']; ?></p>
            <p><strong>Contact Number:</strong> <?php echo $order['user_contact_number']; ?></p>
            <p><strong>Shipping Address:</strong> <?php echo $order['shipping_address']; ?></p>
        </div>

        <!-- Order Details Table -->
        <table class="order-details">
            <thead>
                <tr>
                    <th>Product Image</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order_detail = mysqli_fetch_assoc($result_order_details)): ?>
                    <tr>
                        <td><img src="uploads/<?php echo $order_detail['product_image']; ?>" alt="<?php echo $order_detail['product_name']; ?>" width="100"></td>
                        <td><?php echo $order_detail['product_name']; ?></td>
                        <td><?php echo $order_detail['quantity']; ?></td>
                        <td><?php echo number_format($order_detail['unit_price'], 2); ?></td>
                        <td><?php echo number_format($order_detail['total_price'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Order Status Update Form -->
        <div class="order-status">
            <h2>Update Order Status</h2>
            <form method="POST" action="">
                <label for="order_status">Order Status:</label>
                <select name="order_status" id="order_status">
                    <option value="Processing" <?php echo $order['order_status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="Shipping" <?php echo $order['order_status'] == 'Shipping' ? 'selected' : ''; ?>>Shipping</option>
                    <option value="Complete" <?php echo $order['order_status'] == 'Complete' ? 'selected' : ''; ?>>Complete</option>
                </select>
                <button type="submit" name="update_status">Update Status</button>
            </form>
        </div>

        <div class="order-summary">
            <h2>Order Summary</h2>
            <p><strong>Grand Total:</strong> $<?php echo number_format($order['Grand_total'], 2); ?></p>
            <p><strong>Discount:</strong> $<?php echo number_format($order['discount_amount'], 2); ?></p>
            <p><strong>Delivery Charge:</strong> $<?php echo number_format($order['delivery_charge'], 2); ?></p>
            <p><strong>Final Amount:</strong> $<?php echo number_format($order['final_amount'], 2); ?></p>
        </div>
    </div>
</body>
</html>

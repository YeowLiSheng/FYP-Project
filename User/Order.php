<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];

// Retrieve all orders for the logged-in user
$order_query = "
    SELECT 
        o.order_id,
        o.order_date,
        o.final_amount,
        o.order_status,
        (SELECT product_image FROM order_details WHERE order_id = o.order_id LIMIT 1) AS first_product_image
    FROM orders AS o
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
";

$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$order_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add your CSS file -->
</head>
<body>
    <h1>Your Orders</h1>
    <div class="order-list">
        <?php while ($order = $order_result->fetch_assoc()): ?>
            <div class="order-item">
                <img src="images/<?php echo htmlspecialchars($order['first_product_image']); ?>" alt="Product Image">
                <div class="order-info">
                    <p>Order ID: <?php echo htmlspecialchars($order['order_id']); ?></p>
                    <p>Date: <?php echo htmlspecialchars($order['order_date']); ?></p>
                    <p>Total: RM<?php echo number_format($order['final_amount'], 2); ?></p>
                    <p>Status: <?php echo htmlspecialchars($order['order_status']); ?></p>
                    <a href="orderdetails.php?order_id=<?php echo htmlspecialchars($order['order_id']); ?>">View Details</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>

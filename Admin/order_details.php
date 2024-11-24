<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

// Fetch the order details based on the order_id
$order_id = $_GET['order_id']; // Assuming the order_id is passed via GET

// Fetch order information
$order_query = "SELECT * FROM orders WHERE order_id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();

// Fetch order details
$order_details_query = "SELECT * FROM order_details WHERE order_id = ?";
$stmt = $conn->prepare($order_details_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_details_result = $stmt->get_result();

// Fetch user information
$user_query = "SELECT * FROM user WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $order['user_id']);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Handle status update
if (isset($_POST['update_status'])) {
    $new_status = $_POST['order_status'];
    $update_status_query = "UPDATE orders SET order_status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($update_status_query);
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    header("Location: order_details.php?order_id=$order_id"); // Refresh the page
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            margin-left: 250px;
            padding: 20px;
            color: #333;
        }
        .container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 20px;
        }
        h2, h3 {
            color: #444;
            margin-bottom: 20px;
        }
        .order-header, .user-header, .form-container {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #f4f4f4;
            color: #333;
            font-size: 14px;
        }
        table td {
            font-size: 15px;
            color: #555;
        }
        table tr:hover {
            background-color: #f1f1f1;
        }
        .order-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-dropdown {
            width: 180px;
            padding: 8px;
            font-size: 14px;
        }
        .form-container button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        .form-container button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Order Information -->
        <div class="order-header">
            <h2>Order Information</h2>
            <p><strong>Order ID:</strong> #<?= $order['order_id']; ?></p>
            <p><strong>Order Date:</strong> <?= $order['order_date']; ?></p>
            <p><strong>Shipping Address:</strong> <?= $order['shipping_address']; ?></p>
            <p><strong>Total Amount:</strong> RM <?= number_format($order['final_amount'], 2); ?></p>
        </div>

        <!-- Order Details -->
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
                    <?php while ($detail = $order_details_result->fetch_assoc()) : ?>
                        <tr>
                            <td><?= $detail['product_name']; ?></td>
                            <td><?= $detail['quantity']; ?></td>
                            <td>RM <?= number_format($detail['unit_price'], 2); ?></td>
                            <td>RM <?= number_format($detail['total_price'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- User Information -->
        <div class="user-header">
            <h3>User Information</h3>
            <p><strong>User Name:</strong> <?= $user['user_name']; ?></p>
            <p><strong>Email:</strong> <?= $user['user_email']; ?></p>
            <p><strong>Contact:</strong> <?= $user['user_contact_number']; ?></p>
        </div>

        <!-- Update Order Status -->
        <div class="form-container">
            <h3>Update Order Status</h3>
            <form method="POST">
                <div class="order-status">
                    <label for="order_status">Order Status:</label>
                    <select name="order_status" id="order_status" class="status-dropdown">
                        <option value="Processing" <?= $order['order_status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="Shipping" <?= $order['order_status'] == 'Shipping' ? 'selected' : ''; ?>>Shipping</option>
                        <option value="Complete" <?= $order['order_status'] == 'Complete' ? 'selected' : ''; ?>>Complete</option>
                    </select>
                </div>
                <button type="submit" name="update_status">Update Status</button>
            </form>
        </div>
    </div>
</body>
</html>

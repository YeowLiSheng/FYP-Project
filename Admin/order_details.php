<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

// Fetch orders with user and order details
$query = "SELECT o.*, u.user_name, u.user_email, u.user_contact_number, u.user_gender, 
                 (SELECT GROUP_CONCAT(od.product_name, ' (Qty: ', od.quantity, ')')
                  FROM order_details od WHERE od.order_id = o.order_id) AS product_details
          FROM orders o
          INNER JOIN user u ON o.user_id = u.user_id";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Order Management</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 20px;
            max-width: 1200px;
            margin: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        .btn {
            padding: 8px 12px;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .status {
            padding: 5px;
            border-radius: 4px;
            text-align: center;
        }

        .status.Processing {
            background-color: #ffc107;
            color: #fff;
        }

        .status.Shipping {
            background-color: #17a2b8;
            color: #fff;
        }

        .status.Complete {
            background-color: #28a745;
            color: #fff;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Manage Orders</h1>
    <table>
        <thead>
        <tr>
            <th>Order ID</th>
            <th>User Name</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Gender</th>
            <th>Order Date</th>
            <th>Final Amount</th>
            <th>Shipping Address</th>
            <th>Product Details</th>
            <th>Order Status</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $row['order_id'] ?></td>
                <td><?= $row['user_name'] ?></td>
                <td><?= $row['user_email'] ?></td>
                <td><?= $row['user_contact_number'] ?></td>
                <td><?= $row['user_gender'] ?></td>
                <td><?= date('Y-m-d H:i', strtotime($row['order_date'])) ?></td>
                <td>RM <?= number_format($row['final_amount'], 2) ?></td>
                <td><?= $row['shipping_address'] ?></td>
                <td><?= $row['product_details'] ?></td>
                <td><span class="status <?= $row['order_status'] ?>"><?= $row['order_status'] ?></span></td>
                <td>
                    <form method="post" action="update_order_status.php">
                        <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                        <select name="order_status">
                            <option value="Processing" <?= $row['order_status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="Shipping" <?= $row['order_status'] === 'Shipping' ? 'selected' : '' ?>>Shipping</option>
                            <option value="Complete" <?= $row['order_status'] === 'Complete' ? 'selected' : '' ?>>Complete</option>
                        </select>
                        <button type="submit" class="btn">Update</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>

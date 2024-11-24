<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

// Fetch orders and details
$sql = "SELECT o.*, u.user_name, u.user_email, u.user_contact_number, u.user_image, 
        GROUP_CONCAT(CONCAT(d.product_name, ' (x', d.quantity, ')') SEPARATOR '<br>') AS products
        FROM orders o
        JOIN user u ON o.user_id = u.user_id
        JOIN order_details d ON o.order_id = d.order_id
        GROUP BY o.order_id";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <link rel="stylesheet" href="styles/admin_styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .main-content {
            margin-left: 250px; /* Adjust for sidebar */
            padding: 20px;
        }
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .order-table th, .order-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .order-table th {
            background-color: #6c63ff;
            color: #fff;
        }
        .update-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .update-form select {
            padding: 5px 10px;
        }
        .update-form button {
            padding: 5px 15px;
            background-color: #6c63ff;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .update-form button:hover {
            background-color: #5b55d1;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 10px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .user-info img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <h1>Order Management</h1>

        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="user-info">
                <img src="<?= htmlspecialchars($row['user_image']) ?>" alt="User Image">
                <div>
                    <strong><?= htmlspecialchars($row['user_name']) ?></strong><br>
                    Email: <?= htmlspecialchars($row['user_email']) ?><br>
                    Contact: <?= htmlspecialchars($row['user_contact_number']) ?>
                </div>
            </div>

            <table class="order-table">
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Products</th>
                    <th>Grand Total</th>
                    <th>Final Amount</th>
                    <th>Order Status</th>
                </tr>
                <tr>
                    <td><?= htmlspecialchars($row['order_id']) ?></td>
                    <td><?= htmlspecialchars($row['order_date']) ?></td>
                    <td><?= $row['products'] ?></td>
                    <td>RM <?= htmlspecialchars($row['Grand_total']) ?></td>
                    <td>RM <?= htmlspecialchars($row['final_amount']) ?></td>
                    <td>
                        <form class="update-form" action="update_order_status.php" method="post">
                            <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                            <select name="order_status">
                                <option value="Processing" <?= $row['order_status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="Shipping" <?= $row['order_status'] == 'Shipping' ? 'selected' : '' ?>>Shipping</option>
                                <option value="Complete" <?= $row['order_status'] == 'Complete' ? 'selected' : '' ?>>Complete</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            </table>
        <?php endwhile; ?>

    </div>
</body>
</htm
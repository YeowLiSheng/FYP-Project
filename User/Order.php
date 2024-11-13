<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .order-summary {
            border: 1px solid #ddd;
            margin: 15px;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            cursor: pointer;
            background-color: #f9f9f9;
        }
        .order-summary img {
            width: 100px;
            height: auto;
            margin-right: 15px;
        }
        .order-info {
            flex: 1;
        }
        .order-total {
            font-size: 18px;
            color: #ff5722;
            font-weight: bold;
        }
        .order-status {
            color: green;
            font-weight: bold;
        }
        .details-container {
            display: none;
            padding: 15px;
            margin-top: 10px;
            border-top: 1px solid #ddd;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table th, .details-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .details-table th {
            background-color: #f2f2f2;
        }
        .action-buttons {
            margin-top: 10px;
        }
        .action-buttons button {
            padding: 8px 12px;
            margin-right: 10px;
            cursor: pointer;
        }
        .rate-button {
            background-color: #ff5722;
            color: white;
            border: none;
        }
        .refund-button {
            background-color: #ccc;
            border: none;
        }
    </style>
    <script>
        function toggleDetails(orderId) {
            const details = document.getElementById('details-' + orderId);
            if (details.style.display === 'none' || details.style.display === '') {
                details.style.display = 'block';
            } else {
                details.style.display = 'none';
            }
        }
    </script>
</head>
<body>

<h1>Order History</h1>

<?php
// Connect to database
$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = 36; // User ID for demonstration purposes
$order_sql = "SELECT * FROM orders WHERE user_id = $user_id";
$order_result = $conn->query($order_sql);

if ($order_result->num_rows > 0) {
    while ($order = $order_result->fetch_assoc()) {
        $order_id = $order["order_id"];
        $order_status = ($order["order_status"] == 'Complete') ? 'Completed' : 'Processing';

        echo "<div class='order-summary' onclick='toggleDetails($order_id)'>";
        echo "<img src='path/to/product-image.jpg' alt='Product Image'>"; // Replace with dynamic image path
        echo "<div class='order-info'>";
        echo "<h3>" . $order["shipping_address"] . "</h3>";
        echo "<p>Order Date: " . $order["order_date"] . "</p>";
        echo "<p class='order-status'>$order_status</p>";
        echo "<p class='order-total'>RM" . $order["final_amount"] . "</p>";
        echo "</div>";
        echo "</div>";

        echo "<div id='details-$order_id' class='details-container'>";
        $detail_sql = "SELECT * FROM order_details WHERE order_id = $order_id";
        $detail_result = $conn->query($detail_sql);

        if ($detail_result->num_rows > 0) {
            echo "<table class='details-table'>";
            echo "<tr><th>Product Name</th><th>Quantity</th><th>Unit Price</th><th>Total Price</th></tr>";
            while ($detail = $detail_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $detail["product_name"] . "</td>";
                echo "<td>" . $detail["quantity"] . "</td>";
                echo "<td>RM" . $detail["unit_price"] . "</td>";
                echo "<td>RM" . $detail["total_price"] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        echo "<div class='action-buttons'>";
        echo "<button class='rate-button'>Rate</button>";
        echo "<button class='refund-button'>Request For Return/Refund</button>";
        echo "</div>";
        echo "</div>";
    }
} else {
    echo "<p>No orders found.</p>";
}

$conn->close();
?>

</body>
</html>

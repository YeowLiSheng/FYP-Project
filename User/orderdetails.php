<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

// 连接数据库
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 检查是否登录
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

// Retrieve the user information
$user_id = $_SESSION['id'];

// 使用预处理语句来防止 SQL 注入
$stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

// 获取用户信息
if ($user_result && $user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
} else {
    echo "User not found.";
    exit;
}

// 获取订单 ID
if (!isset($_GET['order_id'])) {
    echo "Invalid order ID.";
    exit;
}



$order_id = intval($_GET['order_id']);

// 使用预处理语句获取订单信息
$order_stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.Grand_total, o.discount_amount, o.delivery_charge,
           o.final_amount, o.order_status, o.shipping_address, o.shipping_method, o.user_message,
           u.user_name
    FROM orders o
    JOIN user u ON o.user_id = u.user_id
    WHERE o.order_id = ?
");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    echo "Order not found.";
    exit;
}

$order = $order_result->fetch_assoc();

// 获取订单详情
$details_stmt = $conn->prepare("
    SELECT od.product_id, od.product_name, od.quantity, od.unit_price, od.total_price, p.product_image
    FROM order_details od
    JOIN product p ON od.product_id = p.product_id
    WHERE od.order_id = ?
");
$details_stmt->bind_param("i", $order_id);
$details_stmt->execute();
$details_result = $details_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Details</title>
<link rel="stylesheet" href="styles.css">
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5;
        margin: 0;
        padding: 20px;
    }
    .order-details-container {
        max-width: 800px;
        margin: auto;
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .order-summary, .product-details, .pricing-details {
        margin-bottom: 20px;
    }
    .order-summary h2, .product-details h2, .pricing-details h2 {
        border-bottom: 2px solid #ddd;
        padding-bottom: 5px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }
    th, td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    th {
        background-color: #f8f8f8;
    }
    img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
    }
    .rate-comment {
        margin-top: 10px;
        display: flex;
        align-items: center;
    }
    .stars {
        cursor: pointer;
        color: #ffcc00;
        margin-right: 10px;
    }
    .stars span {
        font-size: 20px;
    }
    .comment-box {
        width: calc(100% - 120px);
        padding: 5px;
        margin-right: 5px;
    }
    .submit-review {
        padding: 5px 10px;
        background-color: #28a745;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .submit-review:hover {
        background-color: #218838;
    }
</style>
</head>
<body>
<div class="order-details-container">
    <h1>Order Details #<?= $order['order_id'] ?></h1>

    <!-- Order Summary -->
    <div class="order-summary">
        <h2>Order Summary</h2>
        <p><strong>User:</strong> <?= $order['user_name'] ?></p>
        <p><strong>Order Date:</strong> <?= date("Y-m-d H:i:s", strtotime($order['order_date'])) ?></p>
        <p><strong>Order Status:</strong> <?= $order['order_status'] ?></p>
        <p><strong>Shipping Address:</strong> <?= $order['shipping_address'] ?></p>
        <p><strong>Shipping Method:</strong> <?= $order['shipping_method'] ?></p>
    </div>

    <!-- Product Details -->
    <div class="product-details">
        <h2>Product Details</h2>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($detail = $details_result->fetch_assoc()) { ?>
                <tr>
                    <td><img src="images/<?= $detail['product_image'] ?>" alt="<?= $detail['product_name'] ?>"></td>
                    <td><?= $detail['product_name'] ?></td>
                    <td><?= $detail['quantity'] ?></td>
                    <td>RM <?= number_format($detail['unit_price'], 2) ?></td>
                    <td>RM <?= number_format($detail['total_price'], 2) ?></td>
                </tr>
                <tr>
                    <td colspan="5">
                        <div class="rate-comment">
                            <div class="stars" data-product-id="<?= $detail['product_id'] ?>">
                                <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                            </div>
                            <input type="text" class="comment-box" placeholder="Leave a comment">
                            <button class="submit-review" onclick="submitReview(<?= $detail['product_id'] ?>)">Submit Review</button>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Pricing Details -->
    <div class="pricing-details">
        <h2>Pricing Details</h2>
        <p><strong>Grand Total:</strong> RM <?= number_format($order['Grand_total'], 2) ?></p>
        <p><strong>Discount:</strong> RM <?= number_format($order['discount_amount'], 2) ?></p>
        <p><strong>Delivery Charge:</strong> RM <?= number_format($order['delivery_charge'], 2) ?></p>
        <p><strong>Final Amount:</strong> RM <?= number_format($order['final_amount'], 2) ?></p>
    </div>

    <!-- Print Receipt Button -->
    <button onclick="window.location.href='receipt.php?order_id=<?= $order['order_id'] ?>'">Print Receipt</button>
</div>

<script>
    // 提交评分和评论
    function submitReview(productId) {
        const stars = document.querySelector(`.stars[data-product-id="${productId}"]`);
        const commentBox = stars.nextElementSibling;
        const comment = commentBox.value.trim();
        
        if (comment === "") {
            alert("Please leave a comment.");
            return;
        }

        const rating = stars.querySelectorAll(".selected").length;
        alert(`Review submitted for product ${productId} with rating ${rating} and comment: ${comment}`);
        
        // 这里可以添加 AJAX 提交评论功能
    }

    // 星级评分交互
    document.querySelectorAll('.stars').forEach(starContainer => {
        const stars = starContainer.querySelectorAll('span');
        stars.forEach((star, index) => {
            star.addEventListener('click', () => {
                stars.forEach((s, i) => {
                    s.classList.toggle('selected', i <= index);
                });
            });
        });
    });
</script>
</body>
</html>

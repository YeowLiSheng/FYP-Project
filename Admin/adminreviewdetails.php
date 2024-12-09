<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

// 检查产品 ID 是否存在
if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
    echo "<p>Error: Product not found.</p>";
    exit;
}

$product_id = intval($_GET['product_id']);

// 获取产品信息
$product_query = "SELECT product_name, product_image FROM product WHERE product_id = ?";
$stmt = $connect->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_result = $stmt->get_result();

if ($product_result->num_rows === 0) {
    echo "<p>Error: Product not found.</p>";
    exit;
}

$product = $product_result->fetch_assoc();

// 获取评论
$review_query = "
    SELECT 
        r.review_id, 
        r.rating, 
        r.comment, 
        r.image AS review_image, 
        r.admin_reply, 
        u.user_image, 
        u.user_name 
    FROM reviews r 
    INNER JOIN users u ON r.user_id = u.user_id 
    INNER JOIN order_details od ON r.detail_id = od.detail_id 
    WHERE od.product_id = ? 
    AND r.status = 'active'
    ORDER BY r.created_at DESC
";

$stmt = $connect->prepare($review_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$reviews = $stmt->get_result();

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $review_id = intval($_POST['review_id']);
    $admin_reply = trim($_POST['admin_reply'] ?? '');

    if (isset($_POST['reply'])) {
        $update_query = "UPDATE reviews SET admin_reply = ? WHERE review_id = ?";
        $stmt = $connect->prepare($update_query);
        $stmt->bind_param("si", $admin_reply, $review_id);
        $stmt->execute();
        header("Location: admin_productreview.php?product_id=$product_id");
        exit;
    }

    if (isset($_POST['deactivate'])) {
        $deactivate_query = "UPDATE reviews SET status = 'inactive' WHERE review_id = ?";
        $stmt = $connect->prepare($deactivate_query);
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
        header("Location: admin_productreview.php?product_id=$product_id");
        exit;
    }
}
?>

<!-- 页面内容 -->
<div class="main">
    <h1><ion-icon name="chatbubbles-outline"></ion-icon> Product Reviews</h1>

    <div class="product-info">
        <img src="../User/images/<?= htmlspecialchars($product['product_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
        <h2><?= htmlspecialchars($product['product_name']) ?></h2>
    </div>

    <div class="review-container">
        <?php if ($reviews->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $reviews->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <img src="../User/images/<?= htmlspecialchars($row['user_image']) ?>" alt="<?= htmlspecialchars($row['user_name']) ?>" style="width: 50px;">
                                <p><?= htmlspecialchars($row['user_name']) ?></p>
                            </td>
                            <td><?= htmlspecialchars($row['rating']) ?> <ion-icon name="star-outline"></ion-icon></td>
                            <td>
                                <p><?= htmlspecialchars($row['comment']) ?></p>
                                <?php if (!empty($row['review_image'])): ?>
                                    <img src="../User/images/<?= htmlspecialchars($row['review_image']) ?>" alt="Review Image" style="width: 100px;">
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="review_id" value="<?= $row['review_id'] ?>">
                                    <textarea name="admin_reply" placeholder="Write your reply..."><?= htmlspecialchars($row['admin_reply']) ?></textarea>
                                    <button type="submit" name="reply">Reply</button>
                                    <button type="submit" name="deactivate" class="danger">Deactivate</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No reviews available for this product.</p>
        <?php endif; ?>
    </div>
</div>

<!-- 样式 -->
<style>
.main {
    padding: 20px;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 20px;
}

.product-info img {
    width: 150px;
    border-radius: 8px;
}

.review-container {
    margin-top: 30px;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th, .table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

textarea {
    width: 100%;
    height: 60px;
    margin-bottom: 10px;
}

button {
    padding: 8px 16px;
    border: none;
    cursor: pointer;
}

button.danger {
    background-color: #e74c3c;
    color: white;
}

button[type="submit"] {
    background-color: #3498db;
    color: white;
    margin-right: 5px;
}

img {
    max-width: 100px;
    height: auto;
}
</style>

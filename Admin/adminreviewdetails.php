<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

// 获取产品 ID
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id > 0) {
    // 查询产品信息
    $product_query = "
        SELECT product_name, product_image
        FROM product
        WHERE product_id = $product_id
    ";
    $product_result = $connect->query($product_query);

    if ($product_result && $product_result->num_rows > 0) {
        $product = $product_result->fetch_assoc();
    } else {
        echo "<script>alert('Product not found.'); window.location.href = 'admin_review.php';</script>";
        exit;
    }

    // 查询评论信息
    $reviews_query = "
        SELECT 
            r.review_id, 
            r.rating, 
            r.comment, 
            r.image AS review_image, 
            r.admin_reply, 
            u.user_name, 
            u.user_image, 
            r.created_at 
        FROM reviews r
        INNER JOIN users u ON r.user_id = u.user_id
        INNER JOIN order_details od ON r.detail_id = od.detail_id
        WHERE od.product_id = $product_id 
          AND r.status = 'active'
        ORDER BY r.created_at DESC
    ";
    $reviews_result = $connect->query($reviews_query);

    if (!$reviews_result) {
        echo "<script>alert('Failed to fetch reviews. SQL Error: {$connect->error}');</script>";
        $reviews_result = null;
    }
} else {
    echo "<script>alert('Invalid product ID'); window.location.href = 'admin_review.php';</script>";
    exit;
}

// 提交管理员回复
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id']) && isset($_POST['admin_reply'])) {
    $review_id = intval($_POST['review_id']);
    $admin_reply = $connect->real_escape_string($_POST['admin_reply']);

    $query = "UPDATE reviews SET admin_reply = '$admin_reply' WHERE review_id = $review_id";
    if ($connect->query($query)) {
        echo "<script>alert('Reply submitted successfully.'); window.location.href = window.location.href;</script>";
    } else {
        echo "<script>alert('Failed to submit reply.');</script>";
    }
}

// 禁用评论
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deactivate_review_id'])) {
    $review_id = intval($_POST['deactivate_review_id']);

    $query = "UPDATE reviews SET status = 'inactive' WHERE review_id = $review_id";
    if ($connect->query($query)) {
        echo "<script>alert('Review deactivated successfully.'); window.location.href = window.location.href;</script>";
    } else {
        echo "<script>alert('Failed to deactivate review.');</script>";
    }
}
?>

<div class="main">
    <h1><ion-icon name="chatbubbles-outline"></ion-icon> Product Reviews</h1>

    <?php if ($product): ?>
        <div class="product-info">
            <img src="../User/images/<?= htmlspecialchars($product['product_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" style="width: 150px; height: auto;">
            <h2><?= htmlspecialchars($product['product_name']) ?></h2>
        </div>
    <?php endif; ?>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th><ion-icon name="person-outline"></ion-icon> User</th>
                    <th><ion-icon name="star-outline"></ion-icon> Rating</th>
                    <th><ion-icon name="chatbubble-outline"></ion-icon> Comment</th>
                    <th><ion-icon name="image-outline"></ion-icon> Photo</th>
                    <th><ion-icon name="chatbubble-ellipses-outline"></ion-icon> Admin Reply</th>
                    <th><ion-icon name="close-circle-outline"></ion-icon> Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($reviews_result && $reviews_result->num_rows > 0): ?>
                    <?php while ($row = $reviews_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <img src="../User/images/<?= htmlspecialchars($row['user_image']) ?>" alt="<?= htmlspecialchars($row['user_name']) ?>" style="width: 50px; height: 50px; border-radius: 50%;">
                                <br><?= htmlspecialchars($row['user_name']) ?>
                            </td>
                            <td><?= htmlspecialchars($row['rating']) ?>/5</td>
                            <td><?= nl2br(htmlspecialchars($row['comment'])) ?></td>
                            <td>
                                <?php if ($row['review_image']): ?>
                                    <img src="../User/review_images/<?= htmlspecialchars($row['review_image']) ?>" alt="Review Image" style="width: 100px; height: auto;">
                                <?php else: ?>
                                    No image
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST">
                                    <textarea name="admin_reply" rows="3" style="width: 100%;"><?= htmlspecialchars($row['admin_reply']) ?></textarea>
                                    <input type="hidden" name="review_id" value="<?= $row['review_id'] ?>">
                                    <button type="submit" style="margin-top: 5px;">Submit Reply</button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to deactivate this review?');">
                                    <input type="hidden" name="deactivate_review_id" value="<?= $row['review_id'] ?>">
                                    <button type="submit" style="color: red;">Deactivate</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No reviews found for this product.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

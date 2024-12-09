<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // 查询指定产品的评论
    $query = "
        SELECT 
            r.review_id,
            r.rating,
            r.comment,
            r.image,
            r.status,
            r.admin_reply,
            r.created_at,
            u.user_name
        FROM reviews r
        INNER JOIN order_details od ON r.detail_id = od.detail_id
        INNER JOIN orders o ON od.order_id = o.order_id
        INNER JOIN users u ON r.user_id = u.user_id
        WHERE od.product_id = ? 
        ORDER BY r.created_at DESC
    ";
    $stmt = $connect->prepare($query);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    echo "Invalid product ID.";
    exit();
}

// 禁用评论功能
if (isset($_POST['deactivate_review'])) {
    $review_id = $_POST['review_id'];
    $update_query = "UPDATE reviews SET status = 'inactive' WHERE review_id = ?";
    $stmt = $connect->prepare($update_query);
    $stmt->bind_param('i', $review_id);
    $stmt->execute();
    header("Location: adminreviewdetails.php?product_id=$product_id");
}

// 回复用户评论
if (isset($_POST['reply_review'])) {
    $review_id = $_POST['review_id'];
    $admin_reply = $_POST['admin_reply'];
    $update_query = "UPDATE reviews SET admin_reply = ? WHERE review_id = ?";
    $stmt = $connect->prepare($update_query);
    $stmt->bind_param('si', $admin_reply, $review_id);
    $stmt->execute();
    header("Location: adminreviewdetails.php?product_id=$product_id");
}
?>

<div class="main">
    <h1>Product Reviews</h1>
    <div class="reviews-container">
        <?php if ($result->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Image</th>
                        <th>Status</th>
                        <th>Admin Reply</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['user_name']) ?></td>
                            <td><?= htmlspecialchars($row['rating']) ?></td>
                            <td><?= htmlspecialchars($row['comment']) ?></td>
                            <td>
                                <?php if ($row['image']): ?>
                                    <img src="../User/images/<?= htmlspecialchars($row['image']) ?>" alt="Review Image" style="width: 50px;">
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td><?= htmlspecialchars($row['admin_reply']) ?: 'No Reply' ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="review_id" value="<?= $row['review_id'] ?>">
                                    <button type="submit" name="deactivate_review" class="btn btn-danger">Deactivate</button>
                                </form>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="review_id" value="<?= $row['review_id'] ?>">
                                    <input type="text" name="admin_reply" placeholder="Reply">
                                    <button type="submit" name="reply_review" class="btn btn-primary">Reply</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No reviews found for this product.</p>
        <?php endif; ?>
    </div>
</div>

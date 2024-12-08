<?php include 'admin_sidebar.php';
include 'dataconnection.php';

// 获取所有评论
$review_query = "
    SELECT 
        r.review_id,
        r.comment,
        r.rating,
        r.image,
        r.created_at,
        r.status,
        r.admin_reply,
        u.user_name,
        p.product_name
    FROM 
        reviews r
    JOIN 
        user u ON r.user_id = u.user_id
    JOIN 
        order_details od ON r.detail_id = od.detail_id
    JOIN 
        product p ON od.product_id = p.product_id
";
$reviews = $conn->query($review_query);

// 处理删除请求
if (isset($_POST['delete_review'])) {
    $review_id = intval($_POST['review_id']);
    $delete_query = "UPDATE reviews SET status = 'inactive' WHERE review_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $review_id);
    $stmt->execute();
    header("Location: admin_review.php");
    exit;
}

// 处理回复请求
if (isset($_POST['reply_review'])) {
    $review_id = intval($_POST['review_id']);
    $reply = $conn->real_escape_string($_POST['admin_reply']);
    $reply_query = "UPDATE reviews SET admin_reply = ? WHERE review_id = ?";
    $stmt = $conn->prepare($reply_query);
    $stmt->bind_param("si", $reply, $review_id);
    $stmt->execute();
    header("Location: admin_review.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Review Management</title>
</head>
<body>
    <h1>Review Management</h1>
    <table border="1">
        <thead>
            <tr>
                <th>User Name</th>
                <th>Product</th>
                <th>Comment</th>
                <th>Rating</th>
                <th>Status</th>
                <th>Admin Reply</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($review = $reviews->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($review['user_name']); ?></td>
                    <td><?php echo htmlspecialchars($review['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($review['comment']); ?></td>
                    <td><?php echo htmlspecialchars($review['rating']); ?></td>
                    <td><?php echo htmlspecialchars($review['status']); ?></td>
                    <td><?php echo htmlspecialchars($review['admin_reply'] ?? 'No reply'); ?></td>
                    <td>
                        <?php if ($review['status'] === 'active') { ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">
                                <button type="submit" name="delete_review">Delete</button>
                            </form>
                        <?php } ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">
                            <input type="text" name="admin_reply" placeholder="Reply to review">
                            <button type="submit" name="reply_review">Reply</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>

<?php
// 开启错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 数据库连接
include 'dataconnection.php';

if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
    die("Product ID is missing.");
}

$product_id = $connect->real_escape_string($_GET['product_id']);

// 查询产品信息及其评论
$product_query = "
    SELECT product_name, product_image 
    FROM product 
    WHERE product_id = '$product_id'
";
$product_result = $connect->query($product_query);
$product = $product_result->fetch_assoc();

$review_query = "
    SELECT 
        r.review_id, r.rating, r.comment, r.image, r.admin_reply, r.created_at, r.status,
        u.user_name, u.user_image
    FROM reviews r
    INNER JOIN users u ON r.user_id = u.user_id
    INNER JOIN order_details od ON r.detail_id = od.detail_id
    WHERE od.product_id = '$product_id'
    ORDER BY r.created_at DESC
";

$review_result = $connect->query($review_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Review Details</title>
</head>
<body>
    <h1>Reviews for <?= htmlspecialchars($product['product_name']) ?></h1>

    <img src="../User/images/<?= htmlspecialchars($product['product_image']) ?>" 
         alt="<?= htmlspecialchars($product['product_name']) ?>" width="150">

    <?php if ($review_result->num_rows > 0): ?>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Photo</th>
                    <th>Admin Reply</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $review_result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <img src="../User/images/<?= htmlspecialchars($row['user_image']) ?>" 
                                 alt="User Image" width="50">
                            <?= htmlspecialchars($row['user_name']) ?>
                        </td>
                        <td><?= htmlspecialchars($row['rating']) ?></td>
                        <td><?= htmlspecialchars($row['comment']) ?></td>
                        <td>
                            <?php if ($row['image']): ?>
                                <img src="../User/images/<?= htmlspecialchars($row['image']) ?>" width="100">
                            <?php else: ?>
                                No Photo
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['admin_reply']) ?: 'Not replied yet' ?>
                            <form method="post" action="submit_review_reply.php">
                                <input type="hidden" name="review_id" value="<?= $row['review_id'] ?>">
                                <textarea name="admin_reply" required></textarea>
                                <button type="submit">Submit Reply</button>
                            </form>
                        </td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td>
                            <form method="post" action="deactivate_review.php" 
                                  onsubmit="return confirm('Are you sure you want to deactivate this review?')">
                                <input type="hidden" name="review_id" value="<?= $row['review_id'] ?>">
                                <button type="submit">Deactivate</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No reviews found for this product.</p>
    <?php endif; ?>
</body>
</html>

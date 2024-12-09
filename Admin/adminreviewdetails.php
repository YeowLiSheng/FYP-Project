<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

// 获取产品 ID
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

// 查询产品评论
$query = "
    SELECT 
        r.review_id,
        r.rating,
        r.comment,
        r.image,
        r.status,
        r.admin_reply,
        r.created_at,
        u.username AS user_name
    FROM reviews r
    INNER JOIN order_details od ON r.detail_id = od.detail_id
    INNER JOIN users u ON r.user_id = u.user_id
    WHERE od.product_id = $product_id
    ORDER BY r.created_at DESC
";
$reviewResult = $connect->query($query);
?>

<div class="main">
    <h1><ion-icon name="chatbubble-outline"></ion-icon> Product Reviews</h1>
    
    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Review ID</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Image</th>
                    <th>Status</th>
                    <th>Admin Reply</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($reviewResult->num_rows > 0): ?>
                    <?php while ($row = $reviewResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['review_id'] ?></td>
                            <td><?= $row['rating'] ?> / 5</td>
                            <td><?= htmlspecialchars($row['comment']) ?></td>
                            <td>
                                <?php if ($row['image']): ?>
                                    <img src="../User/images/<?= $row['image'] ?>" alt="Review Image" style="width: 50px; height: auto;">
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                            <td><?= ucfirst($row['status']) ?></td>
                            <td><?= htmlspecialchars($row['admin_reply']) ?: 'No Reply Yet' ?></td>
                            <td>
                                <form method="post" style="display: inline-block;">
                                    <input type="hidden" name="review_id" value="<?= $row['review_id'] ?>">
                                    <?php if ($row['status'] === 'active'): ?>
                                        <button type="submit" name="deactivate" class="btn btn-danger">Deactivate</button>
                                    <?php else: ?>
                                        <button type="submit" name="activate" class="btn btn-success">Activate</button>
                                    <?php endif; ?>
                                </form>
                                <button onclick="replyReview(<?= $row['review_id'] ?>)" class="btn btn-primary">Reply</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">No reviews found for this product.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function replyReview(reviewId) {
    const reply = prompt("Enter your reply:");
    if (reply) {
        window.location.href = `adminreviewdetails.php?product_id=<?= $product_id ?>&review_id=${reviewId}&reply=${encodeURIComponent(reply)}`;
    }
}
</script>

<?php
// 激活或禁用评论
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $review_id = intval($_POST['review_id']);
    if (isset($_POST['deactivate'])) {
        $connect->query("UPDATE reviews SET status = 'inactive' WHERE review_id = $review_id");
    } elseif (isset($_POST['activate'])) {
        $connect->query("UPDATE reviews SET status = 'active' WHERE review_id = $review_id");
    }
    header("Location: adminreviewdetails.php?product_id=$product_id");
    exit;
}

// 回复评论
if (isset($_GET['reply']) && isset($_GET['review_id'])) {
    $review_id = intval($_GET['review_id']);
    $reply = $connect->real_escape_string($_GET['reply']);
    $connect->query("UPDATE reviews SET admin_reply = '$reply' WHERE review_id = $review_id");
    header("Location: adminreviewdetails.php?product_id=$product_id");
    exit;
}
?>

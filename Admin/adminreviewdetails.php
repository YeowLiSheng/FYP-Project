<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

$product_id = $_GET['product_id'] ?? 0;

// 查询产品信息
$product_query = "SELECT product_name, product_image FROM product WHERE product_id = ?";
$stmt = $connect->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

// 查询产品评论
$review_query = "
    SELECT r.review_id, r.rating, r.comment, r.image AS review_image, r.created_at, 
           u.user_name, u.user_image, r.admin_reply, r.status
    FROM reviews r 
    INNER JOIN user u ON r.user_id = u.user_id 
    WHERE r.detail_id IN (
        SELECT detail_id FROM order_details WHERE product_id = ?
    )
    ORDER BY r.created_at DESC
";
$stmt = $connect->prepare($review_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$reviews = $stmt->get_result();

// 处理管理员操作
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $review_id = $_POST['review_id'];
    if (isset($_POST['reply'])) {
        $admin_reply = trim($_POST['admin_reply']);
        $status = $_POST['status'];
        $query = "UPDATE reviews SET admin_reply = ?, status = ? WHERE review_id = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("ssi", $admin_reply, $status, $review_id);
        $stmt->execute();
    }
    header("Location: admin_productreview.php?product_id=$product_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Product Review</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
<div class="main">
    <h1><ion-icon name="chatbubbles-outline"></ion-icon> Product Reviews</h1>

    <div class="product-info">
        <img src="../User/images/<?= htmlspecialchars($product['product_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" class="product-image">
        <h2><?= htmlspecialchars($product['product_name']) ?></h2>
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Review Image</th>
                    <th>Status</th>
                    <th>Admin Reply</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($reviews->num_rows > 0): ?>
                    <?php while ($row = $reviews->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <img src="../User/images/<?= htmlspecialchars($row['user_image']) ?>" alt="<?= htmlspecialchars($row['user_name']) ?>" class="user-image">
                                <p><?= htmlspecialchars($row['user_name']) ?></p>
                            </td>
                            <td><?= htmlspecialchars($row['rating']) ?> / 5</td>
                            <td><?= nl2br(htmlspecialchars($row['comment'])) ?></td>
                            <td>
                                <?php if ($row['review_image']): ?>
                                    <img src="../User/images/<?= htmlspecialchars($row['review_image']) ?>" alt="Review Image" class="review-image">
                                <?php else: ?>
                                    <p>No Image</p>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="<?= $row['status'] == 'active' ? 'status-active' : 'status-inactive' ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['admin_reply']) ?: '<em>No reply yet</em>' ?>
                            </td>
                            <td>
                                <button class="btn btn-primary" onclick="openReplyForm(<?= $row['review_id'] ?>, '<?= htmlspecialchars($row['admin_reply']) ?>', '<?= $row['status'] ?>')">Reply/Edit</button>
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

<!-- Reply/Edit Form Modal -->
<div id="reply-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeReplyForm()">&times;</span>
        <h2>Reply/Edit Review</h2>
        <form method="post">
            <input type="hidden" name="review_id" id="modal-review-id">
            <textarea name="admin_reply" id="modal-admin-reply" placeholder="Enter your reply..." required></textarea>
            <label for="status">Status:</label>
            <select name="status" id="modal-status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <button type="submit" name="reply" class="btn btn-success">Save Changes</button>
        </form>
    </div>
</div>

<!-- 样式 -->
<style>
/* 布局样式 */
.main { padding: 20px; }

.product-info { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; }
.product-image, .user-image, .review-image { width: 100px; height: auto; border-radius: 8px; }

h2 { font-size: 24px; color: #333; }

/* 表格样式 */
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 15px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f4f4f4; }

/* 按钮样式 */
.btn { padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; }
.btn-primary { background-color: #007bff; color: white; }
.btn-success { background-color: #28a745; color: white; }

/* 状态样式 */
.status-active { color: green; font-weight: bold; }
.status-inactive { color: red; font-weight: bold; }

/* 弹窗样式 */
.modal { display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); border-radius: 8px; z-index: 1000; width: 400px; max-width: 90%; }
.modal-content { position: relative; }
.close-btn { position: absolute; top: 10px; right: 15px; font-size: 24px; cursor: pointer; }
.modal textarea { width: 100%; height: 100px; padding: 8px; margin-top: 10px; border: 1px solid #ccc; border-radius: 5px; }
.modal .btn-success { display: block; margin: 15px auto 0; }
</style>

<!-- JavaScript 功能 -->
<script>
function openReplyForm(reviewId, replyText, status) {
    document.getElementById("modal-review-id").value = reviewId;
    document.getElementById("modal-admin-reply").value = replyText || '';
    document.getElementById("modal-status").value = status || 'active';
    document.getElementById("reply-modal").style.display = "block";
}

function closeReplyForm() {
    document.getElementById("reply-modal").style.display = "none";
}
</script>
</body>
</html>

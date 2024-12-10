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
    ORDER BY r.created_at DESC";
$stmt = $connect->prepare($review_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$reviews = $stmt->get_result();

// 处理管理员操作
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $review_id = $_POST['review_id'];

    if (isset($_POST['submit_reply'])) {
        $admin_reply = trim($_POST['admin_reply']);
        $query = "UPDATE reviews SET admin_reply = ? WHERE review_id = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("si", $admin_reply, $review_id);
        $stmt->execute();
    }

    if (isset($_POST['toggle_status'])) {
        $status = $_POST['status'] == 'active' ? 'inactive' : 'active';
        $query = "UPDATE reviews SET status = ? WHERE review_id = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("si", $status, $review_id);
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
                <?php while ($row = $reviews->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <img src="../User/images/<?= htmlspecialchars($row['user_image']) ?>" class="user-image" alt="<?= htmlspecialchars($row['user_name']) ?>">
                            <p><?= htmlspecialchars($row['user_name']) ?></p>
                        </td>
                        <td><?= htmlspecialchars($row['rating']) ?> / 5</td>
                        <td><?= nl2br(htmlspecialchars($row['comment'])) ?></td>
                        <td>
                            <?= $row['review_image'] ? "<img src='../User/images/{$row['review_image']}' class='review-image'>" : "No Image" ?>
                        </td>
                        <td>
                            <span class="<?= $row['status'] == 'active' ? 'status-active' : 'status-inactive' ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?= nl2br(htmlspecialchars($row['admin_reply'] ?? 'No Reply')) ?>
                        </td>
                        <td>
                            <button class="btn btn-info" onclick="openReplyModal(<?= $row['review_id'] ?>, '<?= htmlspecialchars($row['admin_reply']) ?>')">Reply/Edit</button>
                            <form method="post" onsubmit="return confirm('Are you sure?')">
                                <input type="hidden" name="review_id" value="<?= $row['review_id'] ?>">
                                <input type="hidden" name="status" value="<?= $row['status'] ?>">
                                <button type="submit" name="toggle_status" class="btn btn-warning">
                                    <?= $row['status'] == 'active' ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 弹窗表单 -->
<div id="replyModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Reply to Review</h2>
        <form method="post">
            <input type="hidden" id="reviewIdInput" name="review_id">
            <textarea name="admin_reply" id="adminReplyInput" placeholder="Enter your reply..."></textarea>
            <button type="submit" name="submit_reply" class="btn btn-success">Submit</button>
        </form>
    </div>
</div>

<style>
.main { padding: 20px; }
.product-info { display: flex; gap: 20px; margin-bottom: 20px; }
.product-image, .user-image, .review-image { width: 100px; height: auto; border-radius: 8px; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 15px; border: 1px solid #ddd; }
th { background-color: #f4f4f4; }
.status-active { color: green; font-weight: bold; }
.status-inactive { color: red; font-weight: bold; }
.btn { padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; }
.btn-success { background-color: #28a745; color: white; }
.btn-warning { background-color: #ff9800; color: white; }
.btn-info { background-color: #17a2b8; color: white; }
.modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); }
.modal-content { background: white; padding: 20px; width: 400px; margin: 10% auto; border-radius: 8px; position: relative; }
.close { position: absolute; top: 10px; right: 15px; font-size: 24px; cursor: pointer; }
</style>

<script>
function openReplyModal(reviewId, adminReply) {
    document.getElementById('replyModal').style.display = 'block';
    document.getElementById('reviewIdInput').value = reviewId;
    document.getElementById('adminReplyInput').value = adminReply || '';
}

function closeModal() {
    document.getElementById('replyModal').style.display = 'none';
}
</script>
</body>
</html>

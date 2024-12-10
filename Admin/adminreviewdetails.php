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
        $query = "UPDATE reviews SET admin_reply = ? WHERE review_id = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("si", $admin_reply, $review_id);
        $stmt->execute();
    } elseif (isset($_POST['toggle_status'])) {
        $new_status = $_POST['new_status'];
        $query = "UPDATE reviews SET status = ? WHERE review_id = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("si", $new_status, $review_id);
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
    <style>
        .main { padding: 20px; }
        .product-info { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; }
        .product-image, .user-image, .review-image {
            width: auto;
            height: 100px;
            max-width: 100px;
            object-fit: contain;
            border-radius: 8px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 15px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f4f4f4; }
        .btn { padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-warning { background-color: #ffc107; color: black; }
        .status-active { color: green; font-weight: bold; }
        .status-inactive { color: red; font-weight: bold; }

        /* Redesigned Modal */
       /* 回复表单弹窗 */
       .modal {
    display: none;
    position: fixed;
    top: 50%; /* 页面垂直居中 */
    left: 50%; /* 页面水平居中 */
    transform: translate(-50%, -50%);
    width: 450px;
    max-width: 90%;
    background: none; /* 移除背景颜色 */
    padding: 0; /* 移除额外的内边距 */
    z-index: 1000;
}

.modal-content {
    text-align: center;
    background: rgba(0, 0, 0, 0.6); /* 半透明黑色背景 */
    padding: 20px;
    border-radius: 10px; /* 圆角 */
    color: white; /* 文本颜色设置为白色 */
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.3); /* 添加阴影 */
}

.modal h2 {
    margin-bottom: 20px;
    font-size: 24px;
    font-weight: 700;
    color: #fff;
}

.modal textarea {
    width: 100%;
    height: 150px;
    resize: none;
    padding: 15px;
    border: none; /* 移除边框 */
    border-radius: 10px;
    font-size: 14px;
    font-family: Arial, sans-serif;
    margin-bottom: 20px;
    background-color: rgba(255, 255, 255, 0.2); /* 半透明背景 */
    color: white; /* 文本颜色 */
    transition: box-shadow 0.3s ease, background-color 0.3s ease;
}

.modal textarea:focus {
    box-shadow: 0px 0px 8px rgba(255, 255, 255, 0.5); /* 聚焦效果 */
    outline: none;
    background-color: rgba(255, 255, 255, 0.3); /* 增加聚焦时的亮度 */
}

.modal button {
    padding: 12px 25px;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    background: linear-gradient(to right, #ff6b6b, #d63031);
    color: white;
    transition: background 0.3s ease;
}

.modal button:hover {
    background: linear-gradient(to right, #d63031, #ff6b6b);
    box-shadow: 0px 4px 10px rgba(255, 107, 107, 0.3);
}

.close-btn {
    position: absolute; /* 设置按钮在右上角 */
    top: 15px;
    right: 20px;
    font-size: 24px;
    font-weight: bold;
    color: white;
    cursor: pointer;
    transition: color 0.3s ease;
}

.close-btn:hover {
    color: #ff6b6b; /* 添加悬停颜色 */
}

        .image-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .image-modal img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            border-radius: 10px;
        }

        .image-modal .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            color: white;
            cursor: pointer;
        }
    </style>
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
                                <p><?= htmlspecialchars($row['user_name']) ?></p>
                            </td>
                            <td><?= htmlspecialchars($row['rating']) ?> / 5</td>
                            <td><?= nl2br(htmlspecialchars($row['comment'])) ?></td>
                            <td>
                                <?php if ($row['review_image']): ?>
                                    <img src="../User/<?= htmlspecialchars($row['review_image']) ?>" 
                                         alt="Review Image" 
                                         class="review-image" 
                                         style="cursor: pointer;"
                                         onclick="openImageModal('../User/<?= htmlspecialchars($row['review_image']) ?>')">
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
                                <button class="btn btn-primary" onclick="openReplyForm(<?= $row['review_id'] ?>, '<?= htmlspecialchars($row['admin_reply']) ?>')">Reply/Edit</button>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="review_id" value="<?= $row['review_id'] ?>">
                                    <input type="hidden" name="new_status" value="<?= $row['status'] == 'active' ? 'inactive' : 'active' ?>">
                                    <button type="submit" name="toggle_status" class="btn <?= $row['status'] == 'active' ? 'btn-warning' : 'btn-success' ?>">
                                        <?= $row['status'] == 'active' ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">No reviews found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Review Reply Modal -->
<div class="modal" id="replyModal">
    <div class="modal-content">
        <h2>Reply to Review</h2>
        <form method="post">
            <textarea id="replyTextarea" name="admin_reply" placeholder="Type your reply here..." required></textarea>
            <input type="hidden" name="review_id" id="reviewIdInput">
            <button type="submit" name="reply">Save changes</button>
        </form>
        <span class="close-btn" onclick="closeReplyForm()">&times;</span>
    </div>
</div>

<!-- Image Modal -->
<div class="image-modal" id="imageModal">
    <img src="" alt="Review Image" id="imagePreview">
    <span class="close-btn" onclick="closeImageModal()">&times;</span>
</div>

<script>
function openReplyForm(reviewId, currentReply) {
    document.getElementById('replyModal').style.display = 'block';
    document.getElementById('replyTextarea').value = currentReply || '';
    document.getElementById('reviewIdInput').value = reviewId;
}

function closeReplyForm() {
    document.getElementById('replyModal').style.display = 'none';
}

function openImageModal(imageUrl) {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'flex';
    document.getElementById('imagePreview').src = imageUrl;
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}
</script>
</body>
</html>

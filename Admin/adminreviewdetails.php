<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

$item_id = $_GET['item_id'] ?? 0;
$item_type = $_GET['item_type'] ?? 'product'; // 默认类型为 product

// 确定查询的表和列
if ($item_type === 'product') {
    $item_query = "SELECT product_name AS item_name, product_image AS item_image FROM product WHERE product_id = ?";
} elseif ($item_type === 'package') {
    $item_query = "SELECT package_name AS item_name, package_image AS item_image FROM product_package WHERE package_id = ?";
} else {
    die("Invalid item type.");
}

// 查询产品或套餐信息
$stmt = $connect->prepare($item_query);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

// 查询评论信息
$review_query = "
    SELECT r.review_id, r.rating, r.comment, r.image AS review_image, r.created_at, 
           u.user_name, u.user_image, r.admin_reply, r.status
    FROM reviews r 
    INNER JOIN user u ON r.user_id = u.user_id 
    WHERE r.detail_id IN (
        SELECT detail_id FROM order_details WHERE " . ($item_type === 'product' ? "product_id" : "package_id") . " = ?
    )
    ORDER BY r.created_at DESC
";
$stmt = $connect->prepare($review_query);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$reviews = $stmt->get_result();

// 处理管理员操作
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $review_id = $_POST['review_id'];
    $staff_id = $_SESSION['staff_id']; // 使用 login.php 中的键名

    if (isset($_POST['reply'])) {
        $admin_reply = trim($_POST['admin_reply']);
        $query = "UPDATE reviews 
                  SET admin_reply = ?, admin_reply_updated_at = CURRENT_TIMESTAMP, staff_id = ? 
                  WHERE review_id = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("sii", $admin_reply, $staff_id, $review_id);
        $stmt->execute();
        if (!$stmt->execute()) {
            die("SQL 执行失败：" . $stmt->error);
        }
    } elseif (isset($_POST['toggle_status'])) {
        $new_status = $_POST['new_status'];
        $query = "UPDATE reviews 
                  SET status = ?, status_updated_at = CURRENT_TIMESTAMP 
                  WHERE review_id = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("si", $new_status, $review_id);
        $stmt->execute();
    } elseif (isset($_POST['delete_reply'])) {
        $query = "UPDATE reviews 
                  SET admin_reply = NULL, admin_reply_updated_at = CURRENT_TIMESTAMP, staff_id = NULL 
                  WHERE review_id = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
    }

    echo "<script>window.location.href='adminreviewdetails.php?item_id=$item_id&item_type=$item_type';</script>";
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

        .btn-primary {  background-color: #28a745; color: white; }
        .btn-primary:hover { background-color: #218838; color: white; }
        .btn-success { background-color: #28a745; color: white; }

        .btn-warning { background-color: #ff4d4d;; color: white; }
        .btn-warning:hover {   background-color: #ff1a1a; color: white; }
        .status-active { color: green; font-weight: bold; }
        .status-inactive { color: red; font-weight: bold; }

       /* Redesigned Modal */
.modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 450px;
    max-width: 90%;
    background: none;
    padding: 0;
    z-index: 1000;
    transition: opacity 0.3s ease;
}

/* Modal Content */
.modal-content {
    position: relative;
    text-align: center;
    background: white; /* 白色背景 */
    padding: 25px;
    border-radius: 15px;
    color: #333; /* 深灰文本颜色 */
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2); /* 轻微阴影 */
}

/* Modal Header */
.modal h2 {
    margin-bottom: 20px;
    font-size: 24px;
    font-weight: bold;
    letter-spacing: 0.5px;
    color: #333; /* 深灰标题颜色 */
}

/* Textarea */
.modal textarea {
    width: 100%;
    height: 150px;
    resize: none;
    padding: 15px;
    border: 1px solid #ccc; /* 浅灰边框 */
    border-radius: 10px;
    font-size: 14px;
    font-family: Arial, sans-serif;
    margin-bottom: 20px;
    background-color: #f9f9f9; /* 浅灰背景 */
    color: #333; /* 深灰文本颜色 */
    transition: box-shadow 0.3s ease, border-color 0.3s ease;
}

.modal textarea:focus {
    box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.1); /* 聚焦效果 */
    outline: none;
    border-color: #666; /* 聚焦时边框颜色 */
}

/* Buttons */
.modal button {
    padding: 12px 25px;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    background: #333; /* 深灰背景 */
    color: white; /* 白色按钮文本 */
    transition: background 0.3s ease, transform 0.2s ease;
}

.modal button:hover {
    background: #555; /* 浅灰悬停效果 */
    transform: scale(1.05);
}

/* Close Button */
.close-btn {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 24px;
    font-weight: bold;
    color: #333; /* 深灰关闭按钮 */
    cursor: pointer;
    transition: color 0.3s ease;
}

.close-btn:hover {
    color: #555; /* 悬停时的按钮颜色 */
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
        <span class="close-btn" onclick="closeReplyForm()">&times;</span>
        <h2>Reply to Review</h2>
        <form method="post">
            <textarea id="replyTextarea" name="admin_reply" placeholder="Type your reply here..." required></textarea>
            <input type="hidden" name="review_id" id="reviewIdInput">
            <button type="submit" name="reply">Save changes</button>
            <button type="submit" name="delete_reply" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this reply?')">Delete Reply</button>

        </form>
    </div>
</div>

<!-- Image Modal -->
<div class="image-modal" id="imageModal">
    <img src="" alt="Review Image" id="imagePreview">
    <span class="close-btn" onclick="closeImageModal()">&times;</span>
</div>

<script>
function openReplyForm(reviewId, currentReply) {
    const replyTextarea = document.getElementById('replyTextarea');
    const deleteButton = document.querySelector('button[name="delete_reply"]');

    document.getElementById('replyModal').style.display = 'block';
    replyTextarea.value = currentReply || '';
    document.getElementById('reviewIdInput').value = reviewId;

    // 如果当前回复为空，隐藏删除按钮
    if (!currentReply) {
        deleteButton.style.display = 'none';
    } else {
        deleteButton.style.display = 'inline-block';
    }
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

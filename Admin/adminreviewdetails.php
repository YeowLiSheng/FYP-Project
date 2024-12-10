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
        // 回复或编辑回复
        $admin_reply = trim($_POST['admin_reply']);
        $query = "UPDATE reviews SET admin_reply = ? WHERE review_id = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("si", $admin_reply, $review_id);
        $stmt->execute();
    } elseif (isset($_POST['toggle_status'])) {
        // 激活或停用评论
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
        /* 主内容样式 */
        .main { padding: 20px; }
        .product-info { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; }
        .product-image, .user-image, .review-image { width: 100px; height: auto; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 15px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f4f4f4; }
        .btn { padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-warning { background-color: #ffc107; color: black; }
        .status-active { color: green; font-weight: bold; }
        .status-inactive { color: red; font-weight: bold; }
        /* 弹窗样式 */
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            max-width: 90%;
            background: linear-gradient(to bottom, #ffffff, #f4f4f4);
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            z-index: 1000;
            padding: 20px;
            font-family: Arial, sans-serif;
            color: #333;
        }
        .modal-content { position: relative; text-align: center; }
        .modal h2 { margin-bottom: 20px; font-size: 24px; font-weight: bold; color: #444; }
        .modal textarea {
            width: 100%;
            height: 150px;
            resize: none;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            font-family: Arial, sans-serif;
            margin-bottom: 20px;
        }
        .modal textarea:focus {
            outline: none;
            border: 1px solid #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
        .modal button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            transition: background-color 0.3s ease;
        }
        .modal button:hover { background-color: #0056b3; }
        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            font-weight: bold;
            color: #555;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .close-btn:hover { color: #ff0000; }
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
            <textarea name="admin_reply" id="modal-admin-reply" placeholder="Enter your reply here..." required></textarea>
            <button type="submit" name="reply">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openReplyForm(reviewId, replyText) {
    document.getElementById("modal-review-id").value = reviewId;
    document.getElementById("modal-admin-reply").value = replyText || '';
    document.getElementById("reply-modal").style.display = "block";
}

function closeReplyForm() {
    document.getElementById("reply-modal").style.display = "none";
}
</script>
</body>
</html>

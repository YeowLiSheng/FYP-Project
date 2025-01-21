<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

$product_id = $_GET['product_id'] ?? 0;


$product_query = "
    SELECT 
        COALESCE(p.product_id, pp.promotion_id) AS item_id,
        CASE 
            WHEN p.product_id IS NOT NULL THEN p.product_name
            ELSE pp.promotion_name
        END AS item_name,
        CASE 
            WHEN p.product_id IS NOT NULL THEN p.product_image
            ELSE pp.promotion_image
        END AS item_image
    FROM product_variant pv
    LEFT JOIN product p ON pv.product_id = p.product_id
    LEFT JOIN promotion_product pp ON pv.promotion_id = pp.promotion_id
    WHERE COALESCE(p.product_id, pp.promotion_id) = ?
    LIMIT 1
";
$stmt = $connect->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();


$review_query = "
    SELECT 
        r.review_id, 
        r.rating, 
        r.comment, 
        r.image AS review_image, 
        r.created_at, 
        u.user_name, 
        u.user_image, 
        r.admin_reply, 
        r.status
    FROM reviews r 
    INNER JOIN user u ON r.user_id = u.user_id 
    WHERE r.detail_id IN (
        SELECT od.detail_id 
        FROM order_details od
        INNER JOIN product_variant pv ON od.variant_id = pv.variant_id
        WHERE pv.product_id = ? OR pv.promotion_id = ?
    )
    ORDER BY r.created_at DESC
";
$stmt = $connect->prepare($review_query);
$stmt->bind_param("ii", $product_id, $product_id);
$stmt->execute();
$reviews = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $review_id = $_POST['review_id'];
    $staff_id = $_SESSION['staff_id']; 
    $success_message = '';

    if (isset($_POST['reply'])) {
        $admin_reply = trim($_POST['admin_reply']);
        $query = "UPDATE reviews 
                  SET admin_reply = ?, admin_reply_updated_at = CURRENT_TIMESTAMP, staff_id = ? 
                  WHERE review_id = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("sii", $admin_reply, $staff_id, $review_id);
        if ($stmt->execute()) {
            $success_message = 'Reply added successfully.';
        } else {
            $success_message = 'Failed to add reply.';
        }
    } elseif (isset($_POST['toggle_status'])) {
        $new_status = $_POST['new_status'];
        $query = "UPDATE reviews 
                  SET status = ?, status_updated_at = CURRENT_TIMESTAMP 
                  WHERE review_id = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("si", $new_status, $review_id);
        if ($stmt->execute()) {
            $success_message = 'Status updated successfully.';
        } else {
            $success_message = 'Failed to update status.';
        }
    } elseif (isset($_POST['delete_reply'])) {
        $query = "UPDATE reviews 
                  SET admin_reply = NULL, admin_reply_updated_at = CURRENT_TIMESTAMP, staff_id = NULL 
                  WHERE review_id = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("i", $review_id);
        if ($stmt->execute()) {
            $success_message = 'Reply deleted successfully.';
        } else {
            $success_message = 'Failed to delete reply.';
        }
    }

    echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Reply Updated',
                    text: '" . $success_message . "',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'adminreviewdetails.php?product_id=$product_id';
                    }
                });
            });
        </script>";
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
    background: white; 
    padding: 25px;
    border-radius: 15px;
    color: #333; 
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
}

/* Modal Header */
.modal h2 {
    margin-bottom: 20px;
    font-size: 24px;
    font-weight: bold;
    letter-spacing: 0.5px;
    color: #333; 
}

/* Textarea */
.modal textarea {
    width: 100%;
    height: 150px;
    resize: none;
    padding: 15px;
    border: 1px solid #ccc; 
    border-radius: 10px;
    font-size: 14px;
    font-family: Arial, sans-serif;
    margin-bottom: 20px;
    background-color: #f9f9f9; 
    color: #333; 
    transition: box-shadow 0.3s ease, border-color 0.3s ease;
}

.modal textarea:focus {
    box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.1);
    outline: none;
    border-color: #666; 
}

/* Buttons */
.modal button {
    padding: 12px 25px;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    background: #333; 
    color: white; 
    transition: background 0.3s ease, transform 0.2s ease;
}

.modal button:hover {
    background: #555; 
    transform: scale(1.05);
}

/* Close Button */
.close-btn {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 24px;
    font-weight: bold;
    color: #333; 
    cursor: pointer;
    transition: color 0.3s ease;
}

.close-btn:hover {
    color: #555; 
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
    <img 
    src="../User/images/<?= htmlspecialchars($product['item_image']) ?>" 
    alt="<?= htmlspecialchars($product['item_name']) ?>" 
    class="product-image">
<h2><?= htmlspecialchars($product['item_name']) ?></h2>
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
            <button type="submit" name="delete_reply" class="btn btn-danger" 
            onclick="event.preventDefault(); confirmDeleteReply();">Delete Reply</button>
        </form>
    </div>
</div>

<!-- Image Modal -->
<div class="image-modal" id="imageModal">
    <img src="" alt="Review Image" id="imagePreview">
    <span class="close-btn" onclick="closeImageModal()">&times;</span>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function openReplyForm(reviewId, currentReply) {
    const replyTextarea = document.getElementById('replyTextarea');
    const deleteButton = document.querySelector('button[name="delete_reply"]');

    document.getElementById('replyModal').style.display = 'block';
    replyTextarea.value = currentReply || '';
    document.getElementById('reviewIdInput').value = reviewId;

    
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
function confirmDeleteReply() {
        Swal.fire({
            icon: 'warning',
            title: 'Are you sure?',
            text: 'Do you really want to delete this reply?',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Proceed with form submission if confirmed
                document.getElementById('deleteReplyForm').submit();
            }
        });
    }
</script>
</body>
</html>
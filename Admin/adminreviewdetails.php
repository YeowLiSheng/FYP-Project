<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

// Get product ID from URL
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

// Fetch all reviews for the product
$query = "
    SELECT r.review_id, r.rating, r.comment, r.image, r.status, r.admin_reply, 
           r.created_at, u.user_name, u.user_image 
    FROM reviews r
    JOIN order_details od ON r.detail_id = od.detail_id
    JOIN users u ON r.user_id = u.user_id
    WHERE od.product_id = ? 
    ORDER BY r.created_at DESC";

$stmt = $connect->prepare($query);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle deactivate review request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deactivate_review'])) {
    $review_id = intval($_POST['review_id']);
    $update_query = "UPDATE reviews SET status = 'inactive' WHERE review_id = ?";
    $update_stmt = $connect->prepare($update_query);
    $update_stmt->bind_param('i', $review_id);
    $update_stmt->execute();
    echo "<script>alert('Review has been deactivated!'); window.location.reload();</script>";
}

// Handle reply review request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_review'])) {
    $review_id = intval($_POST['review_id']);
    $admin_reply = trim($_POST['admin_reply']);
    $update_query = "UPDATE reviews SET admin_reply = ? WHERE review_id = ?";
    $update_stmt = $connect->prepare($update_query);
    $update_stmt->bind_param('si', $admin_reply, $review_id);
    $update_stmt->execute();
    echo "<script>alert('Reply sent successfully!'); window.location.reload();</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Review Details</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(120deg, #f5f7fa, #e4e9f0);
            margin: 0;
            padding: 0;
        }

        .main {
            margin-left: 78px;
            padding: 15px;
        }

        h1 {
            color: #2c3e50;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        h1 ion-icon {
            font-size: 32px;
            color: #3498db;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            overflow: hidden;
            border-radius: 10px;
            margin-top: 10px;
            table-layout: fixed;
        }

        .table th, .table td {
            padding: 15px;
            text-align: center;
            border: 1px solid #dcdde1;
            word-wrap: break-word;
        }

        .table th {
            background: #3498db;
            color: white;
            font-weight: bold;
        }

        .table tr:hover {
            background: #ecf0f1;
        }

        .table th ion-icon {
            margin-right: 5px;
        }

        tr[onclick] {
            cursor: pointer;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #1d6fa5;
        }

        .reply-input {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .reply-input input {
            padding: 5px;
            width: 100%;
            border: 1px solid #dcdde1;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="main">
        <h1><ion-icon name="chatbubble-ellipses-outline"></ion-icon> Review Details</h1>
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Image</th>
                        <th>Status</th>
                        <th>Admin Reply</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td>
                            <img src="<?php echo !empty($row['user_image']) ? $row['user_image'] : 'images/default-avatar.png'; ?>" 
                                 alt="User Avatar" style="width: 50px; height: 50px; border-radius: 50%;"><br>
                            <?php echo htmlspecialchars($row['user_name']); ?>
                        </td>
                        <td><?php echo $row['rating']; ?> / 5</td>
                        <td><?php echo htmlspecialchars($row['comment']); ?></td>
                        <td>
                            <?php if (!empty($row['image'])) { ?>
                                <img src="<?php echo $row['image']; ?>" alt="Review Image" style="width: 100px;">
                            <?php } else { ?>
                                No Image
                            <?php } ?>
                        </td>
                        <td><?php echo $row['status'] === 'active' ? 'Active' : 'Inactive'; ?></td>
                        <td><?php echo !empty($row['admin_reply']) ? htmlspecialchars($row['admin_reply']) : 'No Reply'; ?></td>
                        <td>
                            <div class="action-buttons">
                                <!-- Deactivate Review -->
                                <?php if ($row['status'] === 'active') { ?>
                                    <form method="POST">
                                        <input type="hidden" name="review_id" value="<?php echo $row['review_id']; ?>">
                                        <button type="submit" name="deactivate_review" class="btn btn-danger">Deactivate</button>
                                    </form>
                                <?php } ?>

                                <!-- Reply to Review -->
                                <form method="POST" class="reply-input">
                                    <input type="hidden" name="review_id" value="<?php echo $row['review_id']; ?>">
                                    <input type="text" name="admin_reply" placeholder="Reply..." required>
                                    <button type="submit" name="reply_review" class="btn btn-primary">Reply</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

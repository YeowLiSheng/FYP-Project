<?php
include("dataconnection.php"); 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['package_id'])) {
    $packageId = intval($_POST['package_id']);

    // Fetch reviews for the selected package
    $sql = "SELECT r.rating, r.comment, r.image, u.username, r.created_at
            FROM reviews r
            JOIN order_details od ON r.detail_id = od.detail_id
            JOIN user u ON r.user_id = u.user_id
            WHERE od.package_id = ? AND r.status = 'active'
            ORDER BY r.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $packageId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='review'>";
            echo "  <h6>User: " . htmlspecialchars($row['username']) . "</h6>";
            echo "  <p>Rating: " . htmlspecialchars($row['rating']) . "/5</p>";
            echo "  <p>" . htmlspecialchars($row['comment']) . "</p>";
            if ($row['image']) {
                echo "  <img src='review_images/" . htmlspecialchars($row['image']) . "' alt='Review Image' class='img-fluid' />";
            }
            echo "  <p class='text-muted'>" . htmlspecialchars($row['created_at']) . "</p>";
            echo "</div><hr />";
        }
    } else {
        echo "<p class='text-warning'>No reviews available for this package.</p>";
    }
}
?>

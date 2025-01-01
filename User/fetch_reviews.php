<?php
include("dataconnection.php"); 

if (isset($_GET['detail_id'])) {
    $detail_id = intval($_GET['detail_id']);
    $stmt = $conn->prepare("SELECT rating, comment, created_at FROM reviews WHERE detail_id = ? AND status = 'active'");
    $stmt->bind_param("i", $detail_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='review-item'>";
            echo "<p><strong>Rating:</strong> " . htmlspecialchars($row['rating']) . "/5</p>";
            echo "<p><strong>Comment:</strong> " . htmlspecialchars($row['comment']) . "</p>";
            echo "<p><small><em>Posted on: " . htmlspecialchars($row['created_at']) . "</em></small></p>";
            echo "<hr>";
            echo "</div>";
        }
    } else {
        echo "<p>No reviews available for this package.</p>";
    }

    $stmt->close();
}
?>

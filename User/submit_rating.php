<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);
    $user_id = $_SESSION['id'];
    $ratings = $_POST['rating'];
    $comments = $_POST['comment'];

    $stmt = $conn->prepare("INSERT INTO feedback (rating, comment, user_id, product_id, order_id) VALUES (?, ?, ?, ?, ?)");
    foreach ($ratings as $product_id => $rating) {
        $comment = $comments[$product_id];
        $stmt->bind_param("isiii", $rating, $comment, $user_id, $product_id, $order_id);
        $stmt->execute();
    }
    $stmt->close();

    header("Location: order_details.php?order_id=$order_id");
    exit;
}
?>

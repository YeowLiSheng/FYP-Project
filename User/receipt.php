<?php
session_start();
require('fpdf/fpdf.php'); // 引入 FPDF 库

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 检查用户是否登录
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];

// 获取订单详情
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$order_query = "
    SELECT o.*, u.user_name, u.user_email 
    FROM orders AS o
    JOIN user AS u ON o.user_id = u.user_id
    WHERE o.order_id = ? AND o.user_id = ?
";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    die("Invalid order or access denied.");
}

$order = $order_result->fetch_assoc();

// 获取订单商品详情
$order_items_query = "
    SELECT product_name, quantity, unit_price, total_price 
    FROM order_details 
    WHERE order_id = ?
";
$item_stmt = $conn->prepare($order_items_query);
$item_stmt->bind_param("i", $order_id);
$item_stmt->execute();
$order_items_result = $item_stmt->get_result();

// 初始化 FPDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);

// 收据标题
$pdf->Cell(0, 10, 'Order Receipt', 0, 1, 'C');
$pdf->Ln(5);

// 用户和订单信息
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Order ID: ' . $order['order_id'], 0, 1);
$pdf->Cell(0, 10, 'Customer Name: ' . $order['user_name'], 0, 1);
$pdf->Cell(0, 10, 'Email: ' . $order['user_email'], 0, 1);
$pdf->Cell(0, 10, 'Order Date: ' . $order['order_date'], 0, 1);
$pdf->Cell(0, 10, 'Shipping Address: ' . $order['shipping_address'], 0, 1);
$pdf->Ln(5);

// 订单商品详情
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(80, 10, 'Product Name', 1);
$pdf->Cell(30, 10, 'Quantity', 1);
$pdf->Cell(40, 10, 'Unit Price (RM)', 1);
$pdf->Cell(40, 10, 'Total Price (RM)', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
while ($item = $order_items_result->fetch_assoc()) {
    $pdf->Cell(80, 10, $item['product_name'], 1);
    $pdf->Cell(30, 10, $item['quantity'], 1);
    $pdf->Cell(40, 10, number_format($item['unit_price'], 2), 1);
    $pdf->Cell(40, 10, number_format($item['total_price'], 2), 1);
    $pdf->Ln();
}

// 显示订单总结
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Order Summary', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Grand Total: RM' . number_format($order['Grand_total'], 2), 0, 1);
$pdf->Cell(0, 10, 'Discount: RM' . number_format($order['discount_amount'], 2), 0, 1);
$pdf->Cell(0, 10, 'Delivery Charge: RM' . number_format($order['delivery_charge'], 2), 0, 1);
$pdf->Cell(0, 10, 'Final Amount: RM' . number_format($order['final_amount'], 2), 0, 1);

// 输出 PDF 文件
$pdf->Output('D', 'Receipt_Order_' . $order['order_id'] . '.pdf');
exit;
?>

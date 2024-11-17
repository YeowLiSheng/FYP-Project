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
$pdf->SetFont('Arial', 'B', 16);

// 标题
$pdf->SetTextColor(50, 50, 50); // 深灰色字体
$pdf->Cell(0, 10, 'Order Receipt', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Ln(5);

// 用户信息
$pdf->SetFillColor(240, 240, 240); // 淡灰色背景
$pdf->Cell(0, 8, 'Customer Details', 0, 1, 'L', true);
$pdf->Ln(2);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 8, 'Customer Name:', 0, 0);
$pdf->Cell(100, 8, $order['user_name'], 0, 1);
$pdf->Cell(50, 8, 'Email:', 0, 0);
$pdf->Cell(100, 8, $order['user_email'], 0, 1);
$pdf->Cell(50, 8, 'Order Date:', 0, 0);
$pdf->Cell(100, 8, $order['order_date'], 0, 1);
$pdf->Cell(50, 8, 'Shipping Address:', 0, 0);
$pdf->MultiCell(100, 8, $order['shipping_address'], 0, 1);
$pdf->Ln(5);

// 订单详情表头
$pdf->SetFillColor(230, 230, 230); // 更浅的灰色背景
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(80, 10, 'Product Name', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Quantity', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Unit Price (RM)', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Total Price (RM)', 1, 1, 'C', true);

// 订单详情内容
$pdf->SetFont('Arial', '', 12);
while ($item = $order_items_result->fetch_assoc()) {
    $pdf->Cell(80, 10, $item['product_name'], 1);
    $pdf->Cell(30, 10, $item['quantity'], 1, 0, 'C');
    $pdf->Cell(40, 10, number_format($item['unit_price'], 2), 1, 0, 'C');
    $pdf->Cell(40, 10, number_format($item['total_price'], 2), 1, 1, 'C');
}
$pdf->Ln(5);

// 订单总结
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Order Summary', 0, 1, 'L', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Ln(2);

$pdf->Cell(50, 8, 'Grand Total:', 0, 0);
$pdf->Cell(100, 8, 'RM ' . number_format($order['Grand_total'], 2), 0, 1);
$pdf->Cell(50, 8, 'Discount:', 0, 0);
$pdf->Cell(100, 8, '- RM ' . number_format($order['discount_amount'], 2), 0, 1);
$pdf->Cell(50, 8, 'Delivery Charge:', 0, 0);
$pdf->Cell(100, 8, '+ RM ' . number_format($order['delivery_charge'], 2), 0, 1);
$pdf->Cell(50, 8, 'Final Amount:', 0, 0);
$pdf->Cell(100, 8, 'RM ' . number_format($order['final_amount'], 2), 0, 1);

// 输出 PDF 文件
$pdf->Output('I', 'Receipt_Order_' . $order['order_id'] . '.pdf');
exit;
?>

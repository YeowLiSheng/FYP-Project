<?php 
require('fpdf/fpdf.php');
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

if (!isset($_SESSION['id']) || !isset($_GET['order_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = intval($_GET['order_id']);

$order_stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.Grand_total, o.discount_amount, o.delivery_charge,
           o.final_amount, o.order_status, o.shipping_address, o.shipping_method, u.user_name
    FROM orders o
    JOIN user u ON o.user_id = u.user_id
    WHERE o.order_id = ?
");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

$details_stmt = $conn->prepare("
    SELECT od.product_name, od.quantity, od.unit_price, od.total_price
    FROM order_details od
    WHERE od.order_id = ?
");
$details_stmt->bind_param("i", $order_id);
$details_stmt->execute();
$details_result = $details_stmt->get_result();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetMargins(10, 20, 10);
$pdf->SetAutoPageBreak(true, 20);

// 添加Logo (假设有一个logo.png文件)

$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(220, 53, 69);
$pdf->Cell(0, 10, 'YLS Atelier', 0, 1, 'C');
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(0);
$pdf->Cell(0, 6, 'Melbourne VIC Australia', 0, 1, 'C');
$pdf->Ln(10);

// 绘制分割线
$pdf->SetDrawColor(220, 53, 69);
$pdf->Line(10, 35, 200, 35);
$pdf->Ln(5);

// Bill To & Ship To 信息
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(248, 249, 250);
$pdf->Cell(95, 8, 'Bill To', 0, 0, 'L', true);
$pdf->Cell(95, 8, 'Ship To', 0, 1, 'R', true);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(95, 6, $order['user_name'], 0, 0, 'L');
$pdf->Cell(95, 6, $order['user_name'], 0, 1, 'R');
$pdf->Cell(95, 6, $order['shipping_address'], 0, 0, 'L');
$pdf->Cell(95, 6, $order['shipping_address'], 0, 1, 'R');
$pdf->Ln(10);

// 订单信息
$pdf->SetDrawColor(128, 128, 128);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 6, 'Receipt #', 0, 0, 'L');
$pdf->Cell(140, 6, '', 0, 0, 'L'); // 空白位置调整对齐
$pdf->Cell(30, 6, 'Order Date:', 0, 1, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(20, 6, 'MY-' . str_pad($order['order_id'], 3, '0', STR_PAD_LEFT), 0, 0, 'L');
$pdf->Cell(140, 6, '', 0, 0, 'L'); // 空白位置调整对齐
$pdf->Cell(30, 6, date('Y-m-d', strtotime($order['order_date'])), 0, 1, 'R');
$pdf->Ln(5);

// 表格头部
$pdf->SetFillColor(220, 53, 69);
$pdf->SetTextColor(255);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 10, 'No.', 1, 0, 'C', true);
$pdf->Cell(85, 10, 'Description', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Unit Price (RM)', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Qty', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Subtotal (RM)', 1, 1, 'C', true);

// 表格内容
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0);
$itemNumber = 1;

while ($detail = $details_result->fetch_assoc()) {
    $pdf->Cell(20, 8, $itemNumber, 1, 0, 'C');
    $pdf->Cell(85, 8, $detail['product_name'], 1, 0, 'C');
    $pdf->Cell(30, 8, number_format($detail['unit_price'], 2), 1, 0, 'C');
    $pdf->Cell(20, 8, $detail['quantity'], 1, 0, 'C');
    $pdf->Cell(35, 8, number_format($detail['total_price'], 2), 1, 1, 'C');
    $itemNumber++;
}

// 价格明细 (往下调整)
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(151, 6, 'Grand Total:', 0, 0, 'R');
$pdf->Cell(40, 6, 'RM ' . number_format($order['Grand_total'], 2), 0, 1, 'R');
$pdf->Cell(151, 6, 'Discount:', 0, 0, 'R');
$pdf->Cell(40, 6, '- RM ' . number_format($order['discount_amount'], 2), 0, 1, 'R');
$pdf->Cell(151, 6, 'Delivery Charge:', 0, 0, 'R');
$pdf->Cell(40, 6, '+ RM ' . number_format($order['delivery_charge'], 2), 0, 1, 'R');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(151, 8, 'Total Amount:', 0, 0, 'R');
$pdf->Cell(40, 8, 'RM ' . number_format($order['final_amount'], 2), 0, 1, 'R');

// 感谢语句
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(128);
$pdf->Cell(0, 6, 'Thank you for your purchase! We appreciate your business and hope to see you again soon.', 0, 1, 'C');

$pdf->Output('D', 'Receipt_Order_' . $order['order_id'] . '.pdf');
?>

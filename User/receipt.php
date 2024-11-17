<?php 
require('fpdf/fpdf.php');
session_start();

// 数据库连接
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

// 获取订单信息
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

// 获取订单详情
$details_stmt = $conn->prepare("
    SELECT od.product_name, od.quantity, od.unit_price, od.total_price
    FROM order_details od
    WHERE od.order_id = ?
");
$details_stmt->bind_param("i", $order_id);
$details_stmt->execute();
$details_result = $details_stmt->get_result();

// 创建 PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);
$pdf->SetMargins(10, 20, 10);
$pdf->SetAutoPageBreak(true, 20);

// 标题
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 10, 'Order Receipt', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Thank you for shopping with us!', 0, 1, 'C');
$pdf->Ln(5);

// 店铺信息
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'East Asia Trading', 0, 1, 'L');
$pdf->Cell(0, 6, 'Pasar Pudu Baru 10', 0, 1, 'L');
$pdf->Cell(0, 6, 'Kuala Lumpur 53000', 0, 1, 'L');
$pdf->Ln(5);

// 订单基本信息
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(95, 6, 'Bill To', 0, 0, 'L');
$pdf->Cell(95, 6, 'Ship To', 0, 1, 'R');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(95, 6, $order['user_name'], 0, 0, 'L');
$pdf->Cell(95, 6, $order['user_name'], 0, 1, 'R');
$pdf->Cell(95, 6, 'Order ID: ' . $order['order_id'], 0, 0, 'L');
$pdf->Cell(95, 6, 'Order Date: ' . date('d/m/Y', strtotime($order['order_date'])), 0, 1, 'R');
$pdf->Cell(0, 6, 'Shipping Address: ' . $order['shipping_address'], 0, 1, 'L');
$pdf->Ln(8);

// 表格标题行
function addTableHeader($pdf) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(10, 8, 'Qty', 1, 0, 'C', true);
    $pdf->Cell(80, 8, 'Description', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Unit Price (RM)', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Amount (RM)', 1, 1, 'C', true);
}

// 添加表格内容
addTableHeader($pdf);
$pdf->SetFont('Arial', '', 10);

while ($detail = $details_result->fetch_assoc()) {
    $pdf->Cell(10, 8, $detail['quantity'], 1, 0, 'C');
    $pdf->Cell(80, 8, $detail['product_name'], 1, 0, 'L');
    $pdf->Cell(30, 8, number_format($detail['unit_price'], 2), 1, 0, 'C');
    $pdf->Cell(30, 8, number_format($detail['total_price'], 2), 1, 1, 'C');
}

// 价格明细
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Pricing Details', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(95, 8, 'Subtotal:', 0, 0, 'L');
$pdf->Cell(95, 8, 'RM ' . number_format($order['Grand_total'], 2), 0, 1, 'R');
$pdf->Cell(95, 8, 'Discount:', 0, 0, 'L');
$pdf->Cell(95, 8, '- RM ' . number_format($order['discount_amount'], 2), 0, 1, 'R');
$pdf->Cell(95, 8, 'Delivery Charge:', 0, 0, 'L');
$pdf->Cell(95, 8, '+ RM ' . number_format($order['delivery_charge'], 2), 0, 1, 'R');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(95, 8, 'Total Amount:', 0, 0, 'L');
$pdf->Cell(95, 8, 'RM ' . number_format($order['final_amount'], 2), 0, 1, 'R');

$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, 'We hope to see you again soon!', 0, 1, 'C');

// 输出 PDF
$pdf->Output('D', 'Receipt_Order_' . $order['order_id'] . '.pdf');
?>

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

$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(0);
$pdf->Cell(0, 10, 'East Asia Trading', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Pasar Pudu Baru 10, Kuala Lumpur 53000', 0, 1, 'C');
$pdf->Ln(10);

// Bill To & Ship To 信息
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(95, 6, 'Bill To', 0, 0, 'L');
$pdf->Cell(95, 6, 'Ship To', 0, 1, 'R');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(95, 6, $order['user_name'], 0, 0, 'L');
$pdf->Cell(95, 6, $order['user_name'], 0, 1, 'R');
$pdf->Cell(95, 6, $order['shipping_address'], 0, 0, 'L');
$pdf->Cell(95, 6, $order['shipping_address'], 0, 1, 'R');
$pdf->Ln(10);

// 订单信息
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(95, 6, 'Receipt #', 0, 0, 'L');
$pdf->Cell(95, 6, 'Order Date:', 0, 1, 'R');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(95, 6, 'MY-' . str_pad($order['order_id'], 3, '0', STR_PAD_LEFT), 0, 0, 'L');
$pdf->Cell(95, 6, date('Y-m-d', strtotime($order['order_date'])), 0, 1, 'R');
$pdf->Ln(5);

// 表格头部
$pdf->SetFillColor(220, 53, 69);
$pdf->SetTextColor(255);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(15, 10, 'Qty', 1, 0, 'C', true);
$pdf->Cell(95, 10, 'Description', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Unit Price (RM)', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Amount (RM)', 1, 1, 'C', true);

// 表格内容
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0);
$totalAmount = 0;
while ($detail = $details_result->fetch_assoc()) {
    $pdf->Cell(15, 8, $detail['quantity'], 1, 0, 'C');
    $pdf->Cell(95, 8, $detail['product_name'], 1, 0, 'L');
    $pdf->Cell(40, 8, number_format($detail['unit_price'], 2), 1, 0, 'C');
    $pdf->Cell(40, 8, number_format($detail['total_price'], 2), 1, 1, 'C');
}

// 价格明细
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, 'Pricing Details', 0, 1, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(135, 6, 'Subtotal:', 0, 0, 'R');
$pdf->Cell(40, 6, 'RM ' . number_format($order['Grand_total'], 2), 0, 1, 'R');
$pdf->Cell(135, 6, 'Discount:', 0, 0, 'R');
$pdf->Cell(40, 6, '- RM ' . number_format($order['discount_amount'], 2), 0, 1, 'R');
$pdf->Cell(135, 6, 'Delivery Charge:', 0, 0, 'R');
$pdf->Cell(40, 6, '+ RM ' . number_format($order['delivery_charge'], 2), 0, 1, 'R');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(135, 8, 'Total Amount:', 0, 0, 'R');
$pdf->Cell(40, 8, 'RM ' . number_format($order['final_amount'], 2), 0, 1, 'R');

// 条款和签名
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 6, 'Terms & Conditions', 0, 1, 'L');
$pdf->Cell(0, 6, 'Payment is due within 15 days.', 0, 1, 'L');
$pdf->Cell(0, 10, '', 0, 1, 'L');
$pdf->Image('signature.png', 150, $pdf->GetY(), 30, 10);

$pdf->Output('D', 'Receipt_Order_' . $order['order_id'] . '.pdf');
?>

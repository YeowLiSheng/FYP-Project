<?php
session_start();
require('fpdf/fpdf.php');

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

// 检查连接
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 检查是否有订单 ID
if (!isset($_GET['order_id'])) {
    echo "Invalid order ID.";
    exit;
}

$order_id = intval($_GET['order_id']);

// 获取订单信息
$order_stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.Grand_total, o.discount_amount, o.delivery_charge,
           o.final_amount, o.order_status, o.shipping_address, o.shipping_method, o.user_message,
           u.user_name
    FROM orders o
    JOIN user u ON o.user_id = u.user_id
    WHERE o.order_id = ?
");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    echo "Order not found.";
    exit;
}

$order = $order_result->fetch_assoc();

// 获取订单详细信息
$details_stmt = $conn->prepare("
    SELECT od.product_id, od.product_name, od.quantity, od.unit_price, od.total_price, p.product_image
    FROM order_details od
    JOIN product p ON od.product_id = p.product_id
    WHERE od.order_id = ?
");
$details_stmt->bind_param("i", $order_id);
$details_stmt->execute();
$details_result = $details_stmt->get_result();

// 创建 FPDF 对象
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// 标题
$pdf->Cell(0, 10, 'Order Receipt', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Ln(5);

// 订单信息
$pdf->Cell(50, 10, 'Order ID:', 0, 0);
$pdf->Cell(0, 10, $order['order_id'], 0, 1);
$pdf->Cell(50, 10, 'Order Date:', 0, 0);
$pdf->Cell(0, 10, date("Y-m-d H:i:s", strtotime($order['order_date'])), 0, 1);
$pdf->Cell(50, 10, 'User Name:', 0, 0);
$pdf->Cell(0, 10, $order['user_name'], 0, 1);
$pdf->Cell(50, 10, 'Status:', 0, 0);
$pdf->Cell(0, 10, $order['order_status'], 0, 1);
$pdf->Cell(50, 10, 'Shipping Address:', 0, 0);
$pdf->MultiCell(0, 10, $order['shipping_address']);
$pdf->Cell(50, 10, 'Shipping Method:', 0, 0);
$pdf->Cell(0, 10, $order['shipping_method'], 0, 1);

// 产品明细
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, 'Product Name', 1);
$pdf->Cell(30, 10, 'Quantity', 1);
$pdf->Cell(40, 10, 'Unit Price (RM)', 1);
$pdf->Cell(40, 10, 'Total Price (RM)', 1);
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 12);

while ($detail = $details_result->fetch_assoc()) {
    $pdf->Cell(50, 10, $detail['product_name'], 1);
    $pdf->Cell(30, 10, $detail['quantity'], 1);
    $pdf->Cell(40, 10, number_format($detail['unit_price'], 2), 1);
    $pdf->Cell(40, 10, number_format($detail['total_price'], 2), 1);
    $pdf->Ln(10);
}

// 价格细节
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, 'Pricing Details', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 10, 'Grand Total:', 0, 0);
$pdf->Cell(0, 10, 'RM ' . number_format($order['Grand_total'], 2), 0, 1);
$pdf->Cell(50, 10, 'Discount:', 0, 0);
$pdf->Cell(0, 10, '- RM ' . number_format($order['discount_amount'], 2), 0, 1);
$pdf->Cell(50, 10, 'Delivery Charge:', 0, 0);
$pdf->Cell(0, 10, '+ RM ' . number_format($order['delivery_charge'], 2), 0, 1);
$pdf->Cell(50, 10, 'Final Amount:', 0, 0);
$pdf->Cell(0, 10, 'RM ' . number_format($order['final_amount'], 2), 0, 1);

// 输出 PDF 并自动下载
$pdf->Output('D', 'Receipt_' . $order['order_id'] . '.pdf');

$conn->close();
?>

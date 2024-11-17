<?php
require('fpdf/fpdf.php');

// 数据库和会话初始化
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if (!isset($_SESSION['id']) || !isset($_GET['order_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = intval($_GET['order_id']);

// 获取订单和详情数据
$order_query = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
$order_query->bind_param("i", $order_id);
$order_query->execute();
$order = $order_query->get_result()->fetch_assoc();

$details_query = $conn->prepare("SELECT * FROM order_details WHERE order_id = ?");
$details_query->bind_param("i", $order_id);
$details_query->execute();
$details = $details_query->get_result();

// 创建 PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);

// 设置字体和样式
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, "East Asia Trading", 0, 1, 'L'); // 商家名称
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, "Pasar Pudu Baru 10", 0, 1, 'L'); // 地址
$pdf->Cell(0, 5, "Kuala Lumpur 53000", 0, 1, 'L');
$pdf->Ln(5);

// 右上角显示订单信息
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(130, 5, "", 0, 0); // 留白
$pdf->Cell(30, 5, "Receipt #:", 0, 0, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 5, $order['order_id'], 0, 1, 'R');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(130, 5, "", 0, 0); // 留白
$pdf->Cell(30, 5, "Date:", 0, 0, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 5, date('Y-m-d', strtotime($order['order_date'])), 0, 1, 'R');
$pdf->Ln(10);

// 收件人和发货地址
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(95, 5, "Bill To:", 0, 0, 'L');
$pdf->Cell(95, 5, "Ship To:", 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(95, 5, "Customer Name", 0, 0, 'L'); // 示例数据
$pdf->Cell(95, 5, "Customer Address", 0, 1, 'L'); // 示例数据
$pdf->Ln(5);

// 表格标题
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(15, 8, "QTY", 1, 0, 'C', true);
$pdf->Cell(85, 8, "DESCRIPTION", 1, 0, 'C', true);
$pdf->Cell(40, 8, "UNIT PRICE", 1, 0, 'C', true);
$pdf->Cell(40, 8, "AMOUNT", 1, 1, 'C', true);

// 表格内容
$pdf->SetFont('Arial', '', 10);
$total_amount = 0;
while ($row = $details->fetch_assoc()) {
    $pdf->Cell(15, 8, $row['quantity'], 1, 0, 'C');
    $pdf->Cell(85, 8, $row['product_name'], 1, 0, 'L');
    $pdf->Cell(40, 8, number_format($row['unit_price'], 2), 1, 0, 'R');
    $pdf->Cell(40, 8, number_format($row['total_price'], 2), 1, 1, 'R');
    $total_amount += $row['total_price'];
}

// 价格明细
$pdf->Ln(5);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(140, 8, "Subtotal:", 0, 0, 'R');
$pdf->Cell(40, 8, "RM " . number_format($total_amount, 2), 0, 1, 'R');

$pdf->Cell(140, 8, "SST (6%):", 0, 0, 'R');
$tax = $total_amount * 0.06;
$pdf->Cell(40, 8, "RM " . number_format($tax, 2), 0, 1, 'R');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(140, 8, "Receipt Total:", 0, 0, 'R');
$pdf->Cell(40, 8, "RM " . number_format($total_amount + $tax, 2), 0, 1, 'R');

// 条款与条件
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 9);
$pdf->Cell(0, 5, "Terms & Conditions", 0, 1, 'L');
$pdf->Cell(0, 5, "Payment is due within 15 days.", 0, 1, 'L');
$pdf->Cell(0, 5, "Public Bank Berhad", 0, 1, 'L');
$pdf->Cell(0, 5, "Account Number: 12345678", 0, 1, 'L');
$pdf->Cell(0, 5, "Routing Number: 0987654321098", 0, 1, 'L');

// 输出 PDF
$pdf->Output('D', 'Receipt_' . $order['order_id'] . '.pdf');
?>

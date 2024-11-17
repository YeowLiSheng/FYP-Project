<?php
require('fpdf/fpdf.php'); // 确保你已经安装并包含 FPDF

session_start();  // 启动会话

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

// 检查登录
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['order_id'])) {
    die("Invalid order ID.");
}

$order_id = intval($_GET['order_id']);

// 查询订单信息
$order_stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.Grand_total, o.discount_amount, o.delivery_charge,
           o.final_amount, o.order_status, o.shipping_address, o.shipping_method, u.user_name
    FROM orders o
    JOIN user u ON o.user_id = u.user_id
    WHERE o.order_id = ?
");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    die("Order not found.");
}

$order = $order_result->fetch_assoc();

// 查询订单详情
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

// 添加公司信息
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'E-Commerce Shop', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, '123 Modern Ave, CityName, Country', 0, 1, 'C');
$pdf->Cell(0, 6, 'Phone: (123) 456-7890 | Email: support@shop.com', 0, 1, 'C');
$pdf->Ln(10);

// 分隔线
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(10, 40, 200, 40);
$pdf->Ln(10);

// 订单基本信息
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Order Receipt', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(95, 8, 'Order ID: ' . $order['order_id'], 0, 0, 'L');
$pdf->Cell(95, 8, 'Order Date: ' . date('Y-m-d H:i', strtotime($order['order_date'])), 0, 1, 'R');
$pdf->Cell(95, 8, 'Customer: ' . $order['user_name'], 0, 0, 'L');
$pdf->Cell(95, 8, 'Shipping Method: ' . $order['shipping_method'], 0, 1, 'R');
$pdf->Cell(0, 8, 'Shipping Address: ' . $order['shipping_address'], 0, 1, 'L');
$pdf->Ln(5);

// 产品明细表格
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(80, 8, 'Product Name', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Quantity', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Unit Price (RM)', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Total Price (RM)', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(255, 255, 255);
while ($detail = $details_result->fetch_assoc()) {
    $pdf->Cell(80, 8, $detail['product_name'], 1, 0, 'L', true);
    $pdf->Cell(30, 8, $detail['quantity'], 1, 0, 'C', true);
    $pdf->Cell(40, 8, number_format($detail['unit_price'], 2), 1, 0, 'C', true);
    $pdf->Cell(40, 8, number_format($detail['total_price'], 2), 1, 1, 'C', true);
}
$pdf->Ln(5);

// 分隔线
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(5);

// 价格明细
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(95, 8, 'Grand Total:', 0, 0, 'L');
$pdf->Cell(95, 8, 'RM ' . number_format($order['Grand_total'], 2), 0, 1, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(95, 8, 'Discount:', 0, 0, 'L');
$pdf->Cell(95, 8, '- RM ' . number_format($order['discount_amount'], 2), 0, 1, 'R');
$pdf->Cell(95, 8, 'Delivery Charge:', 0, 0, 'L');
$pdf->Cell(95, 8, '+ RM ' . number_format($order['delivery_charge'], 2), 0, 1, 'R');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(95, 8, 'Final Amount:', 0, 0, 'L');
$pdf->Cell(95, 8, 'RM ' . number_format($order['final_amount'], 2), 0, 1, 'R');
$pdf->Ln(10);

// 感谢信息
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, 'Thank you for shopping with us!', 0, 1, 'C');

// 输出 PDF
$pdf->Output('D', 'Receipt_Order_' . $order['order_id'] . '.pdf');
?>

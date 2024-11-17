<?php
session_start();  // 启动会话

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");  // 设置字符集

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 检查用户是否登录
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

// 确认订单 ID 是否有效
if (!isset($_GET['order_id'])) {
    echo "Invalid order ID.";
    exit;
}

$order_id = intval($_GET['order_id']);

// 查询订单信息
$order_stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.Grand_total, o.discount_amount, o.delivery_charge,
           o.final_amount, o.shipping_address, u.user_name
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

// 查询订单详情
$details_stmt = $conn->prepare("
    SELECT od.product_name, od.quantity, od.unit_price, od.total_price
    FROM order_details od
    WHERE od.order_id = ?
");
$details_stmt->bind_param("i", $order_id);
$details_stmt->execute();
$details_result = $details_stmt->get_result();

require('fpdf/fpdf.php');

class PDF extends FPDF {
    function Header() {
        // 添加收据标题
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Order Receipt', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        // 添加页脚
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Thank you for shopping with us!', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// 添加用户和订单信息
$pdf->Cell(0, 10, 'Order ID: ' . $order['order_id'], 0, 1);
$pdf->Cell(0, 10, 'Customer: ' . $order['user_name'], 0, 1);
$pdf->Cell(0, 10, 'Order Date: ' . date("Y-m-d H:i:s", strtotime($order['order_date'])), 0, 1);
$pdf->Cell(0, 10, 'Shipping Address: ' . $order['shipping_address'], 0, 1);
$pdf->Ln(10);

// 添加产品详情
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(80, 10, 'Product Name', 1);
$pdf->Cell(30, 10, 'Quantity', 1, 0, 'C');
$pdf->Cell(40, 10, 'Unit Price (RM)', 1, 0, 'C');
$pdf->Cell(40, 10, 'Total Price (RM)', 1, 1, 'C');

$pdf->SetFont('Arial', '', 12);
while ($detail = $details_result->fetch_assoc()) {
    $pdf->Cell(80, 10, $detail['product_name'], 1);
    $pdf->Cell(30, 10, $detail['quantity'], 1, 0, 'C');
    $pdf->Cell(40, 10, number_format($detail['unit_price'], 2), 1, 0, 'C');
    $pdf->Cell(40, 10, number_format($detail['total_price'], 2), 1, 1, 'C');
}
$pdf->Ln(10);

// 添加价格明细
$pdf->Cell(0, 10, 'Pricing Details:', 0, 1);
$pdf->Cell(50, 10, 'Grand Total:', 0, 0);
$pdf->Cell(0, 10, 'RM ' . number_format($order['Grand_total'], 2), 0, 1);
$pdf->Cell(50, 10, 'Discount:', 0, 0);
$pdf->Cell(0, 10, '- RM ' . number_format($order['discount_amount'], 2), 0, 1);
$pdf->Cell(50, 10, 'Delivery Charge:', 0, 0);
$pdf->Cell(0, 10, '+ RM ' . number_format($order['delivery_charge'], 2), 0, 1);
$pdf->Cell(50, 10, 'Final Amount:', 0, 0);
$pdf->Cell(0, 10, 'RM ' . number_format($order['final_amount'], 2), 0, 1);

// 输出 PDF
$pdf_file = 'receipt_order_' . $order_id . '.pdf';
$pdf->Output('D', $pdf_file);
?>

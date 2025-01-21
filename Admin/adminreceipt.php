<?php 
require('../User/fpdf/fpdf.php');
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
    header("Location: admin_login.php");
    exit;
}

$order_id = intval($_GET['order_id']);

$order_stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.Grand_total, o.discount_amount,
           o.final_amount, o.order_status, o.shipping_address, o.shipping_method, u.user_name
    FROM orders o
    JOIN user u ON o.user_id = u.user_id
    WHERE o.order_id = ?
");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

$details_stmt = $conn->prepare("
    SELECT 
        od.detail_id,
        od.order_id,
        pv.variant_id,
        COALESCE(p.product_id, pp.promotion_id) AS product_or_promotion_id,
        COALESCE(p.product_name, pp.promotion_name) AS name,
        pv.color,
        od.quantity,
        od.unit_price,
        od.total_price,
        COALESCE(p.product_image, pp.promotion_image) AS image
    FROM order_details od
    JOIN product_variant pv ON od.variant_id = pv.variant_id
    LEFT JOIN product p ON pv.product_id = p.product_id
    LEFT JOIN promotion_product pp ON pv.promotion_id = pp.promotion_id
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
$pdf->SetTextColor(220, 53, 69);
$pdf->Cell(0, 10, 'YLS Atelier', 0, 1, 'C');
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(0);
$pdf->Cell(0, 6, 'Melbourne VIC Australia', 0, 1, 'C');
$pdf->Ln(10);


$pdf->SetDrawColor(220, 53, 69);
$pdf->Line(10, 35, 200, 35);
$pdf->Ln(5);

// Bill To & Ship To
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(248, 249, 250);
$pdf->Cell(95, 8, 'Bill To', 0, 0, 'L', true);
$pdf->Cell(95, 8, 'Ship To', 0, 1, 'R', true);

$pdf->SetFont('Arial', '', 10);

$currentX = $pdf->GetX();
$currentY = $pdf->GetY();

$pdf->SetXY($currentX, $currentY);
$pdf->Cell(95, 6, $order['user_name'], 0, 0, 'L');
$pdf->Ln(6); 
$billToStartY = $pdf->GetY(); 
$pdf->MultiCell(80, 6, $order['shipping_address'], 0, 'L');


$billToEndY = $pdf->GetY();


$pdf->SetXY($currentX + 95, $currentY);
$pdf->Cell(95, 6, $order['user_name'], 0, 0, 'R');
$pdf->Ln(6); 
$pdf->SetX($currentX + 95); 
$pdf->MultiCell(95, 6, $order['shipping_address'], 0, 'R'); 


$rightEndY = $pdf->GetY();
$finalY = max($billToEndY, $rightEndY); 
$pdf->SetY($finalY + 10); 


$pdf->SetDrawColor(128, 128, 128);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 6, 'Receipt #', 0, 0, 'L');
$pdf->Cell(140, 6, '', 0, 0, 'L'); 
$pdf->Cell(30, 6, 'Order Date:', 0, 1, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(20, 6, 'MY-' . str_pad($order['order_id'], 3, '0', STR_PAD_LEFT), 0, 0, 'L');
$pdf->Cell(140, 6, '', 0, 0, 'L'); 
$pdf->Cell(30, 6, date('Y-m-d H:i:s', strtotime($order['order_date'])), 0, 1, 'R');
$pdf->Ln(5);


$pdf->SetFillColor(220, 53, 69);
$pdf->SetTextColor(255);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 10, 'No.', 1, 0, 'C', true);
$pdf->Cell(85, 10, 'Description', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Color', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Unit Price (RM)', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Qty', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Subtotal (RM)', 1, 1, 'C', true);


$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0);
$itemNumber = 1;

while ($detail = $details_result->fetch_assoc()) {
    $pdf->Cell(20, 8, $itemNumber, 1, 0, 'C');
    $pdf->Cell(85, 8, $detail['name'], 1, 0, 'C');
    $pdf->Cell(30, 8, $detail['color'], 1, 0, 'C'); // Product color
    $pdf->Cell(30, 8, number_format($detail['unit_price'], 2), 1, 0, 'C');
    $pdf->Cell(20, 8, $detail['quantity'], 1, 0, 'C');
    $pdf->Cell(35, 8, number_format($detail['total_price'], 2), 1, 1, 'C');
    $itemNumber++;
}


$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(151, 6, 'Grand Total:', 0, 0, 'R');
$pdf->Cell(40, 6, 'RM ' . number_format($order['Grand_total'], 2), 0, 1, 'R');
$pdf->Cell(151, 6, 'Discount:', 0, 0, 'R');
$pdf->Cell(40, 6, '- RM ' . number_format($order['discount_amount'], 2), 0, 1, 'R');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(151, 10, 'Total Amount:', 0, 0, 'R');
$pdf->Cell(40, 10, 'RM ' . number_format($order['final_amount'], 2), 0, 1, 'R');


$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(128);
$pdf->Cell(0, 6, 'Thank you for your purchase! We appreciate your business and hope to see you again soon.', 0, 1, 'C');

$pdf->Output('D', 'Receipt_Order_' . $order['order_id'] . '.pdf');
?>
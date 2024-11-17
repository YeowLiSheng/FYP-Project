<?php
require('fpdf/fpdf.php');
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if order_id is set
if (!isset($_GET['order_id'])) {
    die("Invalid order ID.");
}

$order_id = intval($_GET['order_id']);

// Fetch order details
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
    die("Order not found.");
}

$order = $order_result->fetch_assoc();

// Fetch order items
$details_stmt = $conn->prepare("
    SELECT od.product_id, od.product_name, od.quantity, od.unit_price, od.total_price
    FROM order_details od
    WHERE od.order_id = ?
");
$details_stmt->bind_param("i", $order_id);
$details_stmt->execute();
$details_result = $details_stmt->get_result();

// Create PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Header
$pdf->Cell(0, 10, 'Order Receipt', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Order ID: ' . $order['order_id'], 0, 1);
$pdf->Cell(0, 10, 'Customer: ' . $order['user_name'], 0, 1);
$pdf->Cell(0, 10, 'Order Date: ' . date("Y-m-d H:i:s", strtotime($order['order_date'])), 0, 1);

// Line break
$pdf->Ln(5);

// Order details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Products:', 0, 1);
$pdf->SetFont('Arial', '', 11);

// Table header
$pdf->Cell(80, 8, 'Product Name', 1);
$pdf->Cell(30, 8, 'Quantity', 1);
$pdf->Cell(40, 8, 'Unit Price (RM)', 1);
$pdf->Cell(40, 8, 'Total Price (RM)', 1);
$pdf->Ln();

// Table content
while ($detail = $details_result->fetch_assoc()) {
    $pdf->Cell(80, 8, $detail['product_name'], 1);
    $pdf->Cell(30, 8, $detail['quantity'], 1);
    $pdf->Cell(40, 8, number_format($detail['unit_price'], 2), 1);
    $pdf->Cell(40, 8, number_format($detail['total_price'], 2), 1);
    $pdf->Ln();
}

// Line break
$pdf->Ln(5);

// Pricing summary
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Pricing Summary:', 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, 'Grand Total: RM ' . number_format($order['Grand_total'], 2), 0, 1);
$pdf->Cell(0, 8, 'Discount: - RM ' . number_format($order['discount_amount'], 2), 0, 1);
$pdf->Cell(0, 8, 'Delivery Charge: + RM ' . number_format($order['delivery_charge'], 2), 0, 1);
$pdf->Cell(0, 8, 'Final Amount: RM ' . number_format($order['final_amount'], 2), 0, 1);

// Output PDF for download
$pdf->Output('D', 'Receipt_Order_' . $order['order_id'] . '.pdf');
?>

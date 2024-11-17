<?php
require('fpdf/fpdf.php');
session_start();

// Database connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the order ID is passed
if (!isset($_GET['order_id'])) {
    die("Invalid order ID.");
}

$order_id = intval($_GET['order_id']);

// Prepare and execute statements to fetch order and details
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

$details_stmt = $conn->prepare("
    SELECT od.product_name, od.quantity, od.unit_price, od.total_price
    FROM order_details od
    WHERE od.order_id = ?
");
$details_stmt->bind_param("i", $order_id);
$details_stmt->execute();
$details_result = $details_stmt->get_result();

// Create PDF with custom format
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(0, 128, 0);
$pdf->Cell(0, 10, 'Receipt', 0, 1, 'C');

// Logo and Header Section
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Company Logo', 0, 1, 'R');

// Order and Customer Info
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0);
$pdf->Cell(100, 10, 'Order ID: ' . $order['order_id'], 0, 0);
$pdf->Cell(0, 10, 'Date: ' . date("Y-m-d", strtotime($order['order_date'])), 0, 1);
$pdf->Cell(100, 10, 'Customer: ' . $order['user_name'], 0, 0);
$pdf->Cell(0, 10, 'Status: ' . $order['order_status'], 0, 1);
$pdf->Ln(5);

// Product Table Header
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(20, 10, 'Qty', 1, 0, 'C', true);
$pdf->Cell(80, 10, 'Description', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Unit Price', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Amount', 1, 1, 'C', true);

// Product Table Content
$pdf->SetFont('Arial', '', 11);
while ($detail = $details_result->fetch_assoc()) {
    $pdf->Cell(20, 10, $detail['quantity'], 1);
    $pdf->Cell(80, 10, $detail['product_name'], 1);
    $pdf->Cell(30, 10, 'RM ' . number_format($detail['unit_price'], 2), 1);
    $pdf->Cell(30, 10, 'RM ' . number_format($detail['total_price'], 2), 1, 1);
}

$pdf->Ln(5);

// Pricing Details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(130, 10, 'Grand Total', 0, 0);
$pdf->Cell(30, 10, 'RM ' . number_format($order['Grand_total'], 2), 0, 1, 'R');
$pdf->Cell(130, 10, 'Discount', 0, 0);
$pdf->Cell(30, 10, '- RM ' . number_format($order['discount_amount'], 2), 0, 1, 'R');
$pdf->Cell(130, 10, 'Delivery Charge', 0, 0);
$pdf->Cell(30, 10, '+ RM ' . number_format($order['delivery_charge'], 2), 0, 1, 'R');
$pdf->Cell(130, 10, 'Final Amount', 0, 0);
$pdf->SetTextColor(0, 128, 0);
$pdf->Cell(30, 10, 'RM ' . number_format($order['final_amount'], 2), 0, 1, 'R');

// Output PDF
$pdf->Output('D', 'receipt_order_' . $order['order_id'] . '.pdf');
$conn->close();
?>

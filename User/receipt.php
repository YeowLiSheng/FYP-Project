<?php
require('fpdf/fpdf.php');
session_start();

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if order ID is provided
if (!isset($_GET['order_id'])) {
    die("Invalid order ID.");
}

$order_id = intval($_GET['order_id']);

// Fetch order and user details
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
$order = $order_stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

// Fetch order details
$details_stmt = $conn->prepare("
    SELECT od.product_name, od.quantity, od.unit_price, od.total_price
    FROM order_details od
    WHERE od.order_id = ?
");
$details_stmt->bind_param("i", $order_id);
$details_stmt->execute();
$details_result = $details_stmt->get_result();

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(0, 51, 102); // Custom color

// Header
$pdf->Cell(0, 10, 'Order Receipt', 0, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);

// Order details
$pdf->Cell(0, 10, 'Order ID: ' . $order['order_id'], 0, 1);
$pdf->Cell(0, 10, 'Order Date: ' . date('Y-m-d H:i:s', strtotime($order['order_date'])), 0, 1);
$pdf->Cell(0, 10, 'Customer: ' . $order['user_name'], 0, 1);
$pdf->Cell(0, 10, 'Shipping Address: ' . $order['shipping_address'], 0, 1);
$pdf->Cell(0, 10, 'Shipping Method: ' . $order['shipping_method'], 0, 1);
$pdf->Cell(0, 10, 'User Message: ' . (!empty($order['user_message']) ? $order['user_message'] : 'N/A'), 0, 1);
$pdf->Ln(5);

// Product details header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(80, 10, 'Product Name', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Quantity', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Unit Price (RM)', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Total Price (RM)', 1, 1, 'C', true);

// Product details rows
$pdf->SetFont('Arial', '', 12);
while ($detail = $details_result->fetch_assoc()) {
    $pdf->Cell(80, 10, $detail['product_name'], 1);
    $pdf->Cell(30, 10, $detail['quantity'], 1, 0, 'C');
    $pdf->Cell(40, 10, number_format($detail['unit_price'], 2), 1, 0, 'C');
    $pdf->Cell(40, 10, number_format($detail['total_price'], 2), 1, 1, 'C');
}

// Pricing details
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Pricing Summary', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Grand Total: RM ' . number_format($order['Grand_total'], 2), 0, 1);
$pdf->Cell(0, 10, 'Discount: -RM ' . number_format($order['discount_amount'], 2), 0, 1);
$pdf->Cell(0, 10, 'Delivery Charge: +RM ' . number_format($order['delivery_charge'], 2), 0, 1);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Final Amount: RM ' . number_format($order['final_amount'], 2), 0, 1);

// Output PDF
$pdf->Output('D', 'receipt_order_' . $order['order_id'] . '.pdf');
?>

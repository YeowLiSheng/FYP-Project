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
    SELECT od.product_id, od.product_name, od.quantity, od.unit_price, od.total_price
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
$pdf->SetFillColor(230, 230, 230);
$pdf->SetTextColor(50, 50, 50);

// Header Section
$pdf->Cell(0, 10, 'Order Receipt', 0, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('Arial', '', 12);

// Order Summary Section
$pdf->Cell(0, 10, "Order ID: " . $order['order_id'], 0, 1, 'L');
$pdf->Cell(0, 10, "User: " . $order['user_name'], 0, 1, 'L');
$pdf->Cell(0, 10, "Order Date: " . date("Y-m-d H:i:s", strtotime($order['order_date'])), 0, 1, 'L');
$pdf->Cell(0, 10, "Status: " . $order['order_status'], 0, 1, 'L');
$pdf->Cell(0, 10, "Shipping Address: " . $order['shipping_address'], 0, 1, 'L');
$pdf->Cell(0, 10, "Shipping Method: " . $order['shipping_method'], 0, 1, 'L');
$pdf->Cell(0, 10, "User Message: " . (!empty($order['user_message']) ? $order['user_message'] : 'N/A'), 0, 1, 'L');
$pdf->Ln(5);

// Product Details Section
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(50, 10, 'Product Name', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Quantity', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Unit Price (RM)', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Total Price (RM)', 1, 1, 'C', true);

while ($detail = $details_result->fetch_assoc()) {
    $pdf->Cell(50, 10, $detail['product_name'], 1);
    $pdf->Cell(30, 10, $detail['quantity'], 1);
    $pdf->Cell(40, 10, number_format($detail['unit_price'], 2), 1);
    $pdf->Cell(40, 10, number_format($detail['total_price'], 2), 1, 1);
}

$pdf->Ln(5);

// Pricing Details Section
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Pricing Details', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(100, 10, 'Grand Total:', 0, 0);
$pdf->Cell(40, 10, 'RM ' . number_format($order['Grand_total'], 2), 0, 1, 'R');
$pdf->Cell(100, 10, 'Discount:', 0, 0);
$pdf->Cell(40, 10, '- RM ' . number_format($order['discount_amount'], 2), 0, 1, 'R');
$pdf->Cell(100, 10, 'Delivery Charge:', 0, 0);
$pdf->Cell(40, 10, '+ RM ' . number_format($order['delivery_charge'], 2), 0, 1, 'R');
$pdf->Cell(100, 10, 'Final Amount:', 0, 0);
$pdf->Cell(40, 10, 'RM ' . number_format($order['final_amount'], 2), 0, 1, 'R');

// Output PDF
$pdf->Output('D', 'receipt_order_' . $order['order_id'] . '.pdf');

$conn->close();
?>

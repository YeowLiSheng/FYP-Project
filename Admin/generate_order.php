<?php
require('../User/fpdf/fpdf.php');

// Database connection
include 'dataconnection.php';

// Check connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Create PDF instance
$pdf = new FPDF();
$pdf->AddPage();

// Logo
$pdf->Image('../User/images/YLS2.jpg', 85, 10, 40); // Center the logo
$pdf->Ln(20);

// Title
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'YLS Atelier - Order List', 0, 1, 'C');
$pdf->Ln(10);

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 200, 200); // Light gray background
$pdf->Cell(25, 10, 'Order#', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Customer Name', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Order Time', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Shipped To', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Total', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Order Status', 1, 1, 'C', true);

// Fetch Data
$pdf->SetFont('Arial', '', 10);
$query = "SELECT orders.order_id, user.user_name, orders.order_date, 
                 orders.shipping_address, orders.final_amount, orders.order_status
          FROM orders 
          JOIN user ON orders.user_id = user.user_id";
$result = $connect->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Format and display each row
        $pdf->Cell(25, 10, $row['order_id'], 1, 0, 'C');
        $pdf->Cell(40, 10, $row['user_name'], 1, 0, 'C');
        $pdf->Cell(40, 10, date('d/m/Y H:i:s', strtotime($row['order_date'])), 1, 0, 'C');
        $pdf->Cell(50, 10, $row['shipping_address'], 1, 0, 'C');
        $pdf->Cell(25, 10, 'RM ' . number_format($row['final_amount'], 2), 1, 0, 'C');
        $pdf->Cell(30, 10, $row['order_status'], 1, 1, 'C');
    }
} else {
    $pdf->Cell(0, 10, 'No orders found.', 1, 1, 'C');
}

// Footer
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetY(-15);
$pdf->Cell(0, 10, 'Generated on ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Output the PDF
$pdf->Output('D', 'Order_List.pdf');
?>
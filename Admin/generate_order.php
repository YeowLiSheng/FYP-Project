<?php
require('../User/fpdf/fpdf.php');

// Database connection
include 'dataconnection.php';

// Check connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Create a new PDF instance
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Title
$pdf->Cell(0, 10, 'YLS Atelier', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Order List', 0, 1, 'C');
$pdf->Ln(10);

// Table Header
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 10, 'Order#', 1);
$pdf->Cell(40, 10, 'Customer Name', 1);
$pdf->Cell(40, 10, 'Order Time', 1);
$pdf->Cell(60, 10, 'Shipped To', 1);
$pdf->Cell(20, 10, 'Total', 1);
$pdf->Cell(30, 10, 'Order Status', 1);
$pdf->Ln();

// Fetch Data
$query = "SELECT *, user.user_name, orders.order_date AS order_datetime FROM orders 
          JOIN user ON orders.user_id = user.user_id";
$result = $connect->query($query);

if ($result->num_rows > 0) {
    $pdf->SetFont('Arial', '', 10);
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(20, 10, $row['order_id'], 1);
        $pdf->Cell(40, 10, $row['user_name'], 1);
        $pdf->Cell(40, 10, date('d/m/Y H:i:s', strtotime($row['order_datetime'])), 1);
        $pdf->Cell(60, 10, $row['shipping_address'], 1);
        $pdf->Cell(20, 10, 'RM' . number_format($row['final_amount'], 2), 1);
        $pdf->Cell(30, 10, $row['order_status'], 1);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(0, 10, 'No orders found.', 1, 1, 'C');
}

// Output the PDF
$pdf->Output('D', 'Order_List.pdf');
?>

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

// Logo at the top-left
$pdf->Image('../User/images/YLS2.jpg', 10, 10, 30); // Adjusted position and size
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetXY(50, 15); // Set position for title to the right of the logo
$pdf->Cell(0, 10, 'YLS Atelier - Order List', 0, 1, 'L'); // Align title with logo

$pdf->Ln(15); // Add spacing below title

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230); // Light gray background for the header
$pdf->SetDrawColor(180, 180, 180); // Border color

$header = [
    ['Order#', 20],
    ['Customer Name', 40],
    ['Order Time', 35],
    ['Shipped To', 50],
    ['Total (RM)', 25],
    ['Order Status', 30]
];
foreach ($header as $col) {
    $pdf->Cell($col[1], 10, $col[0], 1, 0, 'C', true);
}
$pdf->Ln();

// Fetch Data
$pdf->SetFont('Arial', '', 10);
$query = "SELECT orders.order_id, user.user_name, orders.order_date, 
                 orders.shipping_address, orders.final_amount, orders.order_status
          FROM orders 
          JOIN user ON orders.user_id = user.user_id";
$result = $connect->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Adjust row content
        $order_id = $row['order_id'];
        $customer_name = $row['user_name'];
        $order_time = date('d/m/Y H:i:s', strtotime($row['order_date']));
        $shipped_to = $row['shipping_address'];
        $total = 'RM ' . number_format($row['final_amount'], 2);
        $order_status = $row['order_status'];

        // Handle word wrapping for the 'Shipped To' column
        $cell_width = 50; // Width of 'Shipped To'
        $cell_height = 6; // Height of each wrapped line
        $line_count = ceil($pdf->GetStringWidth($shipped_to) / $cell_width);

        // Output row data
        $pdf->Cell(20, $cell_height * $line_count, $order_id, 1, 0, 'C');
        $pdf->Cell(40, $cell_height * $line_count, $customer_name, 1, 0, 'C');
        $pdf->Cell(35, $cell_height * $line_count, $order_time, 1, 0, 'C');
        $x = $pdf->GetX(); // Save x position
        $y = $pdf->GetY(); // Save y position
        $pdf->MultiCell($cell_width, $cell_height, $shipped_to, 1, 'C');
        $pdf->SetXY($x + $cell_width, $y); // Move to next cell
        $pdf->Cell(25, $cell_height * $line_count, $total, 1, 0, 'C');
        $pdf->Cell(30, $cell_height * $line_count, $order_status, 1, 1, 'C');
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

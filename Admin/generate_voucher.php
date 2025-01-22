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
$pdf->Image('../User/images/YLS2.jpg', 10, 10, 30);
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetXY(50, 15); // Position title beside the logo
$pdf->Cell(0, 10, 'YLS Atelier - Voucher List', 0, 1, 'L'); // Align title with logo

$pdf->Ln(20); // Add spacing below the title

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230); // Light gray background
$pdf->SetDrawColor(180, 180, 180); // Border color

$header = [
    ['Voucher Code', 30],
    ['Discount Rate', 25],
    ['Usage Limit', 25],
    ['Min. Amount', 30],
    ['Status', 20],
    ['Description', 60] // Wider column for description
];

$left_margin = 10; 
$pdf->SetX($left_margin);
foreach ($header as $col) {
    $pdf->Cell($col[1], 10, $col[0], 1, 0, 'C', true);
}
$pdf->Ln();

// Fetch Data
$pdf->SetFont('Arial', '', 10);
$query = "SELECT * FROM voucher";
$result = $connect->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $voucher_code = $row['voucher_code'];
        $discount_rate = $row['discount_rate'] . '%';
        $usage_limit = $row['usage_limit'];
        $min_amount = '$' . number_format($row['minimum_amount'], 2);
        $status = $row['voucher_status'];
        $description = $row['voucher_des'];

        $pdf->SetX($left_margin);

        // Add each column except description
        $pdf->Cell(30, 6, $voucher_code, 1, 0, 'C');
        $pdf->Cell(25, 6, $discount_rate, 1, 0, 'C');
        $pdf->Cell(25, 6, $usage_limit, 1, 0, 'C');
        $pdf->Cell(30, 6, $min_amount, 1, 0, 'C');
        $pdf->Cell(20, 6, $status, 1, 0, 'C');

        // Add description with automatic line breaks
        $x = $pdf->GetX(); // Get current X position
        $y = $pdf->GetY(); // Get current Y position
        $pdf->MultiCell(60, 6, $description, 1, 'L'); // Multiline cell for description

        // Reset position to align other rows correctly
        $pdf->SetXY($x + 60, $y);
        $pdf->Ln(); // Move to the next row
    }
} else {
    $pdf->SetX($left_margin);
    $pdf->Cell(0, 10, 'No vouchers found.', 1, 1, 'C');
}

// Footer
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetY(-15);
$pdf->Cell(0, 10, 'Generated on ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Output the PDF
$pdf->Output('D', 'Voucher_List.pdf');
?>

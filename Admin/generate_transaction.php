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
$pdf->Cell(0, 10, 'YLS Atelier - Transaction Record', 0, 1, 'L'); // Align title with logo

$pdf->Ln(20); // Add spacing below title (adjusted to move table down)

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230); // Light gray background for the header
$pdf->SetDrawColor(180, 180, 180); // Border color

$header = [
    ['Transaction#', 30],
    ['Customer Name', 50],
    ['Order ID', 35],
    ['Transaction 
    Amount', 40],
    ['Date', 40]
];

// Adjust left margin
$left_margin = 10; 
$pdf->SetX($left_margin); // Set initial left margin
foreach ($header as $col) {
    $pdf->Cell($col[1], 10, $col[0], 1, 0, 'C', true);
}
$pdf->Ln();

// Fetch Data
$pdf->SetFont('Arial', '', 10);
$query = "SELECT *, user.user_name, payment.payment_date AS payment_datetime FROM payment JOIN user ON payment.user_id = user.user_id;";
$result = $connect->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Adjust row content
        $transaction_id = $row['payment_id'];
        $customer_name = $row['user_name'];
        $order_id = $row['order_id'];
        $transaction_amount = 'RM ' . number_format($row['payment_amount'], 2);
        $payment_date = date('d/m/Y H:i:s', strtotime($row['payment_date']));

        // Set left margin for row data
        $pdf->SetX($left_margin);

        // Output row data (adjusting widths and alignment for each column)
        $pdf->Cell(30, 6, $transaction_id, 1, 0, 'C');
        $pdf->Cell(50, 6, $customer_name, 1, 0, 'L');  // Align customer name to the left
        $pdf->Cell(35, 6, $order_id, 1, 0, 'C');
        $pdf->Cell(40, 6, $transaction_amount, 1, 0, 'C');
        $pdf->Cell(40, 6, $payment_date, 1, 1, 'C');
    }
} else {
    $pdf->SetX($left_margin);
    $pdf->Cell(0, 10, 'No transaction found.', 1, 1, 'C');
}

// Footer
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetY(-15);
$pdf->Cell(0, 10, 'Generated on ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Output the PDF
$pdf->Output('D', 'Transaction_List.pdf');
?>

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
$pdf->SetXY(50, 15);
$pdf->Cell(0, 10, 'YLS Atelier - Product List', 0, 1, 'L');

$pdf->Ln(20);

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230);
$pdf->SetDrawColor(180, 180, 180);

$header = [
    ['Product ID', 25],
    ['Product Name', 40],
    ['Tags', 30],
    ['Color', 30],
    ['Category', 25],
    ['Status', 25]
];

$left_margin = 10;
$pdf->SetX($left_margin);
foreach ($header as $col) {
    $pdf->Cell($col[1], 10, $col[0], 1, 0, 'C', true);
}
$pdf->Ln();

// Fetch Data
$pdf->SetFont('Arial', '', 10);
$query = "SELECT 
         product.product_id, 
         product.product_name,
         product.tags,
         product_variant.color,
         category.category_name,
         product_status.product_status
         FROM product
         JOIN category ON product.category_id = category.category_id
         JOIN product_status ON product.product_status = product_status.p_status_id
         JOIN product_variant ON product.product_id = product_variant.product_id";
$result = $connect->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->SetX($left_margin);

        // Output each column with MultiCell for product name
        $pdf->Cell(25, 10, $row['product_id'], 1, 0, 'C');
        
        // Using MultiCell for product name to handle long text
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->MultiCell(40, 10, $row['product_name'], 1, 'C');
        $pdf->SetXY($x + 40, $y); // Move the cursor to the right of the MultiCell
        
        $pdf->Cell(30, 10, $row['tags'], 1, 0, 'C');
        $pdf->Cell(30, 10, $row['color'], 1, 0, 'C');
        $pdf->Cell(25, 10, $row['category_name'], 1, 0, 'C');
        $pdf->Cell(25, 10, $row['product_status'], 1, 1, 'C');
    }
} else {
    $pdf->SetX($left_margin);
    $pdf->Cell(0, 10, 'No products found.', 1, 1, 'C');
}

// Footer
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetY(-15);
$pdf->Cell(0, 10, 'Generated on ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Output the PDF
$pdf->Output('D', 'Product_List.pdf');
?>

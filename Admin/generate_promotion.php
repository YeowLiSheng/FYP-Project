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
$pdf->SetXY(50, 15); // Set position for title to the right of the logo
$pdf->Cell(0, 10, 'YLS Atelier - Promotion Product List', 0, 1, 'L'); 

$pdf->Ln(20); // Spacing below title

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230);
$pdf->SetDrawColor(180, 180, 180);

$header = [
    ['Promotion ID', 30],
    ['Promotion Name', 40],
    ['Tags', 30],
    ['Color', 30],
    ['Category', 25],
    ['Status', 25] 
];

// Adjust left margin
$left_margin = 10; 
$pdf->SetX($left_margin);
foreach ($header as $col) {
    $pdf->Cell($col[1], 10, $col[0], 1, 0, 'C', true);
}
$pdf->Ln();

// Fetch Data
$pdf->SetFont('Arial', '', 10);
$query = "SELECT 
         promotion_product.promotion_id, 
         promotion_product.promotion_name,
         promotion_product.tags,
         product_variant.color,
         category.category_name,
         product_status.product_status
         FROM promotion_product
         JOIN category ON promotion_product.category_id = category.category_id
         JOIN product_status ON promotion_product.promotion_status = product_status.p_status_id
         JOIN product_variant ON promotion_product.promotion_id = product_variant.promotion_id";
$result = $connect->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Fetch data
        $promotion_id = $row['promotion_id'];
        $promotion_name = $row['promotion_name'];
        $tags = $row['tags'];
        $color = $row['color'];
        $category_name = $row['category_name'];
        $status = $row['product_status'];

        // Calculate line count for 'Promotion Name'
        $cell_width = 40; 
        $cell_height = 8; 
        $line_count = max(1, ceil($pdf->GetStringWidth($promotion_name) / ($cell_width - 2))); 

        // Calculate row height
        $row_height = $cell_height * $line_count;

        $pdf->SetX($left_margin);
        $pdf->Cell(30, $row_height, $promotion_id, 1, 0, 'C');

        // Handle 'Promotion Name' with MultiCell
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->MultiCell($cell_width, $cell_height, $promotion_name, 1, 'C');

        // Align other cells
        $pdf->SetXY($x + $cell_width, $y);
        $pdf->Cell(30, $row_height, $tags, 1, 0, 'C');
        $pdf->Cell(30, $row_height, $color, 1, 0, 'C');
        $pdf->Cell(25, $row_height, $category_name, 1, 0, 'C');
        $pdf->Cell(25, $row_height, $status, 1, 1, 'C');
    }
} else {
    $pdf->SetX($left_margin);
    $pdf->Cell(0, 10, 'No promotion products found.', 1, 1, 'C');
}

// Footer
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetY(-15);
$pdf->Cell(0, 10, 'Generated on ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Output the PDF
$pdf->Output('D', 'Promotion_Product_List.pdf');
?>

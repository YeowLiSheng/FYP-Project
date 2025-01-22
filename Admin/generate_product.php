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
$pdf->Cell(0, 10, 'YLS Atelier - Product List', 0, 1, 'L'); // Align title with logo

$pdf->Ln(20); // Add spacing below title (adjusted to move table down)

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230); // Light gray background for the header
$pdf->SetDrawColor(180, 180, 180); // Border color

$header = [
    ['Product ID', 30],
    ['Product Name', 40],
    ['Tags', 30],
    ['Color', 30],
    ['Category', 25],
    ['Status', 25] // Adjusted widths for table columns
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
        // Fetch data for each row
        $product_id = $row['product_id'];
        $product_name = $row['product_name'];
        $tags = $row['tags'];
        $color = $row['color'];
        $category_name = $row['category_name'];
        $status = $row['product_status'];

        // Set left margin for row data
        $pdf->SetX($left_margin);

        // Output row data
        $pdf->Cell(20, 10, $product_id, 1, 0, 'C');
        $pdf->Cell(40, 10, $product_name, 1, 0, 'C');
        $pdf->Cell(30, 10, $tags, 1, 0, 'C');
        $pdf->Cell(30, 10, $color, 1, 0, 'C');
        $pdf->Cell(25, 10, $category_name, 1, 0, 'C');
        $pdf->Cell(25, 10, $status, 1, 1, 'C');
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

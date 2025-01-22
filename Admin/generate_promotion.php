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
$pdf->Cell(0, 10, 'YLS Atelier - Promotion Product List', 0, 1, 'L'); // Align title with logo

$pdf->Ln(20); // Add spacing below title (adjusted to move table down)

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230); // Light gray background for the header
$pdf->SetDrawColor(180, 180, 180); // Border color

$header = [
    ['Promotion ID', 30],
    ['Promotion Name', 40],
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
    // Step 1: Calculate maximum row height
$max_row_height = 0; // Initialize the maximum row height
$cell_height = 6; // Base height per line

$data = []; // Store all data rows for reuse
while ($row = $result->fetch_assoc()) {
    // Store row data
    $data[] = $row;

    // Fetch data for calculations
    $promotion_name = $row['promotion_name'];
    $tags = $row['tags'];
    $color = $row['color'];
    $category_name = $row['category_name'];
    $status = $row['product_status'];

    // Calculate the number of lines for each column
    $promotion_name_lines = ceil($pdf->GetStringWidth($promotion_name) / 40);
    $tags_lines = ceil($pdf->GetStringWidth($tags) / 30);
    $color_lines = ceil($pdf->GetStringWidth($color) / 30);
    $category_name_lines = ceil($pdf->GetStringWidth($category_name) / 25);
    $status_lines = ceil($pdf->GetStringWidth($status) / 25);

    // Determine the maximum number of lines for this row
    $max_lines = max($promotion_name_lines, $tags_lines, $color_lines, $category_name_lines, $status_lines);

    // Calculate row height and update maximum height
    $row_height = $cell_height * $max_lines;
    $max_row_height = max($max_row_height, $row_height);
}

// Step 2: Render table with uniform row height
foreach ($data as $row) {
    // Fetch data for each row
    $promotion_id = $row['promotion_id'];
    $promotion_name = $row['promotion_name'];
    $tags = $row['tags'];
    $color = $row['color'];
    $category_name = $row['category_name'];
    $status = $row['product_status'];

    // Set left margin for row data
    $pdf->SetX($left_margin);

    // Output row data with the maximum row height for non-wrapping cells
    $pdf->Cell(30, $max_row_height, $promotion_id, 1, 0, 'C');

    // MultiCell for text wrapping with manual height adjustment
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell(40, $cell_height, $promotion_name, 1, 'C');

    // Adjust the position back to align with the next cell
    $pdf->SetXY($x + 40, $y);
    
    // Fill in the gap if the MultiCell doesn't use the full height
    $current_height = $pdf->GetY() - $y;
    if ($current_height < $max_row_height) {
        $pdf->SetXY($x, $y + $max_row_height);
    }

    // Output other cells with the same maximum height
    $pdf->Cell(30, $max_row_height, $tags, 1, 0, 'C');
    $pdf->Cell(30, $max_row_height, $color, 1, 0, 'C');
    $pdf->Cell(25, $max_row_height, $category_name, 1, 0, 'C');
    $pdf->Cell(25, $max_row_height, $status, 1, 1, 'C'); // Move to the next line
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

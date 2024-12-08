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
$pdf->Cell(0, 10, 'YLS Atelier - Product Reviews Summary', 0, 1, 'L');

$pdf->Ln(20); // Add spacing below title

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230); // Light gray background for the header
$pdf->SetDrawColor(180, 180, 180); // Border color

$header = [
    ['Product Name', 60],
    ['Category', 40],
    ['Total Reviews', 30],
    ['Average Rating', 30],
    ['Latest Review', 30],
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
$review = "
    SELECT 
        p.product_name, 
        c.category_name, 
        COUNT(r.review_id) AS total_reviews,
        ROUND(AVG(r.rating), 1) AS avg_rating,
        MAX(r.created_at) AS latest_review
    FROM product p
    INNER JOIN category c ON p.category_id = c.category_id
    INNER JOIN order_details od ON p.product_id = od.product_id
    INNER JOIN reviews r ON od.detail_id = r.detail_id
    WHERE r.status = 'active'
    GROUP BY p.product_name, c.category_name
    ORDER BY latest_review DESC
";

$reviewresult = $connect->query($review);

if ($reviewresult->num_rows > 0) {
    while ($row = $reviewresult->fetch_assoc()) {
        $product_name = $row['product_name'];
        $category_name = $row['category_name'];
        $total_reviews = $row['total_reviews'];
        $avg_rating = $row['avg_rating'];
        $latest_review = date('d/m/Y', strtotime($row['latest_review']));

        // Calculate max number of lines required
        $line_count = max(
            ceil($pdf->GetStringWidth($product_name) / 60),
            ceil($pdf->GetStringWidth($category_name) / 40),
            1
        );
        $cell_height = 6 * $line_count;

        // Product Name (multi-line)
        $pdf->SetX($left_margin);
        $pdf->MultiCell(60, 6, $product_name, 1, 'C');
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        // Category
        $pdf->SetXY($x + 60, $y - $cell_height);
        $pdf->MultiCell(40, 6, $category_name, 1, 'C');

        // Total Reviews
        $pdf->SetXY($x + 100, $y - $cell_height);
        $pdf->Cell(30, $cell_height, $total_reviews, 1, 0, 'C');

        // Average Rating
        $pdf->SetXY($x + 130, $y - $cell_height);
        $pdf->Cell(30, $cell_height, $avg_rating, 1, 0, 'C');

        // Latest Review
        $pdf->SetXY($x + 160, $y - $cell_height);
        $pdf->Cell(30, $cell_height, $latest_review, 1, 1, 'C');
    }
} else {
    $pdf->SetX($left_margin);
    $pdf->Cell(0, 10, 'No reviews found.', 1, 1, 'C');
}

// Footer
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetY(-15);
$pdf->Cell(0, 10, 'Generated on ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Output the PDF
$pdf->Output('D', 'Product_Reviews_Summary.pdf');
?>

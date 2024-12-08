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
$pdf->Cell(0, 10, 'YLS Atelier - Reviewed Products', 0, 1, 'L'); // Align title with logo

$pdf->Ln(20); // Add spacing below title (adjusted to move table down)

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230); // Light gray background for the header
$pdf->SetDrawColor(180, 180, 180); // Border color

$header = [
    ['Image', 25],
    ['Product Name', 40],
    ['Category', 30],
    ['Total Reviews', 30],
    ['Avg Rating', 25],
    ['Latest Review', 40]
];

$pdf->SetX(10); // Adjust left margin
foreach ($header as $col) {
    $pdf->Cell($col[1], 10, $col[0], 1, 0, 'C', true);
}
$pdf->Ln();

// Fetch Data
$pdf->SetFont('Arial', '', 10);
$review = "
    SELECT 
        p.product_name, 
        p.product_image, 
        c.category_name, 
        COUNT(r.review_id) AS total_reviews,
        ROUND(AVG(r.rating), 1) AS avg_rating,
        MAX(r.created_at) AS latest_review
    FROM product p
    INNER JOIN category c ON p.category_id = c.category_id
    INNER JOIN order_details od ON p.product_id = od.product_id
    INNER JOIN reviews r ON od.detail_id = r.detail_id
    WHERE r.status = 'active'
    GROUP BY p.product_id, p.product_name, p.product_image, c.category_name
    ORDER BY latest_review DESC
";

$result = $connect->query($review);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $product_name = $row['product_name'];
        $product_image = '../User/images/' . $row['product_image']; // Adjusted path
        $category = $row['category_name'];
        $total_reviews = $row['total_reviews'];
        $avg_rating = $row['avg_rating'];
        $latest_review = date('d/m/Y H:i:s', strtotime($row['latest_review']));

        $pdf->SetX(10);

        // Product image
        if (file_exists($product_image)) {
            $pdf->Cell(25, 25, $pdf->Image($product_image, $pdf->GetX() + 2, $pdf->GetY() + 2, 20, 20), 1, 0, 'C', false);
        } else {
            $pdf->Cell(25, 25, 'No Image', 1, 0, 'C');
        }

        // Other columns
        $pdf->Cell(40, 25, $product_name, 1, 0, 'C');
        $pdf->Cell(30, 25, $category, 1, 0, 'C');
        $pdf->Cell(30, 25, $total_reviews, 1, 0, 'C');
        $pdf->Cell(25, 25, $avg_rating, 1, 0, 'C');
        $pdf->Cell(40, 25, $latest_review, 1, 1, 'C');
    }
} else {
    $pdf->SetX(10);
    $pdf->Cell(0, 10, 'No reviewed products found.', 1, 1, 'C');
}

// Footer
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetY(-15);
$pdf->Cell(0, 10, 'Generated on ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Output the PDF
$pdf->Output('D', 'Reviewed_Products.pdf');
?>

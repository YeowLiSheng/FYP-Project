<?php
require('../User/fpdf/fpdf.php');

// Database connection
include 'dataconnection.php';

// Function to convert AVIF to JPG (if GD library supports AVIF)
function convertAvifToJpeg($avifPath, $outputPath) {
    if (function_exists('imagecreatefromavif')) { // Check if AVIF is supported
        $image = imagecreatefromavif($avifPath);
        if ($image !== false) {
            imagejpeg($image, $outputPath);
            imagedestroy($image);
            return $outputPath;
        }
    }
    return false;
}

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
$pdf->Cell(0, 10, 'YLS Atelier - Product Reviews', 0, 1, 'L');

$pdf->Ln(20);

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230); // Light gray background
$pdf->SetDrawColor(180, 180, 180);

$header = [
    ['Product Image', 30],
    ['Product Name', 40],
    ['Category', 35],
    ['Total Reviews', 25],
    ['Avg Rating', 25],
    ['Latest Review', 35]
];

// Set table header
foreach ($header as $col) {
    $pdf->Cell($col[1], 10, $col[0], 1, 0, 'C', true);
}
$pdf->Ln();

// Fetch Data
$pdf->SetFont('Arial', '', 10);
$review = "
    SELECT 
        p.product_id, 
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
        $category_name = $row['category_name'];
        $total_reviews = $row['total_reviews'];
        $avg_rating = $row['avg_rating'];
        $latest_review = date('d/m/Y H:i:s', strtotime($row['latest_review']));
        $product_image = '../User/images/' . $row['product_image'];

        // Check if image is supported
        $supported_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $image_extension = strtolower(pathinfo($product_image, PATHINFO_EXTENSION));
        
        if ($image_extension === 'avif') {
            $converted_image = convertAvifToJpeg($product_image, '../User/images/temp.jpg');
            if ($converted_image) {
                $product_image = $converted_image; // Use converted image
            } else {
                $product_image = '../User/images/placeholder.png'; // Use placeholder if conversion fails
            }
        } elseif (!in_array($image_extension, $supported_extensions)) {
            $product_image = '../User/images/placeholder.png'; // Use placeholder for unsupported formats
        }

        // Product Image
        $x = $pdf->GetX(); 
        $y = $pdf->GetY(); 
        $pdf->Cell(30, 20, '', 1, 0, 'C');
        if (file_exists($product_image)) {
            $pdf->Image($product_image, $x + 5, $y + 3, 20, 15);
        }
        $pdf->SetXY($x + 30, $y);

        // Other columns
        $pdf->Cell(40, 20, $product_name, 1, 0, 'C');
        $pdf->Cell(35, 20, $category_name, 1, 0, 'C');
        $pdf->Cell(25, 20, $total_reviews, 1, 0, 'C');
        $pdf->Cell(25, 20, $avg_rating, 1, 0, 'C');
        $pdf->Cell(35, 20, $latest_review, 1, 1, 'C');
    }
} else {
    $pdf->Cell(0, 10, 'No reviewed products found.', 1, 1, 'C');
}

// Footer
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetY(-15);
$pdf->Cell(0, 10, 'Generated on ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Output the PDF
$pdf->Output('D', 'Product_Reviews.pdf');
?>

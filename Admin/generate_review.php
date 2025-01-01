<?php
require('../User/fpdf/fpdf.php');
include 'dataconnection.php';

// Function to fetch review data
function fetchReviewData($connect) {
    $query = "
        SELECT 
            item.item_id AS review_item_id,
            item.item_name AS review_item_name,
            item.item_image AS review_item_image,
            item.category_name AS review_category_name,
            item.item_type AS review_item_type, 

            COUNT(r.review_id) AS total_reviews,
            ROUND(AVG(r.rating), 1) AS avg_rating,
            MAX(r.created_at) AS latest_review
        FROM (
            SELECT 
                p.product_id AS item_id,
                p.product_name AS item_name,
                p.product_image AS item_image,
                c.category_name AS category_name,
                'product' AS item_type,
                od.detail_id
            FROM product p
            INNER JOIN category c ON p.category_id = c.category_id
            INNER JOIN order_details od ON p.product_id = od.product_id
            WHERE od.product_id IS NOT NULL

            UNION ALL

            SELECT 
                pp.package_id AS item_id,
                pp.package_name AS item_name,
                pp.package_image AS item_image,
                'Package' AS category_name,
                'package' AS item_type,
                od.detail_id
            FROM product_package pp
            INNER JOIN order_details od ON pp.package_id = od.package_id
            WHERE od.package_id IS NOT NULL
        ) AS item
        INNER JOIN reviews r ON item.detail_id = r.detail_id
        WHERE r.status = 'active'
        GROUP BY review_item_id, review_item_name, review_item_image, review_category_name
        ORDER BY latest_review DESC
    ";
    return $connect->query($query);
}

// Create PDF instance
$pdf = new FPDF();
$pdf->AddPage();

// Logo
$pdf->Image('../User/images/YLS2.jpg', 10, 10, 30);
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetXY(50, 15);
$pdf->Cell(0, 10, 'YLS Atelier - Product & Package Reviews', 0, 1, 'L');
$pdf->Ln(20);

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230); // Light gray background
$pdf->SetDrawColor(180, 180, 180);
$header = [
    ['Item Name', 50],
    ['Category', 40],
    ['Total Reviews', 30],
    ['Avg Rating', 30],
    ['Latest Review', 40]
];

// Add table header
foreach ($header as $col) {
    $pdf->Cell($col[1], 10, $col[0], 1, 0, 'C', true);
}
$pdf->Ln();

// Fetch review data
$result = fetchReviewData($connect);

if ($result->num_rows > 0) {
    $pdf->SetFont('Arial', '', 10);
    while ($row = $result->fetch_assoc()) {
        $item_name = $row['review_item_name'];
        $category_name = $row['review_category_name'];
        $total_reviews = $row['total_reviews'];
        $avg_rating = $row['avg_rating'];
        $latest_review = date('d/m/Y H:i:s', strtotime($row['latest_review']));

        // Data rows
        $pdf->Cell(50, 10, $item_name, 1, 0, 'C');
        $pdf->Cell(40, 10, $category_name, 1, 0, 'C');
        $pdf->Cell(30, 10, $total_reviews, 1, 0, 'C');
        $pdf->Cell(30, 10, $avg_rating, 1, 0, 'C');
        $pdf->Cell(40, 10, $latest_review, 1, 1, 'C');
    }
} else {
    $pdf->Cell(0, 10, 'No reviewed products or packages found.', 1, 1, 'C');
}

// Footer
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetY(-15);
$pdf->Cell(0, 10, 'Generated on ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Output the PDF
$pdf->Output('D', 'Product_and_Package_Reviews.pdf');
?>

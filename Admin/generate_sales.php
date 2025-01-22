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

// Logo and title
$pdf->Image('../User/images/YLS2.jpg', 10, 10, 30); // Adjusted position and size
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetXY(50, 15); // Position title
$pdf->Cell(0, 10, 'YLS Atelier - Sales Report', 0, 1, 'L');

$pdf->Ln(10); // Space below title

// Add a section for Monthly Sales
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Monthly Sales Summary', 0, 1, 'L');
$pdf->Ln(5);

// Monthly Sales Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230);
$pdf->SetDrawColor(180, 180, 180);

$monthlyHeader = [
    ['Month', 70],
    ['Total Sales (RM)', 60]
];
foreach ($monthlyHeader as $col) {
    $pdf->Cell($col[1], 10, $col[0], 1, 0, 'C', true);
}
$pdf->Ln();

// Fetch Monthly Sales Data
$selectedYear = date('Y');
$monthlySales_query = "
    SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, SUM(final_amount) AS monthly_sales 
    FROM orders 
    WHERE YEAR(order_date) = '$selectedYear'
    GROUP BY DATE_FORMAT(order_date, '%Y-%m') 
    ORDER BY DATE_FORMAT(order_date, '%Y-%m')";
$monthlySales_result = $connect->query($monthlySales_query);

// Populate monthly data
$pdf->SetFont('Arial', '', 10);
if ($monthlySales_result->num_rows > 0) {
    while ($row = $monthlySales_result->fetch_assoc()) {
        $pdf->Cell(70, 10, $row['month'], 1, 0, 'C');
        $pdf->Cell(60, 10, 'RM ' . number_format($row['monthly_sales'], 2), 1, 1, 'C');
    }
} else {
    $pdf->Cell(0, 10, 'No monthly sales data found.', 1, 1, 'C');
}

$pdf->Ln(10); // Space between sections

// Add a section for Yearly Sales
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Yearly Sales Summary', 0, 1, 'L');
$pdf->Ln(5);

// Yearly Sales Header
$pdf->SetFont('Arial', 'B', 12);
$yearlyHeader = [
    ['Year', 70],
    ['Total Sales (RM)', 60]
];
foreach ($yearlyHeader as $col) {
    $pdf->Cell($col[1], 10, $col[0], 1, 0, 'C', true);
}
$pdf->Ln();

// Fetch Yearly Sales Data
$yearlySales_query = "
    SELECT YEAR(order_date) AS year, SUM(final_amount) AS yearly_sales 
    FROM orders 
    GROUP BY YEAR(order_date) 
    ORDER BY YEAR(order_date)";
$yearlySales_result = $connect->query($yearlySales_query);

// Populate yearly data
$pdf->SetFont('Arial', '', 10);
if ($yearlySales_result->num_rows > 0) {
    while ($row = $yearlySales_result->fetch_assoc()) {
        $pdf->Cell(70, 10, $row['year'], 1, 0, 'C');
        $pdf->Cell(60, 10, 'RM ' . number_format($row['yearly_sales'], 2), 1, 1, 'C');
    }
} else {
    $pdf->Cell(0, 10, 'No yearly sales data found.', 1, 1, 'C');
}

$pdf->Ln(10); // Space between sections

// Add a section for Category Sales
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Category Sales Summary', 0, 1, 'L');
$pdf->Ln(5);

// Category Sales Header
$pdf->SetFont('Arial', 'B', 12);
$categoryHeader = [
    ['Category', 100],
    ['Total Quantity Sold', 60]
];
foreach ($categoryHeader as $col) {
    $pdf->Cell($col[1], 10, $col[0], 1, 0, 'C', true);
}
$pdf->Ln();

// Fetch Category Sales Data
$categorySales_query = "
    SELECT 
        c.category_name, 
        SUM(od.quantity) AS total_quantity
    FROM order_details od
    LEFT JOIN product_variant pv ON od.variant_id = pv.variant_id
    LEFT JOIN product p ON pv.product_id = p.product_id
    LEFT JOIN promotion_product pp ON pv.promotion_id = pp.promotion_id
    LEFT JOIN category c 
        ON p.category_id = c.category_id 
        OR pp.category_id = c.category_id
    GROUP BY c.category_id";
$categorySales_result = $connect->query($categorySales_query);

// Populate category data
$pdf->SetFont('Arial', '', 10);
if ($categorySales_result->num_rows > 0) {
    while ($row = $categorySales_result->fetch_assoc()) {
        $pdf->Cell(100, 10, $row['category_name'], 1, 0, 'C');
        $pdf->Cell(60, 10, $row['total_quantity'], 1, 1, 'C');
    }
} else {
    $pdf->Cell(0, 10, 'No category sales data found.', 1, 1, 'C');
}

$pdf->Ln(20); // Space before footer

// Footer
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetY(-15);
$pdf->Cell(0, 10, 'Generated on ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Output the PDF
$pdf->Output('D', 'Sales_Report.pdf');
?>

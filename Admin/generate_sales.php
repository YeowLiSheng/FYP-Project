<?php
require('../User/fpdf/fpdf.php');

// Database connection
include 'dataconnection.php';

// Check connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Fetch sales trend data (getting data for the last 30 days)
$salesTrend_query = "SELECT DATE(order_date) AS date, SUM(final_amount) AS daily_sales 
                     FROM orders 
                     WHERE DATE(order_date) >= CURDATE() - INTERVAL 30 DAY
                     GROUP BY DATE(order_date) 
                     ORDER BY DATE(order_date)";
$salesTrend_result = $connect->query($salesTrend_query);
$salesTrend = $salesTrend_result->fetch_all(MYSQLI_ASSOC);

// Fetch yearly sales data (all years)
$yearlySales_query = "SELECT YEAR(order_date) AS year, SUM(final_amount) AS yearly_sales 
                      FROM orders 
                      GROUP BY YEAR(order_date) 
                      ORDER BY YEAR(order_date)";
$yearlySales_result = $connect->query($yearlySales_query);
$yearlySales = $yearlySales_result->fetch_all(MYSQLI_ASSOC);

// Fetch category-wise sales data (all categories)
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
$categorySales = $categorySales_result->fetch_all(MYSQLI_ASSOC);

// Calculate total quantity for percentages
$totalQuantity = array_sum(array_column($categorySales, 'total_quantity'));

// Initialize PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Title and Logo
$pdf->Image('../User/images/YLS2.jpg', 10, 10, 30);
$pdf->Cell(50);
$pdf->Cell(100, 10, 'Sales Report', 0, 1, 'C');
$pdf->Ln(10);

// Section: Sales Trend
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Sales Trend (Last 30 Days)', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);

// Add table headers
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(50, 8, 'Date', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Daily Sales (RM)', 1, 1, 'C', true);

if (!empty($salesTrend)) {
    foreach ($salesTrend as $data) {
        $pdf->Cell(50, 8, date('d/m/Y', strtotime($data['date'])), 1);
        $pdf->Cell(50, 8, 'RM ' . number_format($data['daily_sales'], 2), 1, 1);
    }
} else {
    $pdf->Cell(0, 10, 'No data available.', 1, 1, 'C');
}
$pdf->Ln(10);

// Section: Yearly Sales
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Yearly Sales', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);

// Add table headers
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(50, 8, 'Year', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Total Sales (RM)', 1, 1, 'C', true);

if (!empty($yearlySales)) {
    foreach ($yearlySales as $data) {
        $pdf->Cell(50, 8, $data['year'], 1);
        $pdf->Cell(50, 8, 'RM ' . number_format($data['yearly_sales'], 2), 1, 1);
    }
} else {
    $pdf->Cell(0, 10, 'No data available.', 1, 1, 'C');
}
$pdf->Ln(10);

// Section: Category Sales
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Category-wise Sales', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);

// Add table headers
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(60, 8, 'Category', 1, 0, 'C', true);
$pdf->Cell(60, 8, 'Quantity Sold', 1, 0, 'C', true);
$pdf->Cell(60, 8, 'Percentage (%)', 1, 1, 'C', true);

if (!empty($categorySales)) {
    foreach ($categorySales as $data) {
        $percentage = $totalQuantity > 0 ? ($data['total_quantity'] / $totalQuantity) * 100 : 0;
        $pdf->Cell(60, 8, $data['category_name'], 1);
        $pdf->Cell(60, 8, $data['total_quantity'], 1);
        $pdf->Cell(60, 8, number_format($percentage, 2) . '%', 1, 1);
    }
} else {
    $pdf->Cell(0, 10, 'No data available.', 1, 1, 'C');
}

// Footer
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetY(-15);
$pdf->Cell(0, 10, 'Generated on ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Output PDF
$pdf->Output('D', 'Sales_Report_' . date('Ymd') . '.pdf');
?>

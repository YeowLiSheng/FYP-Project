<?php
require('../User/fpdf/fpdf.php');

// Database connection
include 'dataconnection.php';

// Check connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Fetch required data (reuse logic from your existing script)

// 1. Sales trend data
$salesTrend_query = "SELECT DATE(order_date) AS date, SUM(final_amount) AS daily_sales 
                      FROM orders 
                      WHERE DATE(order_date) BETWEEN '$startDate' AND '$endDate' 
                      GROUP BY DATE(order_date) 
                      ORDER BY DATE(order_date)";
$salesTrend_result = $connect->query($salesTrend_query);
$salesTrend = $salesTrend_result->fetch_all(MYSQLI_ASSOC);

// 2. Monthly sales
$monthlySales_query = "
    SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, SUM(final_amount) AS monthly_sales 
    FROM orders 
    WHERE YEAR(order_date) = '$selectedYear'
    GROUP BY DATE_FORMAT(order_date, '%Y-%m') 
    ORDER BY DATE_FORMAT(order_date, '%Y-%m')";
$monthlySales_result = $connect->query($monthlySales_query);
$monthlySales = $monthlySales_result->fetch_all(MYSQLI_ASSOC);

// 3. Yearly sales
$yearlySales_query = "
    SELECT YEAR(order_date) AS year, SUM(final_amount) AS yearly_sales 
    FROM orders 
    GROUP BY YEAR(order_date) 
    ORDER BY YEAR(order_date) DESC";
$yearlySales_result = $connect->query($yearlySales_query);
$yearlySales = $yearlySales_result->fetch_all(MYSQLI_ASSOC);

// 4. Category sales
$categorySales_query = "
    SELECT 
        c.category_name, 
        SUM(od.quantity) AS total_quantity
    FROM order_details od
    LEFT JOIN product_variant pv ON od.variant_id = pv.variant_id
    LEFT JOIN product p ON pv.product_id = p.product_id
    LEFT JOIN category c ON p.category_id = c.category_id
    GROUP BY c.category_id";
$categorySales_result = $connect->query($categorySales_query);
$categorySalesData = $categorySales_result->fetch_all(MYSQLI_ASSOC);

// Create PDF instance
$pdf = new FPDF();
$pdf->AddPage();

// Logo
$pdf->Image('../User/images/YLS2.jpg', 10, 10, 30);
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetXY(50, 15);
$pdf->Cell(0, 10, 'YLS Atelier - Sales Report', 0, 1, 'L');

$pdf->Ln(20);

// Section 1: Sales Trend
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Sales Trend (Daily)', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);

$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(50, 8, 'Date', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Sales (RM)', 1, 1, 'C', true);

foreach ($salesTrend as $row) {
    $pdf->Cell(50, 8, $row['date'], 1, 0, 'C');
    $pdf->Cell(50, 8, 'RM ' . number_format($row['daily_sales'], 2), 1, 1, 'C');
}

$pdf->Ln(10);

// Section 2: Monthly Sales
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Monthly Sales', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);

$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(50, 8, 'Month', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Sales (RM)', 1, 1, 'C', true);

foreach ($monthlySales as $row) {
    $pdf->Cell(50, 8, $row['month'], 1, 0, 'C');
    $pdf->Cell(50, 8, 'RM ' . number_format($row['monthly_sales'], 2), 1, 1, 'C');
}

$pdf->Ln(10);

// Section 3: Yearly Sales
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Yearly Sales', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);

$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(50, 8, 'Year', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Sales (RM)', 1, 1, 'C', true);

foreach ($yearlySales as $row) {
    $pdf->Cell(50, 8, $row['year'], 1, 0, 'C');
    $pdf->Cell(50, 8, 'RM ' . number_format($row['yearly_sales'], 2), 1, 1, 'C');
}

$pdf->Ln(10);

// Section 4: Category-wise Sales
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Category-wise Sales', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);

$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(60, 8, 'Category', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Quantity', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Percentage', 1, 1, 'C', true);

$totalQuantity = array_sum(array_column($categorySalesData, 'total_quantity'));
foreach ($categorySalesData as $data) {
    $percentage = ($data['total_quantity'] / $totalQuantity) * 100;
    $pdf->Cell(60, 8, $data['category_name'], 1, 0, 'C');
    $pdf->Cell(30, 8, $data['total_quantity'], 1, 0, 'C');
    $pdf->Cell(30, 8, number_format($percentage, 2) . '%', 1, 1, 'C');
}

// Footer
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetY(-15);
$pdf->Cell(0, 10, 'Generated on ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Output the PDF
$pdf->Output('D', 'Sales_Report.pdf');
?>

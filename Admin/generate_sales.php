<?php
require('../User/fpdf/fpdf.php');

// Database connection
include 'dataconnection.php';

// Check connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Get POST data or use default values
$startDate = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d');
$selectedYear = isset($_POST['selected_year']) ? $_POST['selected_year'] : date('Y');

// Fetch sales trend data
$salesTrend_query = "SELECT DATE(order_date) AS date, SUM(final_amount) AS daily_sales 
                     FROM orders 
                     WHERE DATE(order_date) BETWEEN '$startDate' AND '$endDate'
                     GROUP BY DATE(order_date) 
                     ORDER BY DATE(order_date)";
$salesTrend_result = $connect->query($salesTrend_query);
$salesTrend = $salesTrend_result->fetch_all(MYSQLI_ASSOC);

// Fetch monthly sales data
$monthlySales_query = "SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, SUM(final_amount) AS monthly_sales 
                       FROM orders 
                       WHERE YEAR(order_date) = '$selectedYear'
                       GROUP BY DATE_FORMAT(order_date, '%Y-%m') 
                       ORDER BY DATE_FORMAT(order_date, '%Y-%m')";
$monthlySales_result = $connect->query($monthlySales_query);
$monthlySales = $monthlySales_result->fetch_all(MYSQLI_ASSOC);

// Fetch category-wise sales data
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
$categorySales = $categorySales_result->fetch_all(MYSQLI_ASSOC);

// Calculate total quantity for percentages
$totalQuantity = array_sum(array_column($categorySales, 'total_quantity'));

// Initialize PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Title and Logo
$pdf->Image('../User/images/YLS2.jpg', 10, 10, 30);
$pdf->Cell(190, 10, 'Sales Report', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(190, 10, "Date Range: $startDate to $endDate", 0, 1, 'C');
$pdf->Ln(10);

// Sales Trend Section
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(190, 10, 'Sales Trend', 0, 1, 'L');
$pdf->SetFont('Arial', '', 12);
foreach ($salesTrend as $trend) {
    $pdf->Cell(50, 10, $trend['date'], 1);
    $pdf->Cell(50, 10, '$' . number_format($trend['daily_sales'], 2), 1, 1);
}
$pdf->Ln(10);

// Monthly Sales Section
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(190, 10, "Monthly Sales for $selectedYear", 0, 1, 'L');
$pdf->SetFont('Arial', '', 12);
foreach ($monthlySales as $month) {
    $pdf->Cell(50, 10, $month['month'], 1);
    $pdf->Cell(50, 10, '$' . number_format($month['monthly_sales'], 2), 1, 1);
}
$pdf->Ln(10);

// Category-Wise Sales Section
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(190, 10, 'Category-wise Sales', 0, 1, 'L');
$pdf->SetFont('Arial', '', 12);
foreach ($categorySales as $category) {
    $categoryName = $category['category_name'] ?: 'Uncategorized';
    $quantity = $category['total_quantity'];
    $percentage = $totalQuantity > 0 ? ($quantity / $totalQuantity) * 100 : 0;
    $pdf->Cell(60, 10, $categoryName, 1);
    $pdf->Cell(40, 10, $quantity, 1);
    $pdf->Cell(40, 10, number_format($percentage, 2) . '%', 1, 1);
}

// Output PDF
$pdf->Output('I', 'Sales_Report.pdf');
?>

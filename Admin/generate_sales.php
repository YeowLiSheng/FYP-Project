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
$pdf->Cell(50);
$pdf->Cell(100, 10, 'Sales Report', 0, 1, 'C');
$pdf->Ln(10);

// Section: Sales Trend
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Sales Trend (' . date('d/m/Y', strtotime($startDate)) . ' - ' . date('d/m/Y', strtotime($endDate)) . ')', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
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

// Section: Monthly Sales
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Monthly Sales (' . $selectedYear . ')', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(50, 8, 'Month', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Monthly Sales (RM)', 1, 1, 'C', true);

if (!empty($monthlySales)) {
    foreach ($monthlySales as $data) {
        $pdf->Cell(50, 8, $data['month'], 1);
        $pdf->Cell(50, 8, 'RM ' . number_format($data['monthly_sales'], 2), 1, 1);
    }
} else {
    $pdf->Cell(0, 10, 'No data available.', 1, 1, 'C');
}
$pdf->Ln(10);

// Section: Category-wise Sales
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Category-wise Sales', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
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

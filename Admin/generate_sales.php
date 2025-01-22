<?php
require('../User/fpdf/fpdf.php');
require('../User/fpdf/fpdi.php'); // For importing images as chart representations

// Database connection
include 'dataconnection.php';

// Check connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Create PDF instance
$pdf = new FPDI();
$pdf->AddPage();

// Logo and title
$pdf->Image('../User/images/YLS2.jpg', 10, 10, 30);
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetXY(50, 15);
$pdf->Cell(0, 10, 'YLS Atelier - Sales Analytics Report', 0, 1, 'L');

// Fetch general statistics
$orderCount = $order_count ?? 0;
$totalSales = $totalSales ?? 0.00;
$totalCustomers = $total_customers ?? 0;
$totalItemsSold = $total_item_sold ?? 0;

// Add general statistics section
$pdf->Ln(20);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'General Statistics', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, "Total Orders: $orderCount", 0, 1);
$pdf->Cell(0, 8, "Total Sales: RM " . number_format($totalSales, 2), 0, 1);
$pdf->Cell(0, 8, "Total Customers: $totalCustomers", 0, 1);
$pdf->Cell(0, 8, "Total Products Sold: $totalItemsSold", 0, 1);

// Sales trend section
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Sales Trends', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 8, "The following chart shows daily sales trends for the selected date range ($startDate to $endDate):");

// Insert sales trend chart image
$salesTrendChart = '../charts/sales_trend_chart.png'; // Path to pre-generated chart
if (file_exists($salesTrendChart)) {
    $pdf->Image($salesTrendChart, 10, $pdf->GetY() + 5, 190);
    $pdf->Ln(65); // Adjust space after the chart
} else {
    $pdf->Cell(0, 10, 'Sales trend chart is unavailable.', 0, 1, 'C');
}

// Monthly sales section
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Monthly Sales', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 8, "The following chart shows monthly sales for the year $selectedYear:");

// Insert monthly sales chart image
$monthlySalesChart = '../charts/monthly_sales_chart.png'; // Path to pre-generated chart
if (file_exists($monthlySalesChart)) {
    $pdf->Image($monthlySalesChart, 10, $pdf->GetY() + 5, 190);
    $pdf->Ln(65); // Adjust space after the chart
} else {
    $pdf->Cell(0, 10, 'Monthly sales chart is unavailable.', 0, 1, 'C');
}

// Category sales breakdown
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Category Sales Breakdown', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 8, "The chart below illustrates the sales breakdown by category:");

// Insert category sales chart image
$categorySalesChart = '../charts/category_sales_chart.png'; // Path to pre-generated chart
if (file_exists($categorySalesChart)) {
    $pdf->Image($categorySalesChart, 10, $pdf->GetY() + 5, 190);
    $pdf->Ln(65); // Adjust space after the chart
} else {
    $pdf->Cell(0, 10, 'Category sales chart is unavailable.', 0, 1, 'C');
}

// Footer
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetY(-15);
$pdf->Cell(0, 10, 'Generated on ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Output the PDF
$pdf->Output('D', 'Sales_Analytics_Report.pdf');
?>

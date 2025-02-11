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
$pdf->Cell(0, 10, 'YLS Atelier - Product List', 0, 1, 'L'); // Align title with logo

$pdf->Ln(20); // Add spacing below title (adjusted to move table down)

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230); // Light gray background for the header
$pdf->SetDrawColor(180, 180, 180); // Border color

$header = [
    ['Product ID', 30],
    ['Product Name', 40],
    ['Tags', 30],
    ['Color', 30],
    ['Category', 25],
    ['Status', 25]
];

// Adjust left margin
$left_margin = 10;
$pdf->SetX($left_margin); // Set initial left margin
foreach ($header as $col) {
    $pdf->Cell($col[1], 10, $col[0], 1, 0, 'C', true);
}
$pdf->Ln();

// Fetch Data
$pdf->SetFont('Arial', '', 10);
$query = "SELECT 
         product.product_id, 
         product.product_name,
         product.tags,
         product_variant.color,
         category.category_name,
         product_status.product_status
         FROM product
         JOIN category ON product.category_id = category.category_id
         JOIN product_status ON product.product_status = product_status.p_status_id
         JOIN product_variant ON product.product_id = product_variant.product_id";
$result = $connect->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // 数据获取
        $product_id = $row['product_id'];
        $product_name = $row['product_name'];
        $tags = $row['tags'];
        $color = $row['color'];
        $category_name = $row['category_name'];
        $status = $row['product_status'];
    
        // 定义列宽
        $col_widths = [30, 40, 30, 30, 25, 25];
        $cell_height = 6; // 单行高度
    
        // 计算 Product Name 需要的行数
        $line_count = max(
            ceil($pdf->GetStringWidth($product_name) / $col_widths[1]), // 计算 Product Name 需要的行数
            1 // 最少占用 1 行
        );
    
        // **输出数据**
        $pdf->SetX($left_margin);
        $pdf->Cell($col_widths[0], $cell_height * $line_count, $product_id, 1, 0, 'C'); // Product ID
    
        // **使用 MultiCell() 显示 Product Name**
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->MultiCell($col_widths[1], $cell_height, $product_name, 1, 'C');
        $pdf->SetXY($x + $col_widths[1], $y); // 移动到下一列
    
        // **后续列的数据**
        $pdf->Cell($col_widths[2], $cell_height * $line_count, $tags, 1, 0, 'C');
        $pdf->Cell($col_widths[3], $cell_height * $line_count, $color, 1, 0, 'C');
        $pdf->Cell($col_widths[4], $cell_height * $line_count, $category_name, 1, 0, 'C');
        $pdf->Cell($col_widths[5], $cell_height * $line_count, $status, 1, 1, 'C');
    }
    
} else {
    $pdf->SetX($left_margin);
    $pdf->Cell(0, 10, 'No products found.', 1, 1, 'C');
}

// Footer
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetY(-15);
$pdf->Cell(0, 10, 'Generated on ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Output the PDF
$pdf->Output('D', 'Product_List.pdf');
?>

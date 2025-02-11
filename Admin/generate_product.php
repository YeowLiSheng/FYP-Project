<?php
require('../User/fpdf/fpdf.php');
include('../config.php');

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Product List', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

$left_margin = 10;
$pdf->SetLeftMargin($left_margin);

// 定义表头
$header = ['Product ID', 'Product Name', 'Tags', 'Color', 'Category', 'Status'];
$col_widths = [30, 50, 30, 30, 25, 25];  // 调整列宽
$cell_height = 6;

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 200, 200);
$pdf->SetX($left_margin);

foreach ($header as $key => $col) {
    $pdf->Cell($col_widths[$key], $cell_height, $col, 1, 0, 'C', true);
}
$pdf->Ln();

$pdf->SetFont('Arial', '', 10);

$query = "SELECT product_id, product_name, tags, color, category_name, product_status FROM product";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $product_id = $row['product_id'];
    $product_name = $row['product_name'];
    $tags = $row['tags'];
    $color = $row['color'];
    $category_name = $row['category_name'];
    $status = $row['product_status'];

    // 计算每一列的最大行数，保证行高对齐
    $max_lines = max(
        ceil($pdf->GetStringWidth($product_name) / $col_widths[1]),
        ceil($pdf->GetStringWidth($tags) / $col_widths[2]),
        ceil($pdf->GetStringWidth($color) / $col_widths[3]),
        ceil($pdf->GetStringWidth($category_name) / $col_widths[4]),
        ceil($pdf->GetStringWidth($status) / $col_widths[5]),
        1
    );

    $row_height = $cell_height * $max_lines;
    
    $pdf->SetX($left_margin);
    
    // 输出表格数据（全部使用 MultiCell 以确保换行对齐）
    $pdf->Cell($col_widths[0], $row_height, $product_id, 1, 0, 'C');

    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell($col_widths[1], $cell_height, $product_name, 1, 'C'); 
    $pdf->SetXY($x + $col_widths[1], $y);

    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell($col_widths[2], $cell_height, $tags, 1, 'C');
    $pdf->SetXY($x + $col_widths[2], $y);

    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell($col_widths[3], $cell_height, $color, 1, 'C');
    $pdf->SetXY($x + $col_widths[3], $y);

    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell($col_widths[4], $cell_height, $category_name, 1, 'C');
    $pdf->SetXY($x + $col_widths[4], $y);

    $pdf->MultiCell($col_widths[5], $cell_height, $status, 1, 'C');
}

$pdf->Output();
?>

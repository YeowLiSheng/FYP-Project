<?php
include 'dataconnection.php';
require("php_libs/fpdf.php");

date_default_timezone_set("Asia/Kuching");
$time = date("dmY");

if (isset($_POST["admin_pdf"])) {
    $pdf = new FPDF("P", "mm", "A4");

    // 添加页面
    $pdf->AddPage();

    // 标题部分
    $pdf->SetFont("Arial", "B", 16);
    $pdf->Cell(0, 10, "Bag Shop - Product Reviews Report", 0, 1, "C");
    $pdf->Ln(10); // 添加空行

    // 表头
    $pdf->SetFont("Arial", "B", 12);
    $pdf->SetFillColor(200, 220, 255); // 设置表头背景颜色
    $pdf->Cell(20, 10, "#", 1, 0, "C", true);
    $pdf->Cell(30, 10, "Image", 1, 0, "C", true);
    $pdf->Cell(50, 10, "Product Name", 1, 0, "C", true);
    $pdf->Cell(40, 10, "Category", 1, 0, "C", true);
    $pdf->Cell(25, 10, "Reviews", 1, 0, "C", true);
    $pdf->Cell(25, 10, "Rating", 1, 0, "C", true);
    $pdf->Cell(50, 10, "Latest Review", 1, 1, "C", true);

    // 表格内容
    $pdf->SetFont("Arial", "", 10);

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

    $reviewresult = $connect->query($review);
    if ($reviewresult->num_rows > 0) {
        $index = 1;
        while ($row = $reviewresult->fetch_assoc()) {
            $pdf->Cell(20, 10, $index++, 1, 0, "C");
            $pdf->Cell(30, 10, $pdf->Image("../User/images/" . $row["product_image"], $pdf->GetX() + 2, $pdf->GetY() + 2, 16), 1, 0, "C");
            $pdf->Cell(50, 10, $row["product_name"], 1, 0, "L");
            $pdf->Cell(40, 10, $row["category_name"], 1, 0, "L");
            $pdf->Cell(25, 10, $row["total_reviews"], 1, 0, "C");
            $pdf->Cell(25, 10, $row["avg_rating"], 1, 0, "C");
            $pdf->Cell(50, 10, $row["latest_review"], 1, 1, "C");
        }
    } else {
        $pdf->Cell(0, 10, "No reviewed products found.", 1, 1, "C");
    }

    // 输出 PDF
    $pdf->Output("I", "{$time}_review_report.pdf");
}
?>

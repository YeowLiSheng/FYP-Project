<?php
include 'dataconnection.php';
require ('../User/fpdf/fpdf.php');

date_default_timezone_set("Asia/Kuching");
$time = date("dmY");

if (isset($_POST["admin_pdf"])) {
    $pdf = new FPDF("P", "mm", "A4");
    $pdf->AddPage();

    // 添加 logo 和标题
    $pdf->Image('../User/images/YLS2.jpg', 10, 10, 30);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetXY(50, 15);
    $pdf->Cell(0, 10, 'Bag Shop - Admin List', 0, 1, 'L');

    $pdf->Ln(20); // 增加空白行

    // 表头
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(230, 230, 230); // 浅灰色背景
    $pdf->SetDrawColor(180, 180, 180); // 边框颜色

    $header = [
        ['#', 10],
        ['Staff ID', 35],
        ['Name', 40],
        ['Email', 75]
    ];

    foreach ($header as $col) {
        $pdf->Cell($col[1], 10, $col[0], 1, 0, 'C', true);
    }
    $pdf->Ln();

    // 获取数据并显示
    $pdf->SetFont('Arial', '', 12);
    $result = mysqli_query($connect, "SELECT staff_id, admin_name, admin_email FROM admin");
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_array($result)) {
            $pdf->Cell(10, 10, $row["staff_id"], 1, 0, 'C');
            $pdf->Cell(35, 10, $row["staff_id"], 1, 0, 'C');
            $pdf->Cell(40, 10, $row["admin_name"], 1, 0, 'C');
            $pdf->Cell(75, 10, $row["admin_email"], 1, 1, 'C');
        }
    } else {
        $pdf->Cell(0, 10, 'No records found.', 1, 1, 'C');
    }

    // 输出 PDF
    $pdf->Output('D', $time . '_admin_list.pdf');
}

if (isset($_POST["admin_excel"])) {
    $output = '';
    $result = mysqli_query($connect, "SELECT staff_id, admin_name, admin_email FROM admin");

    if ($result && mysqli_num_rows($result) > 0) {
        $output .= '
            <table class="table" bordered="1">
                <tr>
                    <th>#</th>
                    <th>Staff ID</th>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
        ';

        while ($row = mysqli_fetch_array($result)) {
            $output .= '
                <tr>
                    <td>' . $row["staff_id"] . '</td>
                    <td>' . $row["staff_id"] . '</td>
                    <td>' . $row["admin_name"] . '</td>
                    <td>' . $row["admin_email"] . '</td>
                </tr>
            ';
        }
        $output .= '</table>';
        header('Content-Type: application/xls');
        header('Content-Disposition: attachment; filename="' . $time . '_admin_list.xls"');
        echo $output;
    } else {
        echo "No records found.";
    }
}
?>

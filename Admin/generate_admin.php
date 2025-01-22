<?php
include 'dataconnection.php';
require("php_libs/fpdf.php");

date_default_timezone_set("Asia/Kuching");
$time = date("dmY");

if (isset($_POST["admin_pdf"])) {
    $pdf = new FPDF("P", "mm", "A4");

    // Add Page and Logo
    $pdf->AddPage();
    $pdf->Image('../User/images/YLS2.jpg', 10, 10, 30);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetXY(50, 15);
    $pdf->Cell(0, 10, 'YLS Atelier - Admin List', 0, 1, 'L');

    $pdf->Ln(20); // Add spacing below header

    // Table Header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetDrawColor(180, 180, 180);

    $header = [
        ['#', 20],
        ['Staff ID', 35],
        ['Name', 50],
        ['Email', 75],
    ];

    $left_margin = 10;
    $pdf->SetX($left_margin);
    foreach ($header as $col) {
        $pdf->Cell($col[1], 10, $col[0], 1, 0, 'C', true);
    }
    $pdf->Ln();

    // Fetch and Display Data
    $pdf->SetFont('Arial', '', 10);
    $result = mysqli_query($connect, "SELECT staff_id, admin_id, admin_name, admin_email FROM admin");
    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $pdf->SetX($left_margin);
            $pdf->Cell(20, 10, $row['staff_id'], 1, 0, 'C');
            $pdf->Cell(35, 10, $row['admin_id'], 1, 0, 'C');
            $pdf->Cell(50, 10, $row['admin_name'], 1, 0, 'C');
            $pdf->Cell(75, 10, $row['admin_email'], 1, 1, 'C');
        }
    } else {
        $pdf->SetX($left_margin);
        $pdf->Cell(0, 10, 'No admins found.', 1, 1, 'C');
    }

    // Footer
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetY(-15);
    $pdf->Cell(0, 10, 'Generated on ' . date('d/m/Y H:i:s'), 0, 0, 'C');


    $pdf->Output('D', 'Admin_List.pdf'); // 自动下载文件
}

if (isset($_POST["admin_excel"])) {
    $output = '';
    $excel = mysqli_query($connect, "SELECT staff_id, admin_id, admin_name, admin_email FROM admin");
    if ($excel->num_rows > 0) {
        $output .= '
            <table class="table" bordered="1">
                <tr style="background-color: #e6e6e6;">
                    <th>#</th>
                    <th>Staff ID</th>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
        ';
        while ($row = mysqli_fetch_assoc($excel)) {
            $output .= '
                <tr>
                    <td>' . $row["staff_id"] . '</td>
                    <td>' . $row["admin_id"] . '</td>
                    <td>' . $row["admin_name"] . '</td>
                    <td>' . $row["admin_email"] . '</td>
                </tr>
            ';
        }
        $output .= '</table>';
        header('Content-Type: application/xls');
        header('Content-Disposition: attachment; filename="' . $time . '_admin_report.xls"');
        echo $output;
    } else {
        echo "No record found :(";
    }
}
?>

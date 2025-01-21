<?php
include 'dataconnection.php';
require("php_libs/fpdf.php");

date_default_timezone_set("Asia/Kuching");
$time = date("dmY");

if (isset($_POST["cust_pdf"])) {
    $pdf = new FPDF("P", "mm", "A4");

    // Add Page and Logo
    $pdf->AddPage();
    $pdf->Image('../User/images/YLS2.jpg', 10, 10, 30);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetXY(50, 15);
    $pdf->Cell(0, 10, 'YLS Atelier - Customer List', 0, 1, 'L');

    $pdf->Ln(20); // Add spacing below header

    // Table Header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetDrawColor(180, 180, 180);

    $header = [
        ['#', 20],
        ['Username', 35],
        ['Tel', 40],
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
    $result = mysqli_query($connect, "SELECT user_id, user_name, user_contact_number, user_email FROM user");
    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $pdf->SetX($left_margin);
            $pdf->Cell(20, 10, $row['user_id'], 1, 0, 'C');
            $pdf->Cell(35, 10, $row['user_name'], 1, 0, 'C');
            $pdf->Cell(40, 10, $row['user_contact_number'], 1, 0, 'C');
            $pdf->Cell(75, 10, $row['user_email'], 1, 1, 'C');
        }
    } else {
        $pdf->SetX($left_margin);
        $pdf->Cell(0, 10, 'No customers found.', 1, 1, 'C');
    }

    // Footer
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetY(-15);
    $pdf->Cell(0, 10, 'Generated on ' . date('d/m/Y H:i:s'), 0, 0, 'C');

    $pdf->Output();
}

if (isset($_POST["cust_excel"])) {
    $output = '';
    $excel = mysqli_query($connect, "SELECT user_id, user_name, user_contact_number, user_email FROM user");
    if ($excel->num_rows > 0) {
        $output .= '
            <table class="table" bordered="1">
                <tr style="background-color: #e6e6e6;">
                    <th>#</th>
                    <th>Username</th>
                    <th>Tel</th>
                    <th>Email</th>
                </tr>
        ';
        while ($row = mysqli_fetch_assoc($excel)) {
            $output .= '
                <tr>
                    <td>' . $row["user_id"] . '</td>
                    <td>' . $row["user_name"] . '</td>
                    <td>' . $row["user_contact_number"] . '</td>
                    <td>' . $row["user_email"] . '</td>
                </tr>
            ';
        }
        $output .= '</table>';
        header('Content-Type: application/xls');
        header('Content-Disposition: attachment; filename="' . $time . '_customer_report.xls"');
        echo $output;
    } else {
        echo "No record found :(";
    }
}
?>

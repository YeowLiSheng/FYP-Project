<?php
include 'dataconnection.php';
require ("php_libs/fpdf.php");

date_default_timezone_set("Asia/Kuching");
$time = date("dmY");
if (isset($_POST["admin_pdf"])) {
    $pdf = new FPDF("p", "mm", "A4");

    $pdf->AddPage();

    //arial, font-weight, font-size
    $pdf->SetFont("Arial", "B", 40);

    //width, height, text, border, endline, [align]
    $pdf->Cell(130, 5, "Bag Shop", 0, 1);
    $pdf->Cell(130, 5, "", 0, 1);
    $pdf->SetFont("Arial", "", 15);
    $pdf->Cell(59, 5, "Admin List", 0, 1);

    $pdf->Cell(130, 10, "", 0, 1);
    $pdf->SetFont("Arial", "b", 12);
    $pdf->Cell(10, 5, "#", 1, 0);
    $pdf->Cell(35, 5, "Staff_Id", 1, 0);
    $pdf->Cell(40, 5, "Name", 1, 0);
    $pdf->Cell(75, 5, "Email", 1, 1);

    $pdf->SetFont("Arial", "", 12);
    $result = mysqli_query($connect, "SELECT staff_id, admin_id, admin_name, admin_email FROM admin");
    while ($row = mysqli_fetch_array($result)) {
        $pdf->Cell(10, 5, $row["staff_id"], 1, 0);
        $pdf->Cell(35, 5, $row["admin_id"], 1, 0);
        $pdf->Cell(40, 5, $row["admin_name"], 1, 0);
        $pdf->Cell(75, 5, $row["admin_email"], 1, 1);
    }

    $pdf->Output();
}
if (isset($_POST["admin_excel"])) {
    $output = "";

    if (isset($_POST["admin_excel"])) {
        $excel = mysqli_query($connect, "SELECT staff_id, admin_id, admin_name, admin_email FROM admin");

        if (mysqli_num_rows($excel) > 0) {
            $output .= '
                    <table class="table" bordered="1">
                        <tr>
                            <th>#</th>
                            <th>Admin_id</th>
                            <th>Name</th>
                            <th>Email</th>
                        </tr>
            ';

            while ($row = mysqli_fetch_array($excel)) {
                $output .= '
                        <tr>
                            <td>' . $row["staff_id"] . '</td>
                            <td>' . $row["admin_id"] . '</td>
                            <td>#' . $row["admin_name"] . '</td>
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
}

?>
<?php
include 'dataconnection.php';
require ("php_libs/fpdf.php");

date_default_timezone_set("Asia/Kuching");
$time = date("dmY");
if (isset($_POST["cust_pdf"])) {
    $pdf = new FPDF("p", "mm", "A4");

    $pdf->AddPage();

    //arial, font-weight, font-size
    $pdf->SetFont("Arial", "B", 40);

    //width, height, text, border, endline, [align]
    $pdf->Cell(130, 5, "Bag Shop", 0, 1);
    $pdf->Cell(130, 5, "", 0, 1);
    $pdf->SetFont("Arial", "", 15);
    $pdf->Cell(59, 5, "Customer List", 0, 1);

    $pdf->Cell(130, 10, "", 0, 1);
    $pdf->SetFont("Arial", "b", 12);
    $pdf->Cell(10, 5, "#", 1, 0);
    $pdf->Cell(35, 5, "Username", 1, 0);
    $pdf->Cell(40, 5, "Tel", 1, 0);
    $pdf->Cell(75, 5, "Email", 1, 1);

    $pdf->SetFont("Arial", "", 12);
    $result = mysqli_query($connect, "SELECT user_id, user_name, user_contact_number, user_email FROM user");
    while ($row = mysqli_fetch_array($result)) {
        $pdf->Cell(10, 5, $row["user_id"], 1, 0);
        $pdf->Cell(35, 5, $row["user_name"], 1, 0);
        $pdf->Cell(40, 5, $row["user_contact_number"], 1, 0);
        $pdf->Cell(75, 5, $row["user_email"], 1, 1);
    }

    $pdf->Output();
}
if (isset($_POST["cust_excel"])) {
    $output = "";

    if (isset($_POST["cust_excel"])) {
        $excel = mysqli_query($connect, "SELECT user_id, user_name, user_contact_number, user_email FROM user");

        if (mysqli_num_rows($excel) > 0) {
            $output .= '
                    <table class="table" bordered="1">
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Tel</th>
                            <th>Email</th>
                        </tr>
            ';

            while ($row = mysqli_fetch_array($excel)) {
                $output .= '
                        <tr>
                            <td>' . $row["user_id"] . '</td>
                            <td>' . $row["user_name"] . '</td>
                            <td>#' . $row["user_contact_number"] . '</td>
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
}

?>
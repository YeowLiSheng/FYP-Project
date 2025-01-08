<?php
// Include database connection and FPDF library
include 'dataconnection.php';
require("php_libs/fpdf.php");

// Set timezone and date
date_default_timezone_set("Asia/Kuching");
$time = date("dmY");

if (isset($_POST["admin_pdf"])) {
    // Create PDF instance
    $pdf = new FPDF("P", "mm", "A4");
    $pdf->AddPage();

    // Add Title and Header
    $pdf->SetFont("Arial", "B", 24);
    $pdf->Cell(0, 10, "Bag Shop - Admin List", 0, 1, "C");

    $pdf->Ln(10); // Add spacing below title

    // Add Table Header
    $pdf->SetFont("Arial", "B", 12);
    $pdf->SetFillColor(230, 230, 230); // Light gray background
    $pdf->SetDrawColor(180, 180, 180); // Border color
    $header = [
        ["#", 10],
        ["Staff ID", 35],
        ["Admin ID", 40],
        ["Name", 40],
        ["Email", 65]
    ];

    foreach ($header as $col) {
        $pdf->Cell($col[1], 10, $col[0], 1, 0, "C", true);
    }
    $pdf->Ln();

    // Fetch and Add Table Data
    $pdf->SetFont("Arial", "", 12);
    $result = $connect->query("SELECT staff_id, admin_id, admin_name, admin_email FROM admin");

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $pdf->Cell(10, 10, $row["staff_id"], 1, 0, "C");
            $pdf->Cell(35, 10, $row["admin_id"], 1, 0, "C");
            $pdf->Cell(40, 10, $row["admin_name"], 1, 0, "C");
            $pdf->Cell(65, 10, $row["admin_email"], 1, 1, "C");
        }
    } else {
        // No data found
        $pdf->Cell(0, 10, "No records found.", 1, 1, "C");
    }

    // Footer
    $pdf->SetFont("Arial", "I", 8);
    $pdf->SetY(-15);
    $pdf->Cell(0, 10, "Generated on " . date("d/m/Y H:i:s"), 0, 0, "C");

    // Output the PDF
    $pdf->Output("D", "{$time}_admin_list.pdf");
}

if (isset($_POST["admin_excel"])) {
    $output = "";

    // Fetch Data for Excel
    $excel = $connect->query("SELECT staff_id, admin_id, admin_name, admin_email FROM admin");

    if ($excel && $excel->num_rows > 0) {
        // Start Excel Table
        $output .= '
            <table border="1">
                <tr>
                    <th>#</th>
                    <th>Staff ID</th>
                    <th>Admin ID</th>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
        ';

        // Add Data Rows
        while ($row = $excel->fetch_assoc()) {
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

        // Set Headers for Excel Download
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename={$time}_admin_report.xls");

        // Output Excel Content
        echo $output;
    } else {
        echo "No records found :(";
    }
}
?>

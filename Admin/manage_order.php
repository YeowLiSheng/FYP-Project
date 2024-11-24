<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

// Include libraries for PDF and Excel generation
require 'fpdf/fpdf.php';
require 'PHPExcel/Classes/PHPExcel.php';

if (isset($_POST['export_pdf'])) {
    // Export to PDF
    class PDF extends FPDF {
        function Header() {
            $this->SetFont('Arial', 'B', 14);
            $this->Cell(0, 10, 'YLS Atelier - Order List', 0, 1, 'C');
            $this->Ln(10);
        }
        
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 10);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }
    }

    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);

    $query = "SELECT *, user.user_name, orders.order_date AS order_datetime FROM orders JOIN user ON orders.user_id = user.user_id;";
    $result = mysqli_query($connect, $query);

    if (mysqli_num_rows($result) > 0) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(30, 10, 'Order#', 1);
        $pdf->Cell(40, 10, 'Customer Name', 1);
        $pdf->Cell(40, 10, 'Order Time', 1);
        $pdf->Cell(50, 10, 'Shipping Address', 1);
        $pdf->Cell(30, 10, 'Total', 1);
        $pdf->Cell(30, 10, 'Status', 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 12);
        while ($row = mysqli_fetch_assoc($result)) {
            $pdf->Cell(30, 10, $row['order_id'], 1);
            $pdf->Cell(40, 10, $row['user_name'], 1);
            $pdf->Cell(40, 10, $row['order_datetime'], 1);
            $pdf->Cell(50, 10, $row['shipping_address'], 1);
            $pdf->Cell(30, 10, 'RM' . number_format($row['final_amount'], 2), 1);
            $pdf->Cell(30, 10, $row['order_status'], 1);
            $pdf->Ln();
        }
    } else {
        $pdf->Cell(0, 10, 'No orders found.', 0, 1);
    }

    $pdf->Output('D', 'Order_List.pdf');
    exit;
}

if (isset($_POST['export_excel'])) {
    // Export to Excel
    $excel = new PHPExcel();
    $excel->setActiveSheetIndex(0);
    $sheet = $excel->getActiveSheet();

    $sheet->setTitle('Order List');
    $sheet->setCellValue('A1', 'YLS Atelier - Order List');
    $sheet->mergeCells('A1:F1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

    $sheet->setCellValue('A2', 'Order#');
    $sheet->setCellValue('B2', 'Customer Name');
    $sheet->setCellValue('C2', 'Order Time');
    $sheet->setCellValue('D2', 'Shipping Address');
    $sheet->setCellValue('E2', 'Total');
    $sheet->setCellValue('F2', 'Status');

    $query = "SELECT *, user.user_name, orders.order_date AS order_datetime FROM orders JOIN user ON orders.user_id = user.user_id;";
    $result = mysqli_query($connect, $query);

    $rowNum = 3;
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $sheet->setCellValue('A' . $rowNum, $row['order_id']);
            $sheet->setCellValue('B' . $rowNum, $row['user_name']);
            $sheet->setCellValue('C' . $rowNum, $row['order_datetime']);
            $sheet->setCellValue('D' . $rowNum, $row['shipping_address']);
            $sheet->setCellValue('E' . $rowNum, 'RM' . number_format($row['final_amount'], 2));
            $sheet->setCellValue('F' . $rowNum, $row['order_status']);
            $rowNum++;
        }
    } else {
        $sheet->setCellValue('A3', 'No orders found.');
    }

    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Order_List.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(120deg, #f5f7fa, #e4e9f0);
            margin: 0;
            padding: 0;
        }

        .main {
            margin-left: 78px;
            padding: 15px;
        }

        h1 {
            color: #2c3e50;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        h1 ion-icon {
            font-size: 32px;
            color: #3498db;
        }
        .export-buttons {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }

        .export-buttons button {
            background-color: #3498db;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .export-buttons button:hover {
            background-color: #2980b9;
        }
        .search-container {
            margin-bottom: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-container input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dcdde1;
            border-radius: 5px;
            outline: none;
            font-size: 14px;
            background: white;
        }

        .search-container ion-icon {
            font-size: 20px;
            color: #7f8c8d;
        }

        .control-bar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            border-radius: 8px;
            padding: 15px 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            gap: 15px;
        }

        .control-bar .filter-group {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .control-bar select, .control-bar input {
            padding: 10px 12px;
            border: 1px solid #dcdde1;
            border-radius: 5px;
            outline: none;
            font-size: 14px;
            background: white;
            transition: all 0.3s;
        }

        .control-bar select:hover, .control-bar input:hover {
            border-color: #3498db;
        }

        .date-range {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .date-range label {
            font-size: 14px;
            color: #2c3e50;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            overflow: hidden;
            border-radius: 10px;
            margin-top: 10px;
            table-layout: fixed;
        }

        .table th, .table td {
            padding: 15px;
            text-align: center;
            border: 1px solid #dcdde1;
            word-wrap: break-word;
        }

        .table th {
            background: #3498db;
            color: white;
            font-weight: bold;
        }

        .table tr:hover {
            background: #ecf0f1;
        }

        .table th ion-icon {
            margin-right: 5px;
        }

        tr[onclick] {
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .control-bar {
                flex-direction: column;
                gap: 15px;
            }

            .search-container {
                flex-direction: column;
                gap: 10px;
            }

            .table th, .table td {
                padding: 10px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="main">
        <h1><ion-icon name="list-outline"></ion-icon> Manage Orders</h1>
        
        <div class="search-container">
            <ion-icon name="search-outline"></ion-icon>
            <input type="text" id="search-input" placeholder="Search by name" style="flex-grow: 1; max-width: 300px;">
            <form method="POST" class="export-buttons">
                <button type="submit" name="export_pdf">Export to PDF</button>
                <button type="submit" name="export_excel">Export to Excel</button>
            </form>
        </div>
        <div class="control-bar">
            <div class="filter-group">
                <label>Filter by:</label>
                <select id="filter-status">
                    <option value="" selected>- General -</option>
                    <optgroup label="Delivery Status">
                        <option value="Processing">Processing</option>
                        <option value="Shipping">Shipping</option>
                        <option value="Completed">Completed</option>
                    </optgroup>
                </select>
                <label>Sort by:</label>
                <select id="sort-order">
                    <option value="" selected>- General -</option>
                    <option value="newest">Newest</option>
                    <option value="oldest">Oldest</option>
                    <option value="highest">Highest Total</option>
                    <option value="lowest">Lowest Total</option>
                </select>
            </div>
            <div class="date-range">
                <label for="start-date">From:</label>
                <input type="text" id="start-date" placeholder="Start Date">
                <label for="end-date">To:</label>
                <input type="text" id="end-date" placeholder="End Date">
            </div>
        </div>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th><ion-icon name="cart-outline"></ion-icon> Order#</th>
                        <th><ion-icon name="person-outline"></ion-icon> Customers Name</th>
                        <th><ion-icon name="time-outline"></ion-icon> Order Time</th>
                        <th><ion-icon name="location-outline"></ion-icon> Shipped to</th>
                        <th><ion-icon name="cash-outline"></ion-icon> Total</th>
                        <th><ion-icon name="checkmark-circle-outline"></ion-icon> Order Status</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php
                    $order = "SELECT *, user.user_name, orders.order_date AS order_datetime FROM orders JOIN user ON orders.user_id = user.user_id;";
                    $result = mysqli_query($connect, $order);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr onclick="viewOrderDetails('<?php echo $row['order_id']; ?>')">
                                <td><?php echo $row["order_id"]; ?></td>
                                <td><?php echo $row["user_name"]; ?></td>
                                <td><?php echo $row["order_datetime"]; ?></td>
                                <td><?php echo $row["shipping_address"]; ?></td>
                                <td>RM<?php echo number_format($row["final_amount"], 2); ?></td>
                                <td><?php echo $row["order_status"]; ?></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="6">No orders found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        $(function () {
            $("#start-date, #end-date").datepicker({
                dateFormat: "yy-mm-dd",
                onSelect: filterByDate
            });
        });

        document.getElementById("filter-status").addEventListener("change", filterTable);
        document.getElementById("sort-order").addEventListener("change", sortTable);
        document.getElementById("search-input").addEventListener("input", searchTable);

        function filterByDate() {
            const startDate = $("#start-date").val();
            const endDate = $("#end-date").val();
            const rows = document.querySelectorAll("#table-body tr");

            rows.forEach(row => {
                const orderDateTime = row.cells[2].textContent; 
                const orderDate = orderDateTime.split(" ")[0]; // 提取日期部分

                const start = startDate || null;
                const end = endDate || null;

                if ((!start || orderDate >= start) && (!end || orderDate <= end)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        function filterTable() {
            const status = document.getElementById("filter-status").value;
            const rows = document.querySelectorAll("#table-body tr");

            rows.forEach(row => {
                const orderStatus = row.cells[5].textContent;
                row.style.display = orderStatus.includes(status) || status === "" ? "" : "none";
            });
        }

        function sortTable() {
            const rows = Array.from(document.querySelectorAll("#table-body tr"));
            const sortOrder = document.getElementById("sort-order").value;

            rows.sort((a, b) => {
                if (sortOrder === "newest" || sortOrder === "oldest") {
                    const dateA = new Date(a.cells[2].textContent);
                    const dateB = new Date(b.cells[2].textContent);
                    return sortOrder === "newest" ? dateB - dateA : dateA - dateB;
                } else if (sortOrder === "highest" || sortOrder === "lowest") {
                    const totalA = parseFloat(a.cells[4].textContent.replace("RM", ""));
                    const totalB = parseFloat(b.cells[4].textContent.replace("RM", ""));
                    return sortOrder === "highest" ? totalB - totalA : totalA - totalB;
                }
                return 0;
            });

            const tbody = document.getElementById("table-body");
            rows.forEach(row => tbody.appendChild(row));
        }

        function searchTable() {
            const query = document.getElementById("search-input").value.toLowerCase();
            const rows = document.querySelectorAll("#table-body tr");

            rows.forEach(row => {
                const name = row.cells[1].textContent.toLowerCase();
                row.style.display = name.includes(query) ? "" : "none";
            });
        }

        function viewOrderDetails(orderId) {
            // 跳转到 orderdetails.php 并传递 order_id
            window.location.href = `orderdetails.php?order_id=${orderId}`;
        }
    </script>
</body>
</html>

<?php
include 'dataconnection.php';
include 'admin_sidebar.php';
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
    <style>
        
        body 
        {
        font-family: 'Arial', sans-serif;
        background: linear-gradient(120deg, #f5f7fa, #e4e9f0);
        margin: 0;
        padding: 0;
        }

    .main 
    {
        margin-left: 78px;
        padding: 15px;
    }

    h1 
    {
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
        flex: 1;
        padding: 10px 12px;
        border: 1px solid #dcdde1;
        border-radius: 5px;
        outline: none;
        font-size: 14px;
        background: white;
    }

    .search-container button {
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        background: #3498db;
        color: white;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.3s;
    }

    .search-container button:hover {
        background: #1d6fa5;
    }

    .search-container ion-icon {
        font-size: 20px;
        color: #7f8c8d;
    }

    .btn-primary {
    background-color: #4CAF50;
    border-color: #4CAF50;
    color: white;
    padding: 10px 15px;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.2s ease-in-out;
}

.btn-primary:hover {
    background-color: #45a049;
    border-color: #45a049;
}

.btn-group {
    position: relative;
    display: inline-block;
}

.dropdown-toggle::after {
    margin-left: 0.5em;
    border-top: 0.4em solid;
    border-right: 0.4em solid transparent;
    border-left: 0.4em solid transparent;
}

.dropdown-menu {
    display: none;
    position: absolute;
    background: #fff;
    border: 1px solid #dcdde1;
    border-radius: 5px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    z-index: 10;
    margin-top: 5px;
    width: 100%;
}

.dropdown-menu.show {
    display: block;
}

.dropdown-item {
    background-color: #4CAF50;
    padding: 10px 15px;
    text-decoration: none;
    color: white;
    display: block;
    border: none;
    text-align: left;
    width: 100%;
    transition: background-color 0.2s;
}

.dropdown-item:hover {
    background-color: #45a049;
    color: white;
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

    .control-bar select,
    .control-bar input {
        padding: 10px 12px;
        border: 1px solid #dcdde1;
        border-radius: 5px;
        outline: none;
        font-size: 14px;
        background: white;
        transition: all 0.3s;
    }

    .control-bar select:hover,
    .control-bar input:hover {
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
        background-color: #4CAF50; /* Green background */
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
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 20px 0;
        gap: 5px;
    }
    .pagination .page-btn {
    margin: 0;
    padding: 10px 15px; 
    border: 1px solid #007bff; 
    background-color: #f8f9fa; 
    color: #007bff; 
    cursor: pointer;
    border-radius: 5px;
    font-size: 1em; 
    transition: background-color 0.3s, color 0.3s; 
}

.pagination .page-btn.active {
    background-color: #007bff; 
    color: white; 
    font-weight: bold; 
}

.pagination .page-btn:hover {
    background-color: #0056b3; 
    color: white; 
}

.pagination .page-btn:disabled {
    background-color: #e9ecef; 
    color: #6c757d; 
    cursor: not-allowed;
    border-color: #ced4da; 
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
        <h1> Manage Orders</h1>
        
        <div class="search-container">
        <ion-icon name="search-outline"></ion-icon>
        <input type="text" id="search-input" placeholder="Search by name" oninput="searchTable()">

        <div class="btn-group">
    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        Export:
    </button>
    <ul class="dropdown-menu">
        <li><button type="button" class="dropdown-item" onclick="exportPDF()">PDF</button></li>
        <li><button type="button" class="dropdown-item" onclick="exportExcel()">Excel</button></li>
    </ul>
</div>
    </div>

        <div class="control-bar">
            <div class="filter-group">
                <label>Filter by:</label>
                <select id="filter-status">
                    <option value="" selected>- General -</option>
                    <optgroup label="Delivery Status">
                        <option value="Processing">Processing</option>
                        <option value="Shipping">Shipping</option>
                        <option value="Complete">Complete</option>
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
                                <td>$<?php echo number_format($row["final_amount"], 2); ?></td>
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
            <div class="pagination" id="pagination"></div>

        </div>
    </div>
    <script>

document.addEventListener("DOMContentLoaded", function () {
    const tableBody = document.getElementById("table-body");
    const pagination = document.getElementById("pagination");

    const rowsPerPage = 10;
    let currentPage = 1;

    const rows = Array.from(tableBody.rows).filter(row => !row.classList.contains("no-data"));
    const totalRows = rows.length;

    if (totalRows === 0) {
        pagination.innerHTML = ""; 
        return;
    }

    function initPagination() {
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        pagination.innerHTML = "";

        const prevButton = document.createElement("button");
        prevButton.textContent = "Previous";
        prevButton.disabled = currentPage === 1;
        prevButton.classList.add("page-btn");
        prevButton.addEventListener("click", () => goToPage(currentPage - 1));
        pagination.appendChild(prevButton);

        const maxPageButtons = 5;
        const halfRange = Math.floor(maxPageButtons / 2);
        const startPage = Math.max(1, currentPage - halfRange);
        const endPage = Math.min(totalPages, currentPage + halfRange);

        for (let i = startPage; i <= endPage; i++) {
            const pageButton = document.createElement("button");
            pageButton.textContent = i;
            pageButton.classList.add("page-btn");
            if (i === currentPage) pageButton.classList.add("active");
            pageButton.addEventListener("click", () => goToPage(i));
            pagination.appendChild(pageButton);
        }

        const nextButton = document.createElement("button");
        nextButton.textContent = "Next";
        nextButton.disabled = currentPage === totalPages;
        nextButton.classList.add("page-btn");
        nextButton.addEventListener("click", () => goToPage(currentPage + 1));
        pagination.appendChild(nextButton);
    }

    function goToPage(pageNumber) {
        currentPage = Math.max(1, Math.min(pageNumber, Math.ceil(totalRows / rowsPerPage)));
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        rows.forEach((row, index) => {
            row.style.display = index >= start && index < end ? "" : "none";
        });

        initPagination();
    }

    goToPage(1); 
});


document.getElementById("filter-status").addEventListener("change", filterTable);
document.getElementById("sort-order").addEventListener("change", sortTable);

        $(function () {
            $("#start-date, #end-date").datepicker({
                dateFormat: "yy-mm-dd",
                onSelect: filterByDate
            });
        });

        document.getElementById("export-pdf").addEventListener("click", exportPDF);
        document.getElementById("export-excel").addEventListener("click", exportExcel);

        function exportPDF() {
            window.location.href = "generate_order.php";

        }

 
        function exportExcel() {
    const wb = XLSX.utils.book_new();
    wb.Props = {
        Title: "Order List",
        Author: "YLS Atelier",
    };

    // Prepare data for the table with formatted dates
    const table = document.querySelector(".table");
    const rows = Array.from(table.querySelectorAll("tbody tr")).map(row => {
        const cells = Array.from(row.querySelectorAll("td"));
        // Format the Order Time column (index 2)
        const orderTimeIndex = 2;
        if (cells[orderTimeIndex]) {
            const rawDate = new Date(cells[orderTimeIndex].textContent.trim());
            const formattedDate = rawDate.toLocaleString("en-GB", { 
                year: 'numeric', 
                month: '2-digit', 
                day: '2-digit', 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            }).replace(",", ""); // Remove comma for proper formatting
            cells[orderTimeIndex].textContent = formattedDate;
        }
        return cells.map(cell => cell.textContent);
    });

    // Add headers
    const headers = Array.from(table.querySelectorAll("thead th")).map(header => header.textContent.trim());
    rows.unshift(headers);

    // Create worksheet from updated data
    const ws = XLSX.utils.aoa_to_sheet(rows);

    // Set column widths
    ws['!cols'] = [
        { wch: 15 }, // Order# column
        { wch: 20 }, // Customer Name column
        { wch: 25 }, // Order Time column
        { wch: 50 }, // Shipped To column
        { wch: 15 }, // Total column
        { wch: 20 }, // Order Status column
    ];

    // Append the sheet to the workbook
    XLSX.utils.book_append_sheet(wb, ws, "Orders");

    // Save the workbook
    XLSX.writeFile(wb, "Order_List.xlsx");
}

        

        function filterByDate() {
            const startDate = $("#start-date").val();
            const endDate = $("#end-date").val();
            const rows = document.querySelectorAll("#table-body tr");

            rows.forEach(row => {
                const orderDateTime = row.cells[2].textContent; 
                const orderDate = orderDateTime.split(" ")[0];

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
        const orderStatus = row.cells[5].textContent.trim(); 
        row.style.display = (orderStatus.includes(status) || status === "") ? "" : "none";
    });
}

function sortTable() {
    const rows = Array.from(document.querySelectorAll("#table-body tr"));
    const sortOrder = document.getElementById("sort-order").value;

    rows.sort((a, b) => {
        if (sortOrder === "newest" || sortOrder === "oldest") {
            const dateA = new Date(a.cells[2].textContent.trim());
            const dateB = new Date(b.cells[2].textContent.trim());
            return sortOrder === "newest" ? dateB - dateA : dateA - dateB;
        } else if (sortOrder === "highest" || sortOrder === "lowest") {
            const totalA = parseFloat(a.cells[4].textContent.replace(/[^\d.-]/g, ""));
            const totalB = parseFloat(b.cells[4].textContent.replace(/[^\d.-]/g, ""));
            return sortOrder === "highest" ? totalB - totalA : totalA - totalB;
        }
        return 0;
    });

    const tbody = document.getElementById("table-body");
    rows.forEach(row => tbody.appendChild(row));
}

function searchTable() { 
    const query = document.getElementById("search-input").value.toLowerCase().trim();
    const rows = document.querySelectorAll("#table-body tr");

    rows.forEach(row => {
        const name = row.cells[1].textContent.toLowerCase().trim();
        row.style.display = name.includes(query) ? "" : "none";
    });
}

        function viewOrderDetails(orderId) {
            window.location.href = `order_details.php?order_id=${orderId}`;
        }
    </script>
</body>
</html>

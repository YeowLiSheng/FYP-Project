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
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(120deg, #f5f7fa, #e4e9f0);
            margin: 0;
            padding: 0;
        }

        .main {
            margin-left: 100px;
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

        .action-icons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .action-icons ion-icon {
            cursor: pointer;
            font-size: 18px;
            color: #3498db;
        }

        .action-icons ion-icon:hover {
            color: #2980b9;
        }

        .status-processed {
            color: #f39c12;
        }

        .status-shipped {
            color: #2980b9;
        }

        .status-completed {
            color: #27ae60;
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
            <input type="text" id="search-input" placeholder="Search by name">
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
                        <th>Order#</th>
                        <th>Created by</th>
                        <th>Created Time</th>
                        <th>Shipped to</th>
                        <th>Total</th>
                        <th>Delivery Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php
                    $order = "SELECT *, user.user_name FROM orders JOIN user ON orders.user_id = user.user_id;";
                    $result = mysqli_query($connect, $order);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td><?php echo $row["order_id"]; ?></td>
                                <td><?php echo $row["user_name"]; ?></td>
                                <td><?php echo $row["order_date"]; ?></td>
                                <td><?php echo $row["shipping_address"]; ?></td>
                                <td>RM<?php echo number_format($row["final_amount"], 2); ?></td>
                                <td class="status-<?php echo strtolower($row['order_status']); ?>">
                                    <?php echo $row["order_status"]; ?>
                                </td>
                                <td class="action-icons">
                                    <ion-icon name="eye-outline" title="View Order Details"></ion-icon>
                                    <ion-icon name="create-outline" title="Edit Order"></ion-icon>
                                    <ion-icon name="trash-outline" title="Delete Order"></ion-icon>
                                </td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="7">No orders found.</td>
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

        function filterTable() {
            const status = document.getElementById("filter-status").value;
            const rows = document.querySelectorAll("#table-body tr");
            rows.forEach(row => {
                const statusColumn = row.cells[5].textContent.trim().toLowerCase();
                if (status === "" || statusColumn === status.toLowerCase()) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        function sortTable() {
            const sortBy = document.getElementById("sort-order").value;
            const rows = Array.from(document.querySelectorAll("#table-body tr"));
            rows.sort((a, b) => {
                const totalA = parseFloat(a.cells[4].textContent.replace("RM", "").trim());
                const totalB = parseFloat(b.cells[4].textContent.replace("RM", "").trim());

                if (sortBy === "highest") {
                    return totalB - totalA;
                } else if (sortBy === "lowest") {
                    return totalA - totalB;
                } else if (sortBy === "newest") {
                    return new Date(b.cells[2].textContent) - new Date(a.cells[2].textContent);
                } else if (sortBy === "oldest") {
                    return new Date(a.cells[2].textContent) - new Date(b.cells[2].textContent);
                }
                return 0;
            });

            rows.forEach(row => document.querySelector("#table-body").appendChild(row));
        }

        function searchTable() {
            const query = document.getElementById("search-input").value.toLowerCase();
            const rows = document.querySelectorAll("#table-body tr");
            rows.forEach(row => {
                const orderId = row.cells[0].textContent.toLowerCase();
                const userName = row.cells[1].textContent.toLowerCase();
                if (orderId.includes(query) || userName.includes(query)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }
    </script>
</body>
</html>

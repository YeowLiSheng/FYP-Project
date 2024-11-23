<?php

include 'dataconnection.php';
include 'admin_sidebar.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Order</title>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
    <style>
        /* 整体布局样式 */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }

        .main {
            margin-left: 250px;
            padding: 20px;
        }

        h1 {
            color: #333;
            font-size: 24px;
            display: flex;
            align-items: center;
        }

        h1 ion-icon {
            font-size: 28px;
            margin-right: 10px;
            color: #007bff;
        }

        /* 卡片样式 */
        .card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        /* 筛选和搜索栏 */
        .top {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter, .searchbar {
            flex: 1 1 100%;
            max-width: 48%;
        }

        .filter label, .searchbar ion-icon {
            margin-right: 10px;
            color: #007bff;
        }

        .filter select, .filter input, .searchbar input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-top: 5px;
        }

        .searchbar {
            display: flex;
            align-items: center;
        }

        .searchbar ion-icon {
            font-size: 18px;
        }

        .searchbar input {
            padding-left: 30px;
        }

        /* 表格样式 */
        .table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            margin-top: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .table th, .table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background: #007bff;
            color: white;
            font-weight: bold;
        }

        .table tr:hover {
            background: #f1f1f1;
        }

        /* 响应式布局 */
        @media (max-width: 768px) {
            .top {
                flex-direction: column;
                gap: 10px;
            }

            .filter, .searchbar {
                max-width: 100%;
            }

            .table th, .table td {
                padding: 10px;
            }
        }
    </style>
    <script>
        $(function () {
            var dateFormat = "yy/mm/dd",
                from = $("#from")
                    .datepicker({
                        defaultDate: "+1w",
                        changeMonth: true,
                        numberOfMonths: 1,
                        dateFormat: 'yy/mm/dd'
                    })
                    .on("change", function () {
                        to.datepicker("option", "minDate", getDate(this));
                    }),
                to = $("#to").datepicker({
                    defaultDate: "+1w",
                    changeMonth: true,
                    numberOfMonths: 1,
                    dateFormat: 'yy/mm/dd'
                })
                    .on("change", function () {
                        from.datepicker("option", "maxDate", getDate(this));
                    });

            function getDate(element) {
                var date;
                try {
                    date = $.datepicker.parseDate(dateFormat, element.value);
                } catch (error) {
                    date = null;
                }

                return date;
            }
        });
    </script>
</head>

<body>
    <div class="main">
        <h1><ion-icon name="list-outline"></ion-icon> Manage Orders</h1>
        <div class="top">
            <form method="POST" action="" class="filter">
                <label><ion-icon name="filter-outline"></ion-icon> Filter by:</label>
                <select name="o_filt">
                    <option value="" selected>- General -</option>
                    <optgroup label="Delivery Status:">
                        <option value="Processing">Processing</option>
                        <option value="Shipping">Shipping</option>
                        <option value="Completed">Completed</option>
                    </optgroup>
                </select>
                <label><ion-icon name="calendar-outline"></ion-icon> From</label>
                <input type="text" id="from" name="from" placeholder="YYYY/MM/DD">
                <label>To</label>
                <input type="text" id="to" name="to" placeholder="YYYY/MM/DD">
            </form>
            <form method="POST" action="" class="searchbar">
                <ion-icon name="search-outline"></ion-icon>
                <input type="text" name="search" placeholder="Search by name">
            </form>
        </div>
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order#</th>
                        <th><ion-icon name="person-outline"></ion-icon> Created by</th>
                        <th><ion-icon name="time-outline"></ion-icon> Created Time</th>
                        <th><ion-icon name="location-outline"></ion-icon> Shipped to</th>
                        <th><ion-icon name="cash-outline"></ion-icon> Total</th>
                        <th><ion-icon name="checkmark-circle-outline"></ion-icon> Delivery Status</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php
                    $order = "SELECT *, user.user_name 
                              FROM orders 
                              JOIN user ON orders.user_id = user.user_id;";
                    $result = mysqli_query($connect, $order);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr onclick="window.location='order_detail.php?order_id=<?php echo $row['order_id'] ?>';">
                                <td><?php echo $row["order_id"]; ?></td>
                                <td><?php echo $row["user_name"]; ?></td>
                                <td><?php echo $row["order_date"]; ?></td>
                                <td><?php echo $row["shipping_address"]; ?></td>
                                <td>RM<?php echo number_format($row["final_amount"], 2); ?></td>
                                <td><?php echo $row["order_status"]; ?></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="6"><b>No orders found.</b></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

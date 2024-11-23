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
        /* 优化主内容区域样式 */
        .main {
            margin-left: 250px; /* 确保不影响sidebar */
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }

        /* 标题样式 */
        .head h1 {
            color: #343a40;
            font-size: 2.5rem;
            display: flex;
            align-items: center;
        }
        .head h1 i {
            margin-right: 15px;
            color: #007bff;
        }

        /* 筛选和搜索栏样式 */
        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .filter select, .filter input {
            margin-right: 10px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ced4da;
        }
        .searchbar {
            position: relative;
            display: flex;
            align-items: center;
        }
        .searchbar ion-icon {
            position: absolute;
            left: 10px;
            color: #6c757d;
        }
        .searchbar input {
            padding: 8px 10px 8px 35px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            width: 250px;
        }

        /* 表格样式 */
        .table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table thead th {
            background-color: #007bff;
            color: white;
            text-align: center;
        }
        .table tbody tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }
        .table td {
            text-align: center;
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            .top {
                flex-wrap: wrap;
                justify-content: center;
            }
            .filter, .searchbar {
                margin-bottom: 10px;
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
        <div class="head">
            <h1><ion-icon name="list-outline"></ion-icon> Manage Orders</h1>
        </div>
        <hr>
        <div class="top">
            <form method="POST" action="" class="filter">
                <label><ion-icon name="filter-outline"></ion-icon> Filter by:</label>
                <select class="form-select" id="f1" aria-label="Default select example" name="o_filt">
                    <option value="" selected>-General-</option>
                    <optgroup label="Delivery Status:">
                        <option value="Processing">Processing</option>
                        <option value="Shipping">Shipping</option>
                        <option value="Completed">Completed</option>
                    </optgroup>
                </select>
                <label><ion-icon name="sort-outline"></ion-icon> Sort by:</label>
                <select class="form-select" id="f2" aria-label="Default select example" name="o_sort">
                    <option selected>-General-</option>
                    <option value="a">Newest</option>
                    <option value="b">Oldest</option>
                    <option value="c">Highest Total</option>
                    <option value="d">Lowest Total</option>
                </select>
                <label for="from"><ion-icon name="calendar-outline"></ion-icon> From</label>
                <input type="text" id="from" class="from" name="from">
                <label for="to">to</label>
                <input type="text" id="to" class="to" name="to">
            </form>
            <form method="POST" action="" class="searchbar">
                <ion-icon class="magni" name="search-outline"></ion-icon>
                <input type="text" class="input" placeholder="Search by name" name="search" id="search">
            </form>
        </div>
        <hr>
        <div class="card">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th scope="col">Order#</th>
                        <th scope="col"><ion-icon name="person-outline"></ion-icon> Created by</th>
                        <th scope="col"><ion-icon name="time-outline"></ion-icon> Created Time</th>
                        <th scope="col"><ion-icon name="location-outline"></ion-icon> Shipped to</th>
                        <th scope="col"><ion-icon name="cash-outline"></ion-icon> Total</th>
                        <th scope="col"><ion-icon name="checkmark-circle-outline"></ion-icon> Delivery Status</th>
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
                                <th scope="row"><?php echo $row["order_id"]; ?></th>
                                <td><?php echo $row["user_name"]; ?></td>
                                <td><?php echo $row["order_date"]; ?></td>
                                <td><?php echo $row["shipping_address"]; ?></td>
                                <td>RM<?php echo number_format($row["final_amount"], 2); ?></td>
                                <td><?php echo $row["order_status"]; ?></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="6" style="text-align:center"><b>No orders found.</b></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div><!-- end of card-->
    </div>
</body>
</html>

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        .main {
            margin-left: 240px;
            padding: 20px;
        }
        .head {
            color: #333;
            font-weight: bold;
        }
        .head i {
            color: #0d6efd;
        }
        .filter, .searchbar {
            display: inline-block;
            margin-right: 15px;
            vertical-align: middle;
        }
        .filter label, .searchbar input {
            margin-right: 5px;
        }
        .searchbar {
            position: relative;
        }
        .searchbar ion-icon {
            position: absolute;
            left: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        .searchbar .input {
            padding-left: 30px;
        }
        .card {
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 15px;
        }
        .table {
            margin-top: 15px;
        }
        .table-hover tbody tr:hover {
            background-color: #f1f3f5;
            cursor: pointer;
        }
        .table th {
            background-color: #0d6efd;
            color: white;
            text-align: center;
        }
        .table td {
            text-align: center;
        }
        #from, #to {
            padding: 5px;
            border: 1px solid #ced4da;
            border-radius: 5px;
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

        $(document).ready(function () {
            $('#f1, #f2, input[name="search"], #from, #to').on('change keyup', function () {
                var f1 = $('#f1').val();
                var f2 = $('#f2').val();
                var order = $('input[name="search"]').val();
                var from = $('#from').val();
                var to = $('#to').val();

                $.ajax({
                    url: 'run_query.php',
                    method: 'POST',
                    data: { f1: f1, f2: f2, order: order, from: from, to: to },
                    success: function (response) {
                        $('#table-body').html(response);
                    }
                });
            });
        });
    </script>
</head>

<body>
    <div class="main">
        <div class="head" style="display:flex; align-items: center;">
            <i class="fas fa-box"></i>
            <h1 style="margin-left: 15px;">Manage Orders</h1>
        </div>
        <hr>
        <div class="top">
            <form method="POST" action="" class="filter">
                <label>Filter by:</label>
                <select class="form-select" id="f1" name="o_filt">
                    <option value="" selected>-General-</option>
                    <optgroup label="Delivery Status:">
                        <option value="Processing">Processing</option>
                        <option value="Shipping">Shipping</option>
                        <option value="Completed">Completed</option>
                </select>
                <label>Sort by:</label>
                <select class="form-select" id="f2" name="o_sort">
                    <option selected>-General-</option>
                    <option value="a">Newest</option>
                    <option value="b">Oldest</option>
                    <option value="c">Highest Total</option>
                    <option value="d">Lowest Total</option>
                </select>
                <label for="from">From:</label>
                <input type="text" id="from" name="from">
                <label for="to">To:</label>
                <input type="text" id="to" name="to">
            </form>
            <form method="POST" action="" class="searchbar">
                <ion-icon class="magni" name="search-outline"></ion-icon>
                <input type="text" class="input" placeholder="Search with name" name="search" id="search">
            </form>
        </div>
        <hr>
        <div class="card">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Order#</th>
                        <th>Created by:</th>
                        <th>Created Time</th>
                        <th>Shipped to</th>
                        <th>Total</th>
                        <th>Delivery Status</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php
                    $order = "SELECT *, user.user_name 
                             FROM orders 
                             JOIN user ON orders.user_id = user.user_id;";
                    $result = mysqli_query($connect, $order);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr onclick="window.location='order_detail.php?order_id=<?php echo $row['order_id']; ?>';">
                                <th scope="row"> <?php echo $row['order_id']; ?> </th>
                                <td> <?php echo $row['user_name']; ?> </td>
                                <td> <?php echo $row['order_date']; ?> </td>
                                <td> <?php echo $row['shipping_address']; ?> </td>
                                <td> RM<?php echo number_format($row['final_amount'], 2); ?> </td>
                                <td> <?php echo $row['order_status']; ?> </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="6" class="text-center">No orders found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

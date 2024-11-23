<?php

include 'dataconnection.php';
include  'admin_sidebar.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Order</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }

        .filter-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .table-container {
            margin-top: 20px;
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        table {
            border-collapse: collapse;
        }

        th {
            background-color: #007bff;
            color: white;
            text-align: center;
        }

        tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        .form-select, .input {
            width: auto;
            min-width: 150px;
        }

        .searchbar {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .searchbar input {
            flex-grow: 1;
            max-width: 300px;
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
    <div class="container">
        <h1 class="mt-4 mb-3">Manage Orders</h1>
        <div class="filter-bar">
            <form method="POST" action="" class="d-flex gap-3 align-items-center">
                <label for="f1" class="form-label">Filter by:</label>
                <select class="form-select" id="f1" name="o_filt">
                    <option value="" selected>-General-</option>
                    <optgroup label="Delivery Status:">
                        <option value="Processing">Processing</option>
                        <option value="Shipping">Shipping</option>
                        <option value="Completed">Completed</option>
                </select>

                <label for="f2" class="form-label">Sort by:</label>
                <select class="form-select" id="f2" name="o_sort">
                    <option value="" selected>-General-</option>
                    <option value="a">Newest</option>
                    <option value="b">Oldest</option>
                    <option value="c">Highest Total</option>
                    <option value="d">Lowest Total</option>
                </select>

                <label for="from" class="form-label">From</label>
                <input type="text" id="from" class="form-control" name="from">

                <label for="to" class="form-label">to</label>
                <input type="text" id="to" class="form-control" name="to">
            </form>

            <form method="POST" action="" class="searchbar">
                <ion-icon class="magni" name="search-outline"></ion-icon>
                <input type="text" class="form-control" placeholder="Search with name" name="search" id="search">
            </form>
        </div>

        <div class="table-container">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th scope="col" style="width:1px;">Order#</th>
                        <th scope="col">Created by:</th>
                        <th scope="col">Created Time</th>
                        <th scope="col">Shipped to</th>
                        <th scope="col" style="width:1px;">Total</th>
                        <th scope="col">Delivery Status</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php
                    $order = "SELECT *,user.user_name 
                    FROM orders 
                    JOIN user ON orders.user_id = user.user_id;";

                    $result = mysqli_query($connect, $order);
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr onclick="window.location='order_detail.php?order_id=<?php echo $row['order_id'] ?>';">
                                <th scope="row">
                                    <?php echo $row["order_id"] ?>
                                </th>
                                <td>
                                    <?php echo $row["user_name"] ?>
                                </td>
                                <td>
                                    <?php echo $row["order_date"] ?>
                                </td>
                                <td>
                                    <?php echo $row["shipping_address"] ?>
                                </td>
                                <td>
                                    RM<?php echo number_format($row["final_amount"], 2); ?>
                                </td>
                                <td>
                                    <?php echo $row["order_status"] ?>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="6" style="text-align:center">No orders found</td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>

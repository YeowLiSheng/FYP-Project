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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery and jQuery UI -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .main {
            margin: 20px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .head h1 {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }
        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .filter, .searchbar {
            display: flex;
            align-items: center;
        }
        .filter label, .searchbar ion-icon {
            margin-right: 10px;
            font-size: 1rem;
            color: #555;
        }
        .filter select, .filter input, .searchbar input {
            margin-right: 15px;
            padding: 5px 10px;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .searchbar input {
            flex: 1;
            max-width: 300px;
        }
        .table {
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .table th {
            background-color: #f1f1f1;
        }
        .table tbody tr:hover {
            background-color: #f9f9f9;
            cursor: pointer;
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
        <div class="head d-flex align-items-center">
            <i class="lni lni-list" style="font-size:50px; color:#555;"></i>
            <h1 class="ms-3">Manage Orders</h1>
        </div>
        <hr>
        <div class="top">
            <!-- Filter Form -->
            <form method="POST" class="filter">
                <label>Filter by:</label>
                <select class="form-select" id="f1" name="o_filt">
                    <option value="" selected>-General-</option>
                    <optgroup label="Delivery Status:">
                        <option value="Processing">Processing</option>
                        <option value="Shipping">Shipping</option>
                        <option value="Completed">Completed</option>
                    </optgroup>
                </select>
                <label>Sort by:</label>
                <select class="form-select" id="f2" name="o_sort">
                    <option value="" selected>-General-</option>
                    <option value="a">Newest</option>
                    <option value="b">Oldest</option>
                    <option value="c">Highest Total</option>
                    <option value="d">Lowest Total</option>
                </select>
                <label for="from">From</label>
                <input type="text" id="from" class="from" name="from">
                <label for="to">to</label>
                <input type="text" id="to" class="to" name="to">
            </form>
            <!-- Search Form -->
            <form method="POST" class="searchbar">
                <ion-icon name="search-outline"></ion-icon>
                <input type="text" class="form-control" placeholder="Search with name" name="search" id="search">
            </form>
        </div>
        <hr>
        <div class="card">
            <table class="table table-bordered">
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
                    $order = "SELECT *, user.user_name 
                              FROM orders 
                              JOIN user ON orders.user_id = user.user_id;";
                    $result = mysqli_query($connect, $order);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr onclick=\"window.location='order_detail.php?order_id={$row['order_id']}';\">
                                <th scope='row'>{$row['order_id']}</th>
                                <td>{$row['user_name']}</td>
                                <td>{$row['order_date']}</td>
                                <td>{$row['shipping_address']}</td>
                                <td>RM" . number_format($row["final_amount"], 2) . "</td>
                                <td>{$row['order_status']}</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>No orders found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

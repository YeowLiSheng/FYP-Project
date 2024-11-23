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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .main {
            margin-left: 250px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .head h1 {
            font-size: 28px;
            color: #333;
        }

        .filter, .searchbar {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter label, .searchbar ion-icon {
            font-size: 18px;
            color: #555;
        }

        .form-select, .from, .to, .input {
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            font-size: 14px;
        }

        .form-select:focus, .from:focus, .to:focus, .input:focus {
            border-color: #007bff;
        }

        .card {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background-color: #007bff;
            color: white;
            text-align: left;
            padding: 10px;
        }

        tbody td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        tbody tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        .searchbar ion-icon {
            margin-right: -25px;
        }
    </style>
</head>

<body>
    <div class="main">
        <div class="head d-flex align-items-center">
            <i class="fas fa-list" style="font-size: 40px; margin-right: 15px;"></i>
            <h1>Manage Orders</h1>
        </div>
        <hr>
        <div class="top d-flex justify-content-between align-items-center">
            <form method="POST" action="" class="filter d-flex">
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
                    <option selected>-General-</option>
                    <option value="a">Newest</option>
                    <option value="b">Oldest</option>
                    <option value="c">Highest Total</option>
                    <option value="d">Lowest Total</option>
                </select>
                <label for="from">From:</label>
                <input type="text" id="from" class="from" name="from">
                <label for="to">to:</label>
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
                            ?>
                            <tr onclick="window.location='order_detail.php?order_id=<?php echo $row['order_id'] ?>';">
                                <th scope="row"> <?php echo $row["order_id"] ?> </th>
                                <td> <?php echo $row["user_name"]; ?> </td>
                                <td> <?php echo $row["order_date"] ?> </td>
                                <td> <?php echo $row["shipping_address"]; ?> </td>
                                <td> RM<?php echo number_format($row["final_amount"], 2); ?> </td>
                                <td> <?php echo $row["order_status"] ?> </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <td colspan="6" class="text-center"><b>No Orders Found</b></td>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

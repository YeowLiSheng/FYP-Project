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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom right, #007BFF, #6C757D);
            color: #fff;
            margin: 0;
            padding: 0;
        }
        .main {
            margin-left: 260px;
            padding: 20px;
        }
        .head {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #fff;
        }
        .head i {
            color: #FFD700;
            margin-right: 10px;
        }
        .filter, .searchbar {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .filter select, .searchbar input {
            margin-right: 10px;
            border-radius: 10px;
            padding: 8px;
        }
        .card {
            background: #ffffff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }
        .table {
            margin-top: 15px;
            background: #ffffff;
            border-radius: 10px;
        }
        .table thead {
            background: #007BFF;
            color: #fff;
        }
        .table-hover tbody tr:hover {
            background: rgba(0, 123, 255, 0.1);
            cursor: pointer;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        #from, #to {
            padding: 10px;
            border-radius: 10px;
        }
        .btn {
            border-radius: 30px;
            padding: 8px 20px;
            background: #007BFF;
            color: #fff;
        }
        .btn:hover {
            background: #0056b3;
            color: #fff;
        }
        .icon {
            font-size: 18px;
            color: #007BFF;
            margin-right: 5px;
        }
    </style>
    <script>
        $(function () {
            $("#from, #to").datepicker({
                dateFormat: "yy/mm/dd",
                changeMonth: true,
                changeYear: true
            });

            $('#f1, #f2, input[name="search"], #from, #to').on('change keyup', function () {
                const f1 = $('#f1').val();
                const f2 = $('#f2').val();
                const order = $('input[name="search"]').val();
                const from = $('#from').val();
                const to = $('#to').val();

                $.ajax({
                    url: 'run_query.php',
                    method: 'POST',
                    data: { f1, f2, order, from, to },
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
        <div class="head">
            <i class="fas fa-box"></i> Manage Orders
        </div>
        <div class="filter">
            <select class="form-select" id="f1" name="o_filt">
                <option value="" selected>- General -</option>
                <optgroup label="Delivery Status:">
                    <option value="Processing">Processing</option>
                    <option value="Shipping">Shipping</option>
                    <option value="Completed">Completed</option>
                </optgroup>
            </select>
            <select class="form-select" id="f2" name="o_sort">
                <option selected>- General -</option>
                <option value="a">Newest</option>
                <option value="b">Oldest</option>
                <option value="c">Highest Total</option>
                <option value="d">Lowest Total</option>
            </select>
            <label>From:</label>
            <input type="text" id="from" name="from" placeholder="Start Date">
            <label>To:</label>
            <input type="text" id="to" name="to" placeholder="End Date">
        </div>
        <div class="searchbar">
            <i class="icon fas fa-search"></i>
            <input type="text" class="form-control" id="search" name="search" placeholder="Search by name">
        </div>
        <div class="card">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order#</th>
                        <th>Created By</th>
                        <th>Created Time</th>
                        <th>Shipped To</th>
                        <th>Total</th>
                        <th>Delivery Status</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php
                    $order = "SELECT *, user.user_name FROM orders JOIN user ON orders.user_id = user.user_id;";
                    $result = mysqli_query($connect, $order);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr onclick="window.location='order_detail.php?order_id=<?php echo $row['order_id']; ?>';">
                                <td> <?php echo $row['order_id']; ?> </td>
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

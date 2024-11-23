<style?php

include 'dataconnection.php';
include  'admin_sidebar.php';

?>

<!DOCTYPE html>
< lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Order</title>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
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
<style>
body, h1, h2, h3, p, ul, li {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body and Container */
body {
    font-family: 'Roboto', sans-serif;
    background: linear-gradient(to bottom, #f5f7fa, #c3cfe2);
    color: #333;
    padding: 20px;
}

.main-container {
    display: grid;
    grid-template-columns: 250px auto;
    gap: 20px;
    max-width: 1200px;
    margin: auto;
}

/* Sidebar */
.sidebar {
    background: #ffffff;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.sidebar h2 {
    font-size: 20px;
    margin-bottom: 10px;
    color: #6a11cb;
}

.sidebar ul {
    list-style: none;
}

.sidebar li {
    margin: 10px 0;
    font-size: 16px;
    display: flex;
    align-items: center;
    color: #333;
    cursor: pointer;
    transition: color 0.3s ease;
}

.sidebar li i {
    margin-right: 10px;
    color: #6a11cb;
}

.sidebar li:hover {
    color: #6a11cb;
}

/* Content */
.content {
    background: #ffffff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.order-summary h2 {
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

.order-summary h2 i {
    color: #6a11cb;
    margin-right: 10px;
}

.order-item {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.product-image {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    margin-right: 15px;
}

.order-item h3 {
    margin: 0;
    font-size: 18px;
    color: #333;
}

.order-total p {
    font-size: 16px;
    margin: 5px 0;
    font-weight: 500;
}

/* Buttons */
.btn {
    background: linear-gradient(45deg, #6a11cb, #2575fc);
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 25px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: background 0.3s ease, transform 0.3s ease;
}

.btn:hover {
    background: linear-gradient(45deg, #2575fc, #6a11cb);
    transform: scale(1.05);
}
</style>
<body>
    <div class="main p-3">
        <div class="head" style="display:flex;">
            <i class="lni lni-list" style="font-size:50px;"></i>
            <h1 style="margin: 12px 0 0 30px;">Order</h1>
            <hr>
        </div>
        <hr>
        <div class="top">
            <form method="POST" action="" class="filter">
                <label>Filter by:</label>
                <select class="form-select" id="f1" aria-label="Default select example" name="o_filt">
                    <option value="" selected>-General-</option>
                    <optgroup label="Delivery Status:">
                        <option value="Processing">Processing</option>
                        <option value="Shipping">Shipping</option>
                        <option value="Completed">Completed</option>
                </select>
                <label>Sort by:</label>
                <select class="form-select" id="f2" aria-label="Default select example" name="o_sort">
                    <option selected>-General-</option>
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
                    JOIN user ON orders.user_id = user.user_id;                                
                    ";
                    $result = mysqli_query($connect, $order);
                    $count = mysqli_num_rows($result);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $user_id = $row["user_id"];
                            $user = "SELECT * FROM user WHERE user_id = '$user_id'";
                            $user_run = mysqli_query($connect, $user);
                            $row_user = mysqli_fetch_assoc($user_run);

                            ?>
                            <tr onclick="window.location='order_detail.php?order_id=<?php echo $row['order_id'] ?>';">
                                <th scope="row">
                                    <?php echo $row["order_id"] ?>
                                </th>
                                <td>
                                    <?php echo $row["user_name"]; ?><br>
                                    <!-- <div style="font-size:11px;"><i>from </i>
                                        < ?php echo $row["country"] ?> -->
                                    <!-- </div> -->
                                </td>
                                <td>
                                    <?php echo $row["order_date"] ?>
                                </td>
                                <td>
                                    <?php echo
                                     $row["shipping_address"]; ?>
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
                        <td colspan="5" style="text-align:center"><b></b></td>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            <script>
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
        </div><!-- end of card-->
    </div>
</body>

</html>
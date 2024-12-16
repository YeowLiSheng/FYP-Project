<?php include 'admin_sidebar.php'; 
include 'dataconnection.php'; 
?>

<style>
    /* General Reset and Body Styling */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f7fc;
        color: #333;
    }

    .main {
        width: 90%;
        max-width: 1200px;
        margin: 20px auto;
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }

    h1 {
        font-size: 28px;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
    }

    .card {
        border-radius: 8px;
        background-color: #fff;
        border: 1px solid #ddd;
        box-shadow: 0px 6px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        padding: 15px;
    }

    .card-header {
        background-color: #4CAF50; /* Green background */
        color: #fff;
        font-size: 22px;
        font-weight: 600;
        padding: 12px;
        border-radius: 8px 8px 0 0;
    }

    .list-group-item {
        font-size: 16px;
        padding: 12px;
        margin-bottom: 8px;
        background-color: #fafafa;
        border: 1px solid #ddd;
        border-radius: 8px;
        display: flex;
        justify-content: space-between;
    }

    .list-group-item b {
        color: black;
    }

 
    .btn-warning:hover {
        background-color: #218838;
        cursor: pointer;
    }

    /* Address Dropdown */
    select.form-select {
        width: 300%;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #ddd;
        background-color: #fff;
        font-size: 14px;
    }

    /* Table Styling */
    table {
        width: 100%;
        margin-top: 25px;
        border-collapse: collapse;
        font-size: 14px;
    }

    table th, table td {
        padding: 12px;
        text-align: left;
        border: 1px solid #ddd;
    }

    table th {
        background-color: #4CAF50; /* Green background */
        color: #fff;
        font-weight: 600;
    }

    table tr:nth-child(even) {
        background-color: #f8f9fc;
    }

    table tr:hover {
        background-color: #e9ecef;
    }

    /* Responsive Layout */
    @media (max-width: 768px) {
        .main {
            width: 95%;
            padding: 15px;
        }

        h1 {
            font-size: 24px;
        }

        .card-header {
            font-size: 20px;
        }

        .list-group-item {
            font-size: 13px;
        }

        table th, table td {
            padding: 10px;
        }
    }

    /* Flexbox Helper Classes */
    .d-flex {
        display: flex;
    }

    .justify-content-between {
        justify-content: space-between;
    }

    .align-items-center {
        align-items: center;
    }

    .close-btn {
            display: inline-block;
            text-decoration: none;
            background-color: #ff4d4d; /* Red background */
            color: #fff; /* White text */
            font-size: 20px; /* Visible font size */
            font-weight: bold;
            border: none;
            border-radius: 5px; /* Slightly rounded edges for modern look */
            width: 40px;
            height: 40px;
            text-align: center;
            line-height: 40px; /* Center align the text */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Subtle shadow for depth */
            transition: transform 0.2s, box-shadow 0.2s; /* Smooth hover effects */
            cursor: pointer;
            position: absolute; /* Allows precise positioning */
            margin-top: -85px; /* Adjust top distance */
            right: 40px; /* Align to the right */
        }

        /* Hover effect */
        .close-btn:hover {
            background-color: #ff1a1a; /* Darker red on hover */
            transform: scale(1.1); /* Slight zoom on hover */
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.3); /* Enhanced shadow on hover */
        }

        /* Focus outline for accessibility */
        .close-btn:focus {
            outline: 2px solid #fff; /* White outline for focus */
            outline-offset: 2px;
        }

</style>

<body>
    <div class="main p-3">
        <h1>User Details</h1>
        <?php
        if ($_GET["ID"]) {
            $id = $_GET["ID"];
            $query = mysqli_query($connect, "SELECT * FROM user WHERE user_id='$id'");
            $row = mysqli_fetch_assoc($query);
        }
        ?>

        <div class="d-flex justify-content-between align-items-center">
            <span style="font-size: 20px; font-weight: bold;"><?php echo "User:  " . $id ; ?>     <?php echo $row["user_name"];?></span>
            <a href="view_customer.php" class="close-btn" aria-label="Close">&times;</a>
        </div>

        <div class="card">
            <div class="card-header">
                PROFILE
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><b>Email:</b> <?php echo $row["user_email"]; ?></li>
                    <li class="list-group-item"><b>Contact Number:</b> <?php echo $row["user_contact_number"]; ?></li>
                    <li class="list-group-item"><b>Gender:</b> <?php echo $row["user_gender"]; ?></li>
                    <li class="list-group-item"><b>Date Of Birth:</b> <?php echo $row["user_date_of_birth"]; ?></li>
                    <li class="list-group-item"><b>Joined at:</b> <?php echo $row["user_join_time"]; ?></li>
                    <li class="list-group-item">
                        <div>
                            <b>Address(es):</b>
                            <select class="form-select">
                                <?php 
                                $addresses = "SELECT * FROM user_address WHERE user_id = '$id'";
                                $query_add = mysqli_query($connect, $addresses);
                                while($row_add = mysqli_fetch_assoc($query_add)) {
                                ?>
                                    <option><?php echo $row_add["address"] . ", " . $row_add["postcode"] . " " . $row_add["city"] . ", " . $row_add["state"]; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <h1>Purchase History</h1>
        <table>
            <thead>
                <tr>
                    <th scope="col">Order#</th>
                    <th scope="col">Created Time</th>
                    <th scope="col">Total (RM)</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $order = mysqli_query($connect, "SELECT * FROM orders WHERE user_id = '$id'"); 
            while($row_order = mysqli_fetch_assoc($order)) { 
            ?>
                <tr>
                    <th scope="row"><?php echo $row_order["order_id"];?></th>
                    <td><?php echo $row_order["order_date"];?></td>
                    <td><?php echo number_format($row_order["final_amount"], 2);?></td>
                    <td><?php echo $row_order["order_status"];?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div><!--end of main-->
</body>

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
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #f3f4f6, #e8ebf0);
            margin: 0;
        }

        .main {
            margin-left: 240px;
            padding: 20px;
        }

        .head {
            display: flex;
            align-items: center;
            color: #343a40;
            font-weight: 700;
            font-size: 24px;
        }

        .head i {
            color: #0d6efd;
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
            gap: 10px;
        }

        .filter label {
            font-weight: 600;
            color: #495057;
        }

        .filter select, .filter input {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            outline: none;
            transition: all 0.3s;
        }

        .filter select:hover, .filter input:hover {
            border-color: #0d6efd;
        }

        .searchbar .input {
            padding: 8px 12px;
            padding-left: 35px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            outline: none;
            transition: all 0.3s;
            background-color: #fff;
        }

        .searchbar .input:hover {
            border-color: #0d6efd;
        }

        .searchbar ion-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
        }

        .table {
            margin-top: 15px;
            font-size: 14px;
            color: #495057;
        }

        .table th {
            background: linear-gradient(135deg, #0d6efd, #0069d9);
            color: #fff;
            border: none;
            text-align: center;
        }

        .table td {
            text-align: center;
            border-color: #e9ecef;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.02);
            transition: all 0.3s;
        }

        #from, #to {
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 8px;
        }

        #from:hover, #to:hover {
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="main">
        <div class="head">
            <i class="fas fa-box"></i>
            <h1 style="margin-left: 15px;">Manage Orders</h1>
        </div>
        <hr>
        <div class="top">
            <form method="POST" class="filter">
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
                <label>From:</label>
                <input type="text" id="from" name="from">
                <label>To:</label>
                <input type="text" id="to" name="to">
            </form>
            <form method="POST" class="searchbar">
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
                    <!-- Dynamic Content -->
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

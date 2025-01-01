<?php 
include 'admin_sidebar.php';
include 'dataconnection.php';
?>
<head>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery -->
</head>

<style>
    .card {
        padding: 20px;
        max-width: 1200px;
        margin: 20px auto;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .input {
        width: 100%;
        max-width: 600px;
        height: 40px;
        border-radius: 8px;
        border: 1px solid #dcdde1;
        padding-left: 40px;
        font-size: 14px;
        background-color: #fff;
    }

    .top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 15px;
    }

    .searchbar {
        position: relative;
        flex: 1;
    }

    .searchbar .magni {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        left: 10px;
        font-size: 18px;
        color: #7f8c8d;
    }

    tbody tr {
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    tbody tr:hover {
        background-color: #f9f9f9;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        margin-top: 15px;
    }

    .table thead th {
        background-color: #3498db;
        color: #fff;
        padding: 15px;
        font-size: 14px;
    }

    .table th, .table td {
        padding: 12px;
        text-align: center;
        border: 1px solid #dcdde1;
    }

    .btn-group {
        display: flex;
        gap: 10px;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #fff;
        min-width: 160px;
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
        z-index: 1;
        border-radius: 5px;
    }

    .dropdown-content a {
        color: #2c3e50;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        transition: background-color 0.3s ease;
    }

    .dropdown-content a:hover {
        background-color: #ecf0f1;
    }

    .show-dropdown .dropdown-content {
        display: block;
    }
</style>


<body>
    <div class="main p-3">
        <div class="head" style="display:flex;">
            <i class="lni lni-users" style="font-size:50px;"></i>
            <h1 style="margin: 12px 0 0 30px;">User</h1>
            <hr>
        </div>
        <hr>
        <div class="top">
            <form method="POST" action="" class="searchbar">
                <ion-icon class="magni" name="search-outline"></ion-icon>
                <input type="text" class="input" placeholder="Search with name" name="search" id="search">
            </form>

            <!-- Export Button and Dropdown -->
            <form method="POST" action="generate_user.php">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                Export:
                            </button>
                            <ul class="dropdown-menu">
                                <li><button type="submit" class="dropdown-item" name="cust_pdf">PDF</button></li>
                                <li><button type="submit" class="dropdown-item" name="cust_excel">CSV</button></li>
                            </ul>
                        </div>
                    </form>
        </div>

        <hr>
        <div class="card">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Contact</th>
                        <th scope="col">Joined Time</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php
                    // Initial fetch to display all users
                    $result = mysqli_query($connect, "SELECT * FROM user");
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr onclick="window.location='customer_detail.php?ID=<?php echo $row['user_id'] ?>';">
                                <th scope="row"><?php echo $row["user_id"] ?></th>
                                <td><?php echo $row["user_name"]; ?></td>
                                <td style="vertical-align: middle;">
                                    Telephone.No: <?php echo $row["user_contact_number"] ?><br>
                                    Email: <?php echo $row["user_email"] ?>
                                </td>
                                <td><?php echo $row["user_join_time"] ?></td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <td colspan="5" style="text-align:center"><b>No users found.</b></td>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div><!-- end of card-->
    </div><!-- end of main-->

    <script>
        // Use AJAX to fetch the filtered results when user types
        $("#search").keyup(function() {
            var searchQuery = $(this).val();

            $.ajax({
                url: "search_customer.php",
                type: "POST",
                data: { search: searchQuery },
                success: function(data) {
                    // Replace the table body with the new filtered data
                    $("#table-body").html(data);
                }
            });
        });

        // Clear the search bar when a customer is clicked
        $("tbody").on("click", "tr", function() {
            // Clear the search bar
            $("#search").val('');
        });

        // Toggle the visibility of the export dropdown menu
        function toggleDropdown() {
            document.getElementById("exportDropdown").classList.toggle("show-dropdown");
        }
    </script>
</body>

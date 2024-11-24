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
<style>
    .card {
        padding: 30px;
        max-width: 1425px; /* Set max-width to keep the table within this width */
        margin: 10 auto; /* Center the card */
    }

    .input {
        width: 1000px; /* Make the search bar smaller */
        height: 50px;
        border-radius: 10px;
    }

    input[type=text] {
        background-color: white;
        background-position: 10px 10px;
        background-repeat: no-repeat;
        padding-left: 40px;
    }

    tbody tr {
        cursor: pointer;
    }

    .top {
        display: flex;
        align-items: center;
    }

    .top .btn-group {
        margin-left: 30px;
        align-items: center;
        margin-top: 5px;
    }

    .searchbar {
        position: relative;
    }

    .magni {
        position: absolute;
        top: 17%;
        font-size: 30px;
        left: 4.7px;
    }

    .table {
        width: 100%; /* Allow the table to take up full width of the container */
        font-size: 1em; /* Adjust font size as needed */
    }

    .table th, .table td {
        padding: 10px; /* Set padding for cells */
        vertical-align: middle;
    }

    /* Style for Export button and dropdown */
    .btn-group {
        position: relative;
    }

    .export-btn {
        margin-left: 10px;
        padding: 10px 20px;
        font-size: 16px;
        border: none;
        background-color: #4CAF50;
        color: white;
        border-radius: 5px;
        cursor: pointer;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f9f9f9;
        min-width: 160px;
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
        z-index: 1;
        border-radius: 5px;
    }

    .dropdown-content a {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }

    .dropdown-content a:hover {
        background-color: #f1f1f1;
    }

    /* Show dropdown on button click */
    .show-dropdown .dropdown-content {
        display: block;
    }
</style>

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
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        Export:
                    </button>
                    <ul class="dropdown-menu">
                        <li><button type="submit" class="dropdown-item" href="#" name="cust_pdf">PDF</a></li>
                        <li><button type="submit" class="dropdown-item" href="#" name="cust_excel">CSV</a></li>
                    </ul>
                </div>
            </form>
        </div> <!-- End of top div -->
        <hr>
        
        <!-- Moved the table below the search bar -->
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
                            <tr onclick="window.location='cust_detail.php?ID=<?php echo $row['user_id'] ?>';">
                                <th scope="row"><?php echo $row["user_id"] ?></th>
                                <td><?php echo $row["user_name"]; ?><br></td>
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
                url: "search_users.php",
                type: "POST",
                data: { search: searchQuery },
                success: function(data) {
                    // Replace the table body with the new filtered data
                    $("#table-body").html(data);
                }
            });
        });


    </script>
</body>

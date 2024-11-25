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
        padding: 30px;
        max-width: 1425px;
        margin: 20px auto; /* Increased margin for better spacing */
        background-color: #f9f9f9; /* Light background for card */
        border-radius: 8px; /* Rounded corners */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    }

    .input {
        width: 800px; /* Keep the search bar smaller */
        height: 50px;
        border-radius: 10px;
        border: 1px solid #ccc; /* Border color for input */
    }

    input[type=text] {
        background-color: white;
        padding-left: 40px;
        font-size: 14px; /* Slightly smaller font */
    }

    tbody tr {
        cursor: pointer;
        transition: background-color 0.3s; /* Smooth transition for hover */
    }

    tbody tr:hover {
        background-color: #f1f1f1; /* Highlight row on hover */
    }

    .top {
        display: flex;
        align-items: center;
        gap: 20px; /* Space between search bar and export button */
    }

    .searchbar {
        position: relative;
    }

    .magni {
        position: absolute;
        top: 17%;
        font-size: 20px;
        left: 10px; /* Adjusted the position of the icon */
        color: #333; /* Slightly darker color */
    }

    .table {
        width: 100%; /* Ensure table takes full width */
        font-size: 14px; /* Adjusted font size for readability */
        margin-top: 20px; /* Space between card and table */
    }

    .table thead th {
        background-color: #4CAF50; /* Green background for table headers */
        color: white;
        padding: 12px 15px; /* Added more padding for headers */
        font-size: 16px; /* Larger font for headers */
    }

    .table th, .table td {
        padding: 12px 15px; /* Added padding for cells */
        vertical-align: middle;
    }

    .btn-group {
        position: relative;
    }

    .export-btn {
        padding: 12px 20px;
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
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown"
                        aria-expanded="false">
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
                            <tr onclick="window.location='cust_detail.php?ID=<?php echo $row['user_id'] ?>';">
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
                url: "search_users.php",
                type: "POST",
                data: { search: searchQuery },
                success: function(data) {
                    // Replace the table body with the new filtered data
                    $("#table-body").html(data);
                }
            });
        });

        // Toggle the visibility of the export dropdown menu
        function toggleDropdown() {
            document.getElementById("exportDropdown").classList.toggle("show-dropdown");
        }
    </script>
</body>

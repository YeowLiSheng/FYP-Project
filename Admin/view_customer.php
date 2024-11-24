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

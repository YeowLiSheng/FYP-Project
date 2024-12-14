<?php
// Database connection
include("dataconnection.php");
include 'admin_sidebar.php';

$admin_id = $_SESSION['admin_id']; // Get the admin ID from the session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Management</title>
    <style>
        /* Reset and Layout Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }
        main {
            padding: 100px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .admin-content {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        .view-admin, .recent-activity {
            background-color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }
        .view-admin:hover, .recent-activity:hover {
            transform: translateY(-5px);
        }
        .view-admin h2, .recent-activity h2 {
            font-size: 1.8em;
            margin-bottom: 15px;
        }
        .view-admin table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .view-admin th, .view-admin td {
            padding: 12px 18px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 1.1em;
        }
        .view-admin th {
            background-color: #4CAF50; /* Green background */
            color: white; /* White text */
            text-align: left;
            font-size: 1.1em;
        }
        .view-admin tr:nth-child(even) {
            background-color: #fafafa;
        }
        .view-admin button {
            padding: 8px 14px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            margin-right: 8px;
            transition: background-color 0.3s;
        }
        .view-admin button:hover {
            background-color: #45a049;
        }
        .add-blog-btn {
            padding: 10px 16px;
            margin-top: 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s;
        }
        .add-blog-btn:hover {
            background-color: #0056b3;
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-content {
                gap: 20px;
            }
            .top {
                flex-direction: column;
                gap: 10px;
            }
            .btn-group {
                width: 100%;
            }
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .searchbar {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 8px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 50%;
        }
        .searchbar input {
            border: none;
            outline: none;
            font-size: 1em;
            padding: 8px;
            width: 100%;
            border-radius: 8px;
        }
        .btn-group {
            display: flex;
            gap: 10px;
        }
        .btn-success {
            background-color: #28a745;
            color: white;
            border-radius: 8px;
            padding: 8px 16px;
            border: none;
        }
        .btn-success:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<main>
    <section class="admin-content">
        <?php
        if (isset($_GET['status']) && $_GET['status'] == 'deleted') {
            echo "<div style='background-color: #28a745; color: white; padding: 10px; border-radius: 5px; margin-bottom: 20px;'>Blog deleted successfully!</div>";
        }
        ?>
        <!-- View Blog Section -->
        <div class="view-admin">
            <h2>Blog Management</h2>
            <div class="top">
                <div class="searchbar">
                    <ion-icon class="magni" name="search-outline"></ion-icon>
                    <input type="text" class="input" placeholder="Search with title" name="search" id="search">
                </div>
            </div>
            <hr>
            <button class="add-blog-btn" onclick="location.href='add_blog.php'">Add Blog</button>
            <hr>
            <table>
                <thead>
                    <tr>
                        <th>Blog ID</th>
                        <th>Title</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php
                    // Query the database for blog details
                    $query = "SELECT blog_id, title FROM blog";
                    $result = mysqli_query($connect, $query);

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['blog_id'] . "</td>";
                            echo "<td><a href='edit_blog.php?blog_id=" . $row['blog_id'] . "'>" . $row['title'] . "</a></td>";
                            echo "<td>";
                            echo "<button onclick=\"location.href='view_comment.php?blog_id=" . $row['blog_id'] . "'\">View Comments</button>";
                            echo "<button onclick=\"if(confirm('Are you sure you want to delete this blog?')) location.href='delete_blog.php?blog_id=" . $row['blog_id'] . "'\">Delete</button>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>No blogs available</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>
</main>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // AJAX to fetch filtered results when user types
        $("#search").keyup(function() {
            var searchQuery = $(this).val();

            $.ajax({
                url: "search_blog.php",
                type: "POST",
                data: { search: searchQuery },
                success: function(data) {
                    // Replace the table body with the new filtered data
                    $("#table-body").html(data);
                }
            });
        });
    </script>

<script>
    // Ensure the search bar is cleared when the page loads
    $(document).ready(function() {
        // Clear the search input field
        $("#search").val('');
    });
</script>


</body>
</html>

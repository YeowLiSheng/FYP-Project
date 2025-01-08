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
    font-family: 'Arial', sans-serif;
    background: linear-gradient(120deg, #f5f7fa, #e4e9f0);
    color: #333;
    margin: 0;
    padding: 0;
}
main {
    margin-left: 78px;
    padding: 15px;
}
h1 {
    color: #2c3e50;
    font-size: 28px;
    display: flex;
    align-items: center;
    gap: 10px;
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
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-in-out;
}
.view-admin:hover, .recent-activity:hover {
    transform: translateY(-5px);
}
.view-admin h2, .recent-activity h2 {
    font-size: 1.8em;
    margin-bottom: 15px;
}
.view-admin table, .table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    overflow: hidden;
    border-radius: 10px;
    margin-top: 10px;
    table-layout: fixed;
}
.view-admin th, .view-admin td, .table th, .table td {
    padding: 12px 18px;
    border: 1px solid #ddd;
    text-align: left;
    font-size: 1.1em;
}
.view-admin th, .table th {
    background-color: #4CAF50;
    color: white;
    font-weight: bold;
}
.view-admin tr:nth-child(even), .table tr:nth-child(even) {
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
.add-blog-btn, .search-container button {
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
.add-blog-btn:hover, .search-container button:hover {
    background-color: #0056b3;
}
.searchbar, .search-container {
    display: flex;
    align-items: center;
    background-color: #fff;
    padding: 8px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    width: 50%;
}
.searchbar input, .search-container input {
    flex: 1;
    border: 1px solid #dcdde1;
    outline: none;
    font-size: 1em;
    padding: 8px;
    border-radius: 8px;
    background: white;
}
.btn-group {
    display: inline-block;
    position: relative;
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
.dropdown-menu {
    display: none;
    position: absolute;
    background: #fff;
    border: 1px solid #dcdde1;
    border-radius: 5px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    z-index: 10;
    margin-top: 5px;
}
.dropdown-item {
    padding: 10px 15px;
    text-decoration: none;
    color: #2c3e50;
    cursor: pointer;
    display: block;
    transition: background-color 0.2s;
}
.dropdown-item:hover {
    background-color: #ecf0f1;
}
.control-bar {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    border-radius: 8px;
    padding: 15px 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    gap: 15px;
}
.control-bar .filter-group {
    display: flex;
    align-items: center;
    gap: 20px;
}
.control-bar select, .control-bar input {
    padding: 10px 12px;
    border: 1px solid #dcdde1;
    border-radius: 5px;
    outline: none;
    font-size: 14px;
    background: white;
    transition: all 0.3s;
}
.control-bar select:hover, .control-bar input:hover {
    border-color: #3498db;
}
.date-range {
    display: flex;
    align-items: center;
    gap: 10px;
}
.date-range label {
    font-size: 14px;
    color: #2c3e50;
}
.card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    margin-top: 10px;
}
.table th ion-icon {
    margin-right: 5px;
}
tr[onclick] {
    cursor: pointer;
}
@media (max-width: 768px) {
    .control-bar {
        flex-direction: column;
        gap: 15px;
    }
    .search-container {
        flex-direction: column;
        gap: 10px;
    }
    .table th, .table td {
        padding: 10px;
        font-size: 12px;
    }
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
                            echo "<button style=\"background-color: #ff4d4d;\" onclick=\"Swal.fire({
                                icon: 'warning',
                                title: 'Are you sure?',
                                text: 'This blog will be deleted.',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, delete it!',
                                cancelButtonText: 'Cancel'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.href='delete_blog.php?blog_id=" . $row['blog_id'] . "';
                                }
                            })\">Delete</button>";
                            
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

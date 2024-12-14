<?php
// Database connection
include("dataconnection.php");

// Get the search query from the POST request
$searchQuery = $_POST['search'];

// Prevent SQL injection by escaping the input
$searchQuery = mysqli_real_escape_string($connect, $searchQuery);

// Query to search for blogs based on the title
$query = "SELECT blog_id, title FROM blog WHERE title LIKE '%$searchQuery%'";
$result = mysqli_query($connect, $query);

// Check if there are any results
if ($result && mysqli_num_rows($result) > 0) {
    // Loop through and display the results in a table format
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
    // If no results are found, display a message
    echo "<tr><td colspan='3'>No blogs found</td></tr>";
}
?>

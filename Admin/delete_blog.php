<?php
// Include database connection
include("dataconnection.php");

// Check if blog_id is set in the URL
if (isset($_GET['blog_id'])) {
    // Get the blog_id from the URL
    $blog_id = $_GET['blog_id'];

    // SQL query to delete the blog by blog_id
    $query = "DELETE FROM blog WHERE blog_id = $blog_id";

    // Execute the query
    if (mysqli_query($connect, $query)) {
        // Redirect to the blog management page after successful deletion
       
        echo "<script>alert('Blog deleted successfully.');window.location.href='view_blog.php';</script>";
        exit;
    } else {
        // If there is an error in deletion, display an error message
        echo "Error deleting blog: " . mysqli_error($connect);
    }
} else {
    // If blog_id is not set, show an error
    echo "Blog ID not specified!";
}
?>

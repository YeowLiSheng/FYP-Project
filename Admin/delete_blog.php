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
        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Blog deleted successfully.',
                    text: 'The blog has been deleted.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'view_blog.php';
                });
            </script>
        </body>
        </html>";
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

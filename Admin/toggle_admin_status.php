<?php
// Include database connection
include 'dataconnection.php';

if (isset($_POST['toggle_status'])) {
    $staff_id = $_POST['staff_id'];

    // Fetch the current status and admin_id of the admin
    $query = "SELECT admin_status, admin_id FROM admin WHERE staff_id = '$staff_id'";
    $result = mysqli_query($connect, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $current_status = $row['admin_status'];
        $admin_id = $row['admin_id'];

        // Check if the admin is superadmin
        if ($admin_id == 'superadmin') {
            
             // Error uploading file, show SweetAlert
            echo "<!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error uploading',
                        text: 'The superadmin cannot be deactivated.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'view_admin.php';
                    });
                </script>
            </body>
            </html>";
            exit;
            
        }

        // Toggle the admin status
        $new_status = ($current_status == 1) ? 0 : 1;

        // Update the admin status in the database
        $update_query = "UPDATE admin SET admin_status = '$new_status' WHERE staff_id = '$staff_id'";
        if (mysqli_query($connect, $update_query)) {
            header("Location: view_admin.php"); 
            exit();
        } else {
            echo "Error updating admin status: " . mysqli_error($connect);
        }
    } else {
        echo "Admin not found.";
    }
} else {
    echo "Invalid request.";
}
?>

<?php
include 'dataconnection.php';
session_start(); // Ensure session is started to access session variables

$admin_id = $_SESSION['admin_id']; // Get the logged-in admin ID

if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($connect, $_POST['search']);
    
    // Check if search is empty, and if so, retrieve all records
    if ($search == "") {
        $query = "SELECT staff_id, admin_id, admin_name, admin_email FROM admin";
    } else {
        // Otherwise, filter results by search term
        $query = "SELECT staff_id, admin_id, admin_name, admin_email FROM admin WHERE admin_name LIKE '%$search%'";
    }

    $result = mysqli_query($connect, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>".$row['staff_id']."</td>";
            echo "<td>".$row['admin_id']."</td>";
            echo "<td>".$row['admin_name']."</td>";
            echo "<td>".$row['admin_email']."</td>";
            echo "<td>";
            echo "<button onclick=\"location.href='admin_detail.php?staff_id=" . $row['staff_id'] . "'\">View Details</button>";

            // Show the Delete button only if the current admin is superadmin and not deleting their own account
            if ($admin_id === 'superadmin' && $row['staff_id'] !== $admin_id) {
                echo "<button onclick=\"if(confirm('Are you sure you want to delete this staff?')) location.href='deleted_staff.php?staff_id=" . $row['staff_id'] . "'\">Delete</button>";
            } else {
                echo "<button onclick=\"noPermission()\">Delete</button>";
            }
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5' style='text-align:center'><b>No users found.</b></td></tr>";
    }
}
?>

<?php
session_start();  // Start the session to access session variables

include 'dataconnection.php';  // Include the database connection

if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];  // Get the logged-in admin ID
} else {
    // Redirect to login if no session found
    header("Location: login.php");
    exit();
}

if (isset($_POST['search'])) {
    $searchQuery = mysqli_real_escape_string($connect, $_POST['search']);

    // Query to search for admins based on the search query
    $query = "SELECT staff_id, admin_id, admin_name, admin_email, admin_status FROM admin WHERE admin_name LIKE '%$searchQuery%' OR admin_email LIKE '%$searchQuery%'";
    $result = mysqli_query($connect, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        // Output the table rows for each matching result
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['staff_id'] . "</td>";
            echo "<td>" . $row['admin_id'] . "</td>";
            echo "<td>" . $row['admin_name'] . "</td>";
            echo "<td>" . $row['admin_email'] . "</td>";

            // Actions column
            echo "<td>";
            echo "<button onclick=\"location.href='admin_detail.php?staff_id=" . $row['staff_id'] . "'\" style='background-color: #4CAF50; color: white; border: none; padding: 5px 10px;'>View Details</button>";
            echo "</td>";

            // Status column
            echo "<td>";
            if ($admin_id === 'superadmin') {
                // For superadmin, show the form and allow toggling the admin status
                echo "<form method='POST' action='toggle_admin_status.php' style='display:inline-block; margin: 0;'>";
                echo "<input type='hidden' name='staff_id' value='" . $row['staff_id'] . "'>";
                echo "<button type='submit' name='toggle_status' style='background-color: " . ($row['admin_status'] == 1 ? '#4CAF50' : '#ff4d4d') . "; color: white; border: none; padding: 5px 10px;'>";
                echo $row['admin_status'] == 1 ? 'Active' : 'Deactivate';
                echo "</button>";
                echo "</form>";
            } else {
                // For non-superadmin, show the same button but prevent form submission and show "No Permission"
                echo "<form method='POST' action='#' style='display:inline-block; margin: 0;'>";
                echo "<input type='hidden' name='staff_id' value='" . $row['staff_id'] . "'>";
                echo "<button type='button' onclick='noPermission()' style='background-color: " . ($row['admin_status'] == 1 ? '#4CAF50' : '#ff4d4d') . "; color: white; border: none; padding: 5px 10px;'>";
                echo $row['admin_status'] == 1 ? 'Active' : 'Deactivate';
                echo "</button>";
                echo "</form>";
            }
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No admin data available</td></tr>";
    }
}
?>

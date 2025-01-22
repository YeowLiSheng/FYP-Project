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
    <title>Admin Dashboard</title>
    <style>
        /* Reset and Layout Styles */
        * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background: linear-gradient(to bottom, #f5f7fa, #e4e9f0);
        color: #333;
        padding: 20px;
    }

    main {
        padding: 20px;
        max-width: 1200px;
        margin: auto;
    }

    .admin-content {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    /* Card Styling */
    .view-admin, .recent-activity {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .view-admin:hover, .recent-activity:hover {
        transform: translateY(-5px);
    }

    .view-admin h2 {
        font-size: 24px;
        margin-bottom: 20px;
    }

    /* Search Bar Styling */
    .searchbar {
        display: flex;
        align-items: center;
        background-color: #fff;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
    }

    .searchbar input {
        flex: 1;
        border: none;
        outline: none;
        padding: 10px;
        font-size: 16px;
    }

    .searchbar ion-icon {
        font-size: 20px;
        color: #666;
        margin-right: 10px;
    }

    /* Button Group Styling */
    .btn-group {
        display: flex;
        gap: 10px;
    }

    .btn-success {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 16px;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .btn-success:hover {
        background-color: #45a049;
    }

    /* Dropdown Menu */
    .dropdown-menu li {
        padding: 10px 20px;
    }

    .dropdown-menu li button {
        border: none;
        background: none;
        padding: 0;
        cursor: pointer;
        font-size: 16px;
        color: #333;
    }

    .dropdown-menu li button:hover {
        color: #4CAF50;
    }

    /* Add Staff Button */
    .add-staff-btn {
        background-color: #007BFF;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .add-staff-btn:hover {
        background-color: #0056b3;
    }

    /* Table Styling */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th, td {
        padding: 12px 18px;
        text-align: left;
        font-size: 14px;
        border: 1px solid #ddd;
    }

    th {
        background-color: #4CAF50;
        color: white;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    button {
        padding: 8px 12px;
        font-size: 14px;
        border: none;
        border-radius: 6px;
        color: white;
        cursor: pointer;
        background-color: #4CAF50;
        transition: background-color 0.3s;
    }

    button:hover {
        background-color: #45a049;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .admin-content {
            gap: 20px;
        }

        .searchbar {
            width: 100%;
        }

        table {
            font-size: 12px;
        }

        th, td {
            padding: 8px 12px;
        }
    }
    </style>
</head>
<body>
    <main>
        <section class="admin-content">
            <!-- View Admin Section -->
            <div class="view-admin">
                <h2>Admin Management</h2>
                <div class="top">
                    <div class="searchbar">
                        <ion-icon class="magni" name="search-outline"></ion-icon>
                        <input type="text" class="input" placeholder="Search with name" name="search" id="search">
                    </div>
                    <form method="POST" action="generate_admin.php">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                Export:
                            </button>
                            <ul class="dropdown-menu">
                                <li><button type="submit" class="dropdown-item" name="admin_pdf">PDF</button></li>
                                <li><button type="submit" class="dropdown-item" name="admin_excel">CSV</button></li>
                            </ul>
                        </div>
                    </form>
                </div>

                <hr>

                <?php if($admin_id === 'superadmin'): ?>
                    <button class="add-staff-btn" onclick="location.href='add_staff.php'">Add Staff</button>
                <?php else: ?>
                    <button class="add-staff-btn" onclick="noPermission()">Add Staff</button>
                <?php endif; ?>

                <hr>

                <table>
    <thead>
        <tr>
            <th>Staff ID</th>
            <th>Admin ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Actions</th>
            <th>Status</th>
            
        </tr>
    </thead>
    <tbody id="table-body">
    <?php
    // Query the database for admin details
    $query = "SELECT staff_id, admin_id, admin_name, admin_email, admin_status FROM admin";
    $result = mysqli_query($connect, $query);

    if ($result && mysqli_num_rows($result) > 0) {
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
    ?>
</tbody>

</table>

            </div>
        </section>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function noPermission() {
        Swal.fire({
            icon: 'error',
            title: 'Permission Denied',
            text: 'You do not have permission to perform this action.',
            confirmButtonText: 'OK'
        });
    }

    // AJAX to fetch filtered results when user types
    $("#search").keyup(function() {
        var searchQuery = $(this).val();

        $.ajax({
            url: "search_admin.php",
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
</html>

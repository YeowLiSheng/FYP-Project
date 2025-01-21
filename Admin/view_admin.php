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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }
        main {
            padding: 50px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .admin-content {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        .section {
            background-color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }
        .section:hover {
            transform: translateY(-5px);
        }
        .section h2 {
            font-size: 1.8em;
            margin-bottom: 15px;
        }
        .section table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .section th, .section td {
            padding: 12px 18px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 1.1em;
        }
        .section th {
            background-color: #4CAF50; /* Green background */
            color: white;
            font-size: 1.1em;
        }

        .section tr:nth-child(even) {
            background-color: #fafafa;
        }
        .section button {
            padding: 8px 14px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s;
        }
        .section button:hover {
            background-color: #45a049;
        }
        .add-staff-btn {
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
        .add-staff-btn:hover {
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
            color: white;
            border-radius: 8px;
            padding: 8px 16px;
            border: none;
        }
        .btn-success:hover {
            background-color: #4CAF50;
        }
        .dropdown-menu li {
            padding: 8px 10px;
        }
    </style>
</head>
<body>
    <main>
        <section class="admin-content">
            <!-- View Admin Section -->
            <div class="section">
                <h2>Admin Management</h2>
                <div class="top">
                    <div class="searchbar">
                        <input type="text" placeholder="Search with name" name="search" id="search">
                    </div>
                    <form method="POST" action="generate_admin.php">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
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
                            echo "<button onclick=\"location.href='admin_detail.php?staff_id=" . $row['staff_id'] . "'\">View Details</button>";
                            echo "</td>";

                            // Status column
                            echo "<td>";
                            if ($admin_id === 'superadmin') {
                                echo "<form method='POST' action='toggle_admin_status.php'>";
                                echo "<input type='hidden' name='staff_id' value='" . $row['staff_id'] . "'>";
                                echo "<button type='submit' name='toggle_status'>";
                                echo $row['admin_status'] == 1 ? 'Active' : 'Deactivate';
                                echo "</button>";
                                echo "</form>";
                            } else {
                                echo "<button onclick='noPermission()'>";
                                echo $row['admin_status'] == 1 ? 'Active' : 'Deactivate';
                                echo "</button>";
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

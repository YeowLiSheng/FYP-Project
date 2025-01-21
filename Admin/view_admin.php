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
        body 
        {
        font-family: 'Arial', sans-serif;
        background: linear-gradient(120deg, #f5f7fa, #e4e9f0);
        margin: 0;
        padding: 0;
        }

    .main 
    {
        margin-left: 78px;
        padding: 15px;
    }

    h1 
    {
        color: #2c3e50;
        font-size: 28px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    h1 ion-icon {
        font-size: 32px;
        color: #3498db;
    }

    .search-container {
        margin-bottom: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        padding: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .search-container input {
        flex: 1;
        padding: 10px 12px;
        border: 1px solid #dcdde1;
        border-radius: 5px;
        outline: none;
        font-size: 14px;
        background: white;
    }

    .search-container button {
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        background: #3498db;
        color: white;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.3s;
    }

    .search-container button:hover {
        background: #1d6fa5;
    }

    .search-container ion-icon {
        font-size: 20px;
        color: #7f8c8d;
    }

    .btn-primary {
        background-color: #4CAF50 !important;
        border-color: #4CAF50 !important;
    }

    .btn-primary:hover {
        background-color: #45a049 !important;
        border-color: #45a049 !important;
    }

    .btn-group {
        background-color: #4CAF50;
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
        background-color: #4CAF50;
        padding: 10px 15px;
        text-decoration: none;
        color: #fff;
        cursor: pointer;
        display: block;
        transition: background-color 0.2s;
    }

    .dropdown-item:hover {
        background-color: #45a049;
        color: white;
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

    .control-bar select,
    .control-bar input {
        padding: 10px 12px;
        border: 1px solid #dcdde1;
        border-radius: 5px;
        outline: none;
        font-size: 14px;
        background: white;
        transition: all 0.3s;
    }

    .control-bar select:hover,
    .control-bar input:hover {
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

    .table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        overflow: hidden;
        border-radius: 10px;
        margin-top: 10px;
        table-layout: fixed;
    }

    .table th, .table td {
        padding: 15px;
        text-align: center;
        border: 1px solid #dcdde1;
        word-wrap: break-word;
    }

    .table th {
        background-color: #4CAF50;
        color: white;
        font-weight: bold;
    }

    .table tr:hover {
        background: #ecf0f1;
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
            <div class="view-admin">
                <h2>Admin Management</h2>
                <div class="search-container">
                    <ion-icon class="magni" name="search-outline"></ion-icon>
                    <input type="text" class="input" placeholder="Search with name" name="search" id="search">
                </div>
                <form method="POST" action="generate_admin.php">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Export:
                        </button>
                        <ul class="dropdown-menu">
                            <li><button type="submit" class="dropdown-item" name="admin_pdf">PDF</button></li>
                            <li><button type="submit" class="dropdown-item" name="admin_excel">CSV</button></li>
                        </ul>
                    </div>
                </form>

                <hr>

                <?php if($admin_id === 'superadmin'): ?>
                    <button class="add-staff-btn" onclick="location.href='add_staff.php'">Add Staff</button>
                <?php else: ?>
                    <button class="add-staff-btn" onclick="noPermission()">Add Staff</button>
                <?php endif; ?>

                <hr>

                <table class="table">
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
                        $query = "SELECT staff_id, admin_id, admin_name, admin_email, admin_status FROM admin";
                        $result = mysqli_query($connect, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . $row['staff_id'] . "</td>";
                                echo "<td>" . $row['admin_id'] . "</td>";
                                echo "<td>" . $row['admin_name'] . "</td>";
                                echo "<td>" . $row['admin_email'] . "</td>";

                                echo "<td><button onclick=\"location.href='admin_detail.php?staff_id=" . $row['staff_id'] . "'\" style='background-color: #4CAF50; color: white; border: none; padding: 5px 10px;'>View Details</button></td>";

                                echo "<td>";
                                if ($admin_id === 'superadmin') {
                                    echo "<form method='POST' action='toggle_admin_status.php' style='display:inline-block;'>";
                                    echo "<input type='hidden' name='staff_id' value='" . $row['staff_id'] . "'>";
                                    echo "<button type='submit' name='toggle_status' style='background-color: " . ($row['admin_status'] == 1 ? '#4CAF50' : '#ff4d4d') . "; color: white;'>";
                                    echo $row['admin_status'] == 1 ? 'Active' : 'Deactivate';
                                    echo "</button>";
                                    echo "</form>";
                                } else {
                                    echo "<button onclick='noPermission()' style='background-color: " . ($row['admin_status'] == 1 ? '#4CAF50' : '#ff4d4d') . "; color: white;'>";
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

        $("#search").keyup(function() {
            var searchQuery = $(this).val();

            $.ajax({
                url: "search_admin.php",
                type: "POST",
                data: { search: searchQuery },
                success: function(data) {
                    $("#table-body").html(data);
                }
            });
        });
    </script>
</body>
</html>

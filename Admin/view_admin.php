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
            padding: 60px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .admin-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .card {
            background-color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card h2 {
            font-size: 1.8em;
            margin-bottom: 15px;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px 18px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 1em;
        }
        th {
            background-color: #4CAF50; /* Green background */
            color: white; /* White text */
            font-size: 1.1em;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        .button {
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
        .button:hover {
            background-color: #45a049;
        }
        .add-button {
            background-color: #007BFF;
        }
        .add-button:hover {
            background-color: #0056b3;
        }
        .search-container {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 8px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .search-container input {
            border: none;
            outline: none;
            font-size: 1em;
            padding: 8px;
            width: 100%;
            border-radius: 8px;
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-content {
                gap: 15px;
            }
            .search-container {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main>
        <section class="admin-content">
            <div class="card">
                <h2>Admin Management</h2>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="search-container">
                        <input type="text" id="search" placeholder="Search with name">
                    </div>
                    <button class="button add-button" onclick="location.href='add_staff.php'">Add Staff</button>
                </div>

                <div class="table-container">
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
                            $query = "SELECT staff_id, admin_id, admin_name, admin_email, admin_status FROM admin";
                            $result = mysqli_query($connect, $query);

                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>{$row['staff_id']}</td>";
                                    echo "<td>{$row['admin_id']}</td>";
                                    echo "<td>{$row['admin_name']}</td>";
                                    echo "<td>{$row['admin_email']}</td>";
                                    echo "<td><button class='button' onclick=\"location.href='admin_detail.php?staff_id=" . $row['staff_id'] . "'\">View</button></td>";

                
                                    echo "<td><button class='button' style='background-color: " . ($row['admin_status'] ? '#4CAF50' : '#ff4d4d') . ";'>" . ($row['admin_status'] ? 'Active' : 'Deactivate') . "</button></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>No data available</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
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

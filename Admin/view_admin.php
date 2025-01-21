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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(120deg, #4CAF50, #8BC34A);
            color: #333;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        main {
            width: 90%;
            max-width: 1200px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            padding: 40px;
        }
        h2 {
            font-size: 2em;
            margin-bottom: 20px;
            color: #4CAF50;
        }
        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .searchbar {
            display: flex;
            align-items: center;
            background-color: #f9f9f9;
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
        .btn {
            padding: 10px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
            font-size: 1.1em;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .add-staff-btn {
            padding: 12px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            margin-bottom: 20px;
            transition: background-color 0.3s;
        }
        .add-staff-btn:hover {
            background-color: #0056b3;
        }
        @media (max-width: 768px) {
            .top {
                flex-direction: column;
                gap: 15px;
            }
            .searchbar {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main>
        <h2>Admin Management</h2>
        <div class="top">
            <div class="searchbar">
                <input type="text" class="input" placeholder="Search by name" name="search" id="search">
            </div>
            <form method="POST" action="generate_admin.php">
                <div class="btn-group">
                    <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown">
                        Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><button type="submit" class="dropdown-item" name="admin_pdf">PDF</button></li>
                        <li><button type="submit" class="dropdown-item" name="admin_excel">CSV</button></li>
                    </ul>
                </div>
            </form>
        </div>
        <button class="add-staff-btn" onclick="location.href='add_staff.php'">Add Staff</button>
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
            <tbody>
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
                        echo "<td><button class='btn' onclick=\"location.href='admin_detail.php?staff_id=" . $row['staff_id'] . "'\">View Details</button></td>";
                        echo "<td>" . ($row['admin_status'] == 1 ? 'Active' : 'Deactivated') . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No admin data available</td></tr>";
                }
                ?>
            </tbody>
        </table>
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

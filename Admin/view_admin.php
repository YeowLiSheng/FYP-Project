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

        /* 全局样式 */
html, body {
    height: 100%;
    margin: 0;
    font-family: 'Arial', sans-serif;
    background: linear-gradient(120deg, #f5f7fa, #e4e9f0);
}

/* 布局容器 */
.container {
    display: flex;
    height: 100%;
}

/* Sidebar 样式 */
.sidebar {
    width: 250px; /* Sidebar 固定宽度 */
    background-color: #2c3e50;
    color: white;
    padding: 20px;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}
.sidebar ul {
    list-style: none;
    padding: 0;
}
.sidebar li {
    margin: 15px 0;
}
.sidebar a {
    text-decoration: none;
    color: white;
    font-size: 16px;
    display: block;
    transition: background-color 0.3s;
}
.sidebar a:hover {
    background-color: #34495e;
    padding: 10px;
    border-radius: 5px;
}

/* 主内容区 */
.main-content {
    flex-grow: 1; /* 填充剩余空间 */
    padding: 20px;
    overflow: auto; /* 内容溢出时可滚动 */
}

/* 示例内容 */
h1 {
    font-size: 28px;
    color: #2c3e50;
}
.content {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}


* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    font-family: 'Arial', sans-serif;
    background: linear-gradient(120deg, #f5f7fa, #e4e9f0);
    color: #333;
    margin: 0;
    padding: 0;
}
main {
    padding: 15px;
    margin-left: 78px;
}

/* Admin Content */
.admin-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Card Styles */
.view-admin, .recent-activity, .card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    margin-top: 10px;
    transition: transform 0.3s ease-in-out;
}
.view-admin:hover, .recent-activity:hover {
    transform: translateY(-5px);
}

/* Table Styles */
.view-admin table, .table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    overflow: hidden;
    border-radius: 10px;
    margin-top: 10px;
    table-layout: fixed;
}
.view-admin th, .table th, .view-admin td, .table td {
    padding: 15px;
    text-align: center;
    border: 1px solid #dcdde1;
    word-wrap: break-word;
}
.view-admin th, .table th {
    background-color: #4CAF50;
    color: white;
    font-weight: bold;
}
.view-admin tr:nth-child(even), .table tr:nth-child(even) {
    background-color: #fafafa;
}
.view-admin tr:hover, .table tr:hover {
    background: #ecf0f1;
}

/* Search Container */
.search-container {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
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

/* Control Bar */
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
.control-bar select, .control-bar input {
    padding: 10px 12px;
    border: 1px solid #dcdde1;
    border-radius: 5px;
    outline: none;
    font-size: 14px;
    background: white;
    transition: all 0.3s;
}
.control-bar select:hover, .control-bar input:hover {
    border-color: #3498db;
}

/* Button Group and Dropdown */
.btn-group {
    display: inline-block;
    position: relative;
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
    padding: 10px 15px;
    text-decoration: none;
    color: #2c3e50;
    cursor: pointer;
    display: block;
    transition: background-color 0.2s;
}
.dropdown-item:hover {
    background-color: #ecf0f1;
}

/* Responsive Design */
@media (max-width: 768px) {
    .admin-content {
        gap: 15px;
    }
    .control-bar {
        flex-direction: column;
        gap: 15px;
    }
    .search-container {
        flex-direction: column;
        gap: 10px;
    }
    .view-admin th, .view-admin td, .table th, .table td {
        padding: 10px;
        font-size: 12px;
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
            <th>Status</th>
            <th>Actions</th>
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

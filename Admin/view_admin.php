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
            margin: 0;
    height: 100vh; 
    display: flex;
    flex-direction: column;
    
        }
        main {
            margin-top: 50px;
            margin-left: 78px;
    padding: 15px;
    width: calc(100% - 78px); 
    min-height: 100vh; 
    background: white;

        }
        .admin-content {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        .view-admin, .recent-activity {
            background-color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }
        .view-admin:hover, .recent-activity:hover {
            transform: translateY(-5px);
        }
        .view-admin h2, .recent-activity h2 {
            font-size: 1.8em;
            margin-bottom: 15px;
        }
        .view-admin table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .view-admin th, .view-admin td {
            padding: 12px 18px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 1.1em;
        }
        .view-admin th {
            background-color: #4CAF50; /* Green background */
            color: white; /* White text */
            text-align: left;
            font-size: 1.1em;
}

        .view-admin tr:nth-child(even) {
            background-color: #fafafa;
        }
        .view-admin button {
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
        .view-admin button:hover {
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

        .pagination-controls {
    display: flex;
    justify-content: flex-end;
    margin-top: 10px;
}

.pagination-button {
    margin: 0 5px;
    padding: 5px 10px;
    border: 1px solid #ccc;
    background-color: #f9f9f9;
    cursor: pointer;
    border-radius: 4px;
}

.pagination-button.active {
    background-color: #4CAF50;
    color: white;
}

.pagination-button:disabled {
    background-color: #ddd;
    cursor: not-allowed;
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
<div id="pagination-controls" class="pagination-controls"></div>

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

    document.addEventListener('DOMContentLoaded', () => {
    const itemsPerPage = 1; // 每页显示的行数
    const rows = document.querySelectorAll('.table-row');
    const totalPages = Math.ceil(rows.length / itemsPerPage);

    let currentPage = 1;

    const renderPage = (page) => {
        rows.forEach((row, index) => {
            if (index >= (page - 1) * itemsPerPage && index < page * itemsPerPage) {
                row.style.display = ''; // 显示当前页的行
            } else {
                row.style.display = 'none'; // 隐藏其他行
            }
        });
        updatePaginationControls(page);
    };

    const createPaginationControls = () => {
        const paginationContainer = document.getElementById('pagination-controls');

        // 上一页按钮
        const prevButton = document.createElement('button');
        prevButton.textContent = 'Previous';
        prevButton.classList.add('pagination-button');
        prevButton.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderPage(currentPage);
            }
        });
        paginationContainer.appendChild(prevButton);

        // 页码按钮
        for (let i = 1; i <= totalPages; i++) {
            const pageButton = document.createElement('button');
            pageButton.textContent = i;
            pageButton.classList.add('pagination-button');
            pageButton.addEventListener('click', () => {
                currentPage = i;
                renderPage(currentPage);
            });
            paginationContainer.appendChild(pageButton);
        }

        // 下一页按钮
        const nextButton = document.createElement('button');
        nextButton.textContent = 'Next';
        nextButton.classList.add('pagination-button');
        nextButton.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderPage(currentPage);
            }
        });
        paginationContainer.appendChild(nextButton);
    };

    const updatePaginationControls = (page) => {
        const buttons = document.querySelectorAll('.pagination-button');
        buttons.forEach((button, index) => {
            if (index === 0) {
                button.disabled = page === 1; // 上一页按钮
            } else if (index === buttons.length - 1) {
                button.disabled = page === totalPages; // 下一页按钮
            } else {
                button.classList.toggle('active', index === page);
            }
        });
    };

    if (rows.length > itemsPerPage) {
        createPaginationControls();
        renderPage(currentPage);
    } else {
        rows.forEach(row => (row.style.display = '')); // 如果数据少于每页数，全部显示
    }
});

</script>

</body>
</html>

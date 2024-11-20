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
            padding: 100px;
            max-width: 1200px;
            margin: 0 auto;
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
            background-color: #f7f7f7;
            color: #555;
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
        }
    </style>
    <script>
        function noPermission() {
            alert("You do not have permission to perform this action.");
        }
    </script>
</head>
<body>
    <main>
        <section class="admin-content">
            <!-- View Admin Section -->
            <div class="view-admin">
                <h2>Admin Management</h2>

                <!-- Conditionally display the Add Staff button based on superadmin status -->
                <?php if($admin_id === 'superadmin'): ?>
                    <button class="add-staff-btn" onclick="location.href='add_staff.php'">Add Staff</button>
                <?php else: ?>
                    <button class="add-staff-btn" onclick="noPermission()">Add Staff</button>
                <?php endif; ?>

                <table>
                    <thead>
                        <tr>
                            <th>Staff ID</th>
                            <th>Admin ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Query the database for admin details
                        $query = "SELECT staff_id, admin_id, admin_name, admin_email FROM admin";
                        $result = mysqli_query($connect, $query);

                        // Check if there are results
                        if ($result && mysqli_num_rows($result) > 0) {
                            // Loop through each row in the result
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . $row['staff_id'] . "</td>";
                                echo "<td>" . $row['admin_id'] . "</td>";
                                echo "<td>" . $row['admin_name'] . "</td>";
                                echo "<td>" . $row['admin_email'] . "</td>";



                                // Add Delete button with confirmation for superadmins
                                echo "<td>";
                                echo "<button>View Details</button>";
                                
                                if ($admin_id === 'superadmin') {
                                    // Check if the staff_id is not the same as the logged-in admin's ID
                                    if ($row['staff_id'] !== $admin_id) {
                                        echo "<button onclick=\"if(confirm('Are you sure you want to delete this staff?')) location.href='deleted_staff.php?staff_id=" . $row['staff_id'] . "'\">Delete</button>";
                                    } else {
                                        // Disable the delete button if the superadmin tries to delete themselves
                                        echo "<button disabled>Delete</button>";
                                    }
                                } else {
                                    echo "<button onclick=\"noPermission()\">Delete</button>";
                                }
                                echo "</td>";
                                


                                
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No admin data available</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>



        </section>
    </main>
</body>
</html>


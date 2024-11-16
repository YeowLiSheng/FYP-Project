<?php

// Database connection
include("dataconnection.php"); 
include 'admin_sidebar.php';




$admin_id = $_SESSION['admin_id']; // Get the admin ID from the session
?>

<!-- Main Dashboard Page (dashboard.php) -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <style>
        /* Reset some default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body and layout */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            line-height: 1.6;
            color: #333;
        }

        /* Main content */
        main {
            padding: 100px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Admin content layout */
        .admin-content {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        /* View Admin Section */
        .view-admin {
            background-color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }

        .view-admin:hover {
            transform: translateY(-5px);
        }

        .view-admin h2 {
            font-size: 1.8em;
            margin-bottom: 15px;
            color: #333;
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

        .view-admin button:active {
            transform: scale(0.98);
        }

        /* Add Staff Button Style */
        .add-staff-btn {
            display: inline-block;
            padding: 10px 16px;
            margin-top: 20px;
            background-color: #007BFF;
            color: white;
            text-align: center;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s;
        }

        .add-staff-btn:hover {
            background-color: #0056b3;
        }

        /* Recent Activity Section */
        .recent-activity {
            background-color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            transition: transform 0.3s ease-in-out;
        }

        .recent-activity:hover {
            transform: translateY(-5px);
        }

        .recent-activity h2 {
            font-size: 1.8em;
            margin-bottom: 15px;
            color: #333;
        }

        .recent-activity ul {
            list-style-type: none;
            padding-left: 20px;
        }

        .recent-activity li {
            font-size: 1.1em;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        .recent-activity li:last-child {
            border-bottom: none;
        }

        .recent-activity li:nth-child(odd) {
            background-color: #f9f9f9;
        }

        .recent-activity li:nth-child(even) {
            background-color: #f1f1f1;
        }

        /* Mobile responsive design */
        @media (max-width: 768px) {
            .admin-content {
                flex-direction: column;
                gap: 20px;
            }

            .view-admin, .recent-activity {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'dataconnection.php'; ?>

    <main>
        <section class="admin-content">
            <!-- View Admin Section -->
            <div class="view-admin">
                <h2>Admin Management</h2>
                



<!-- Check if the admin is superadmin -->
<?php if($admin_id === 'superadmin'): ?>
    <!-- Display the button for superadmin -->
    <button class="add-staff-btn" onclick="location.href='add_staff.php'">Add Staff</button>
<?php else: ?>
    <!-- Display the message if not superadmin -->
    <p>You do not have permission to add staff.</p>
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
                                echo "<td><button>View Details</button> <button>Delete</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No admin data available</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Activity Section -->
            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <ul>
                    <li>User John Doe logged in at 10:30 AM</li>
                    <li>User Jane Smith updated their profile at 11:15 AM</li>
                    <li>New user registered at 12:00 PM</li>
                </ul>
            </div>
        </section>
    </main>
</body>
</html>

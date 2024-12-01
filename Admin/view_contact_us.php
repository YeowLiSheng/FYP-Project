<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

// Check the connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Query to fetch email, message, and status
$sql = "SELECT user_email, message, status FROM contact_us";
$result = $connect->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us Messages</title>
    <style>
        .table-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100vh;
            background-color: #f4f4f4;
            padding: 80px;
            box-sizing: border-box;
            margin-top: 30px;
        }

        .table-container > div {
            width: 100%;
            height: 550px;
            max-width: 1000px;
            background-color: #fff;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            overflow: hidden;
            padding: 40px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            font-size: 15px;
        }

        table th {
            background-color: #4CAF50;
            color: white;
            text-transform: uppercase;
            border-bottom: 2px solid #ddd;
        }

        table td {
            color: #555;
            border-bottom: 1px solid #ddd;
        }

        table tr:nth-child(even) {
            background-color: #f8f8f8;
        }

        table tr:hover {
            background-color: #eef7ed;
        }

        .status-icon {
            font-size: 20px;
        }

        .status-icon.red {
            color: red;
        }

        .status-icon.green {
            color: green;
        }

        .no-records {
            color: red;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="table-container">
        <div>
            <h2>Contact Us Messages</h2>
            <?php
            if ($result->num_rows > 0) {
                // Display the data
                echo "<table>";
                echo "<tr>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Status</th>
                      </tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><a href='update_status_view.php?email=" . urlencode($row['user_email']) . "'>" . $row['user_email'] . "</a></td>";
                    echo "<td>" . $row['message'] . "</td>";
                    echo "<td>";
                    if ($row['status'] == '0') {
                        echo "<span class='status-icon red'>&#10060;</span>"; // Red X icon
                    } elseif ($row['status'] == '1') {
                        echo "<span class='status-icon green'>&#9989;</span>"; // Green checkmark icon
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='no-records'>No records found.</p>";
            }

            // Close the connection
            $connect->close();
            ?>
        </div>
    </div>
</body>
</html>

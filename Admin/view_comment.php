<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

// Get the blog_id from the URL
if (isset($_GET['blog_id'])) {
    $blog_id = $_GET['blog_id'];
} else {
    die("Blog ID not provided.");
}

// Check the connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Query to fetch comments for the specific blog_id
$sql = "SELECT user_email, user_name, comment, blog_id FROM blog_comment WHERE blog_id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Comments for Blog <?php echo $blog_id; ?></title>
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
            word-wrap: break-word;  /* Allow breaking long words */
            word-break: break-word; /* Ensure long words break and wrap */
            white-space: normal;    /* Allow text to wrap */
            max-width: 300px; /* Optional: Limit column width */
        }

        table tr:nth-child(even) {
            background-color: #f8f8f8;
        }

        table tr:hover {
            background-color: #eef7ed;
        }

        .no-records {
            color: red;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }

        /* General styles for the button */
        .close-btn {
            display: inline-block;
            text-decoration: none;
            background-color: #ff4d4d; /* Red background */
            color: #fff; /* White text */
            font-size: 20px; /* Visible font size */
            font-weight: bold;
            border: none;
            border-radius: 5px; /* Slightly rounded edges for modern look */
            width: 40px;
            height: 40px;
            text-align: center;
            line-height: 40px; /* Center align the text */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Subtle shadow for depth */
            transition: transform 0.2s, box-shadow 0.2s; /* Smooth hover effects */
            cursor: pointer;
            position: absolute; /* Allows precise positioning */
            margin-top: -65px; /* Adjust top distance */
            right: 360px; /* Align to the right */
        }

        /* Hover effect */
        .close-btn:hover {
            background-color: #ff1a1a; /* Darker red on hover */
            transform: scale(1.1); /* Slight zoom on hover */
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.3); /* Enhanced shadow on hover */
        }

        /* Focus outline for accessibility */
        .close-btn:focus {
            outline: 2px solid #fff; /* White outline for focus */
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="table-container">
        <div>
            <h2>Comments for Blog <?php echo $blog_id; ?></h2>
            <a href="view_blog.php" class="close-btn" aria-label="Close">&times;</a>
            <?php
            if ($result->num_rows > 0) {
                // Display the data
                echo "<table>";
                echo "<tr>
                        <th>Blog ID</th>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Comment</th>
                      </tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['blog_id'] . "</td>"; // Show blog_id in a new column
                    echo "<td>" . $row['user_email'] . "</td>";
                    echo "<td>" . $row['user_name'] . "</td>";
                    echo "<td>" . $row['comment'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='no-records'>No comments for this blog.</p>";
            }

            // Close the connection
            $connect->close();
            ?>
        </div>
    </div>
</body>
</html>

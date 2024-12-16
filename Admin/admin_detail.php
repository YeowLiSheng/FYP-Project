<?php
// Include your database connection file
include("dataconnection.php"); 
include 'admin_sidebar.php';
// Get staff_id from query parameter or session (you can adjust this to fit your logic)
$staff_id = $_GET['staff_id'] ?? 0; // If no staff_id is provided, use 0 as a fallback

// Fetch admin details from the database
$query = "SELECT * FROM admin WHERE staff_id = ?";
$stmt = $connect->prepare($query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch the admin data
    $admin = $result->fetch_assoc();
} else {
    $admin = null;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Details</title>
    <style>
        /* Ensure the body takes full height */
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            background color:white;
        }

        .admin-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 750px; /* Full viewport height */
            background-color: #f4f4f4;
            
        }

        .admin-form {
          
            margin-left: 500px;
            border: 1px solid #ccc;
            padding: 20px; /* Reduced padding */
            margin-top:45px;
            border-radius: 5px;
            background-color: white;
            width: 80%;
            height : 85%;
            width: 650px; /* Reduced max-width */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .admin-form label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        .admin-form .form-group {
            margin-bottom: 5px; /* Reduced margin */
        }

        .admin-form .form-group input {
            width: 100%;
            padding: 6px; /* Reduced padding */
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f2f2f2;
        }

        .admin-form .form-group input[readonly] {
            background-color: #e9ecef;
        }

        .admin-form .form-group img {
            width: 130px; /* Slightly reduced size */
            height: 130px; /* Slightly reduced size */
            object-fit: cover;
            border-radius: 8px;
            display: block; /* Ensures image is displayed as a block element */
            margin: 15px auto; /* Adjusted margin */
        }

        .admin-form .inline-group {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }


        .admin-form .form-group input.long {
            width: 100%; /* Make the email field wider */
        }

        /* Make the admin_id and admin_name fields wider */
        .admin-form .inline-group input#admin_id, 
        .admin-form .inline-group input#admin_name {
            width: 100%; /* Adjust this width to make these fields longer */
        }

        .not-found {
            text-align: center;
            color: red;
            font-size: 18px;
        }




    .button-back 
    {

        

        width: 100%;
        display: inline-block;
        padding: 10px 20px;
        background-color: #28a745;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        text-align: center;
        font-weight: bold;
        margin-top: 15px;
    }

.button-back:hover {
    background-color: #218838;
}

    </style>
</head>
<body>

<main>
    <div class="admin-content">
        <?php if ($admin): ?>
            <form class="admin-form" action="" method="post">
    <div class="form-group">
        <label for="admin_image">Admin Image:</label>
        <img src="<?= $admin['admin_image'] ?: 'default.png' ?>" alt="Admin Image" id="admin_image">
    </div>
    <div class="form-group inline-group">
        <div>
            <label for="admin_id">Admin ID:</label>
            <input type="text" id="admin_id" name="admin_id" value="<?= $admin['admin_id'] ?>" readonly>
        </div>
        <div>
            <label for="admin_name">Name:</label>
            <input type="text" id="admin_name" name="admin_name" value="<?= $admin['admin_name'] ?>" readonly>
        </div>
    </div>
    <div class="form-group">
        <label for="admin_email">Email:</label>
        <input type="email" id="admin_email" name="admin_email" value="<?= $admin['admin_email'] ?>" readonly class="long">
    </div>
    <div class="form-group">
        <label for="admin_contact_number">Contact Number:</label>
        <input type="text" id="admin_contact_number" name="admin_contact_number" value="<?= $admin['admin_contact_number'] ?>" readonly>
    </div>
    <div class="form-group">
        <label for="admin_gender">Gender:</label>
        <input type="text" id="admin_gender" name="admin_gender" value="<?= $admin['admin_gender'] ?>" readonly>
    </div>
    <div class="form-group">
        <label for="admin_joined_date">Joined Date:</label>
        <input type="text" id="admin_joined_date" name="admin_joined_date" value="<?= $admin['admin_joined_date'] ?>" readonly>
    </div>
    
    <!-- Add the "Back to View Admin" button -->
    <div class="form-group">
        <a href="view_admin.php" class="button-back">Back to View Admin</a>
    </div>
</form>

        <?php else: ?>
            <p class="not-found">Admin not found.</p>
        <?php endif; ?>
    </div>
</main>

</body>
</html>

<?php
// Start session at the beginning
session_start();

// Include the database connection
include("dataconnection.php");

// Check if the user is logged in and the session variable is set
if (!isset($_SESSION['id'])) {
    die("User is not logged in.");
}

$user_id = $_SESSION['id'];

// Check if the database connection is established
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Query user data
$result = mysqli_query($conn, "SELECT * FROM user WHERE user_id = '$user_id'");
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <style>
        /* Your CSS styles here */
    </style>
</head>
<body>
    <form class="edit-profile-form" action="edit_profile.php" method="POST" enctype="multipart/form-data">
        <h2>Edit Profile</h2>
        
        <!-- Profile Picture -->
        <div class="profile-image-container">
            <label for="profile_image">
                <img src="<?php echo isset($row['user_image']) ? $row['user_image'] : 'default-avatar.jpg'; ?>" alt="Profile Image" class="profile-image" id="profilePreview">
            </label>
            <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;" onchange="previewImage(event)">
        </div>
        
        <!-- Name -->
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo $row['user_name']; ?>" required>
        </div>
        
        <!-- Email -->
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo $row['user_email']; ?>" required>
        </div>
        
        <!-- Password with Eye Icon -->
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" value="<?php echo $row['user_password']; ?>" required>
            <span class="eye-icon" onclick="togglePasswordVisibility()">👁️</span>
        </div>
        
        <!-- Contact Number -->
        <div class="form-group">
            <label for="contact">Contact Number</label>
            <input type="text" id="contact" name="contact" value="<?php echo $row['user_contact_number']; ?>" required>
        </div>
        
        <!-- Address -->
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?php echo $row['user_address']; ?>" required>
        </div>
        
        <!-- Date of Birth -->
        <div class="form-group">
            <label for="dob">Date of Birth</label>
            <input type="date" id="dob" name="dob" value="<?php echo $row['user_date_of_birth']; ?>" required>
        </div>
        
        <!-- Submit Button -->
        <input type="submit" name="submitbtn" value="Save Changes" class="submit-btn">
    </form>

    <script>
        // Prevent future dates for the Date of Birth field
        document.getElementById('dob').max = new Date().toISOString().split("T")[0];

        // Function to preview the selected profile image
        function previewImage(event) {
            const profilePreview = document.getElementById('profilePreview');
            profilePreview.src = URL.createObjectURL(event.target.files[0]);
        }

        // Function to toggle the password visibility
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById("password");
            passwordInput.type = passwordInput.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>

<?php
// Handle form submission
if (isset($_POST["submitbtn"])) {
    $username = $_POST["name"];
    $email = $_POST["email"];
    $pass = $_POST["password"];
    $contact = $_POST["contact"];
    $address = $_POST["address"];
    $dob = $_POST["dob"];

    // Handle image upload
    $target_file = $row['user_image'];
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['profile_image']['name']);
        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file);
    }

    // Check for duplicate username
    $check_duplicate_username = mysqli_query($conn, "SELECT * FROM user WHERE user_name = '$username' AND user_id != $user_id");
    
    if (mysqli_num_rows($check_duplicate_username) > 0) {
        echo "<script>
                alert('This User Name already exists.');
                window.location.href='edit_profile.php';
              </script>";
    } else {
        // Update user record
        $update_query = "UPDATE user SET
                        user_image='$target_file',
                        user_name='$username',
                        user_password='$pass',
                        user_email='$email',
                        user_contact_number='$contact',
                        user_address='$address',
                        user_date_of_birth='$dob'
                        WHERE user_id = '$user_id'";
        
        if (mysqli_query($conn, $update_query)) {
            echo "<script>
                    alert('Record has been saved successfully.');
                    window.location.href='dashboard.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Failed to update the record.');
                  </script>";
        }
    }
}
?>

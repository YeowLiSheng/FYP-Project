<?php

include("dataconnection.php");
include 'admin_sidebar.php';


// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Retrieve the admin information
$admin_id = $_SESSION['admin_id'];
$result = mysqli_query($connect, "SELECT * FROM admin WHERE admin_id = '$admin_id'");

if ($result && mysqli_num_rows($result) > 0) {
    $admin_row = mysqli_fetch_assoc($result);
} else {
    echo "Admin not found.";
    exit;
}

// Handle form submission
if (isset($_POST['submitbtn'])) {
    $name = $_POST['admin_name'];
    $email = $_POST['admin_email'];
    $contact = $_POST['admin_contact'];
    $gender = $_POST['admin_gender'];

    // Use the new password if entered, otherwise keep the old one
    $password = !empty($_POST['admin_password']) ? $_POST['admin_password'] : $admin_row['admin_password'];

    // Handle profile image upload
    $image = $admin_row['admin_image'];  // Default to the current image in case no new image is uploaded

    // Check if a new image has been uploaded
    if (!empty($_FILES['admin_image']['name'])) {
        $target_dir = "uploads/";

        // Create the uploads directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true); // Create the directory with write permissions
        }

        // Generate a unique filename based on the current time to prevent overwriting
        $image_filename = time() . '_' . basename($_FILES["admin_image"]["name"]);
        $target_file = $target_dir . $image_filename;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is a valid image (JPEG, PNG, JPG)
        $valid_extensions = ['jpg', 'jpeg', 'png'];
        if (in_array($imageFileType, $valid_extensions)) {
            // Check for any file upload errors
            if ($_FILES['admin_image']['error'] === UPLOAD_ERR_OK) {
                // Move the uploaded file to the uploads directory
                if (move_uploaded_file($_FILES["admin_image"]["tmp_name"], $target_file)) {
                    $image = $target_file;  // Store the path of the uploaded image
                } else {
                    echo "Error uploading file.";
                }
            } else {
                echo "Error with file upload.";
            }
        } else {
            echo "Invalid image file format. Please upload a JPG, JPEG, or PNG file.";
        }
    }

    // Update admin data in the database, including gender and image
    $update_query = "UPDATE admin SET admin_name='$name', admin_email='$email', admin_password='$password', admin_contact_number='$contact', admin_gender='$gender', admin_image='$image' WHERE admin_id='$admin_id'";

    if (mysqli_query($connect, $update_query)) {
        echo "<script>alert('Admin profile updated successfully.'); window.location.href='admin_edit_profile.php';</script>";
    } else {
        echo "Error updating profile: " . mysqli_error($connect);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin Profile</title>

    <style>
        /* General Styling */


/* General Styling */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #427CA9;
}

/* Center the form in the page */
.edit-admin-form {
    width: 600px;  /* Increased width for a wider form */
    height: 590px;
    background-color: white;
    padding: 10px 10px; /* Increased padding for more space */
    
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin: 86px auto; /* Increased margin-top to move the form further down */
}


/* Title styling */
.edit-admin-form h2 {
    text-align: center;
    margin-bottom: 25px;
    font-size: 1.5rem;
    color: #333;
}

/* Profile Image Container */

.profile-image-container {
    text-align: center;
    margin-bottom: 20px;
}

.profile-image-container img {
    width: 90px; /* Fixed width for the image */
    height: 90px; /* Fixed height for the image */
    border-radius: 50%; /* Circular shape */
    border: 2px solid #ddd;
    object-fit: cover; /* Ensures the image maintains aspect ratio and covers the fixed size */
    margin-bottom: 10px;
}

.profile-image-container input {
    display: none;
}


/* Double Input Layout for Name and Email */
.form-group-double {
    display: flex;
    justify-content: space-between;
    gap: 15px;
}

.form-group {
    margin-bottom: 15px;
    flex: 1;
}

.form-group label {
    display: block;
    font-size: 0.9rem;
    color: #555;
    margin-bottom: 5px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #333;
    background-color: #f8f9fa;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #3b7ddd;
    outline: none;
    background-color: #fff;
}

/* Specific Styling for Email Field */
#admin_email {
    width: 100%; /* Ensure email field spans the full width */
}

/* Submit Button */
.submit-btn {
    width: 100%;
    padding: 12px;
    background-color: #3b7ddd;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-top: 15px;
}

.submit-btn:hover {
    background-color: #2874a6;
}

/* Responsive Design */
@media (max-width: 480px) {
    .edit-admin-form {
        width: 90%;
        padding: 25px;
    }

    .profile-image-container img {
        max-width: 80px;
    }

    .submit-btn {
        padding: 10px;
        font-size: 0.9rem;
    }

    .form-group-double {
        flex-direction: column;
    }

    /* Adjust email width for smaller screens */
    #admin_email {
        width: 100%; /* Full width on smaller screens */
    }
}





/* Style for the eye icon inside the input field */
.toggle-password {
    position: absolute;
    right: 20px;
    top: 70%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 18px;
    color: #666;
    transition: color 0.3s ease;
}

.toggle-password:hover {
    color: #3b7ddd; /* Change color on hover */
}

    </style>
</head>
<body>
<form class="edit-admin-form" action="" method="POST" enctype="multipart/form-data" onsubmit="return validateAdminForm()">
    <h2>Edit Admin Profile</h2>
<!-- Profile Image -->
<div class="profile-image-container">
    <label for="admin_image">
        <img src="<?php echo isset($admin_row['admin_image']) && file_exists($admin_row['admin_image']) ? $admin_row['admin_image'] : 'default-avatar.jpg'; ?>" alt="Admin Image" class="profile-image" id="adminProfilePreview">
    </label>
    <input type="file" id="admin_image" name="admin_image" accept="image/*" style="display: none;" onchange="previewAdminImage(event)">
</div>


    <!-- Admin Name and Email -->
    <div class="form-group-double">
        <div class="form-group">
            <label for="admin_name">Name</label>
            <input type="text" id="admin_name" name="admin_name" value="<?php echo htmlspecialchars($admin_row['admin_name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="admin_email">Email</label>
            <input type="email" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($admin_row['admin_email']); ?>" required>
        </div>
    </div>

    <!-- Password -->

<div class="form-group" style="position: relative;">
    <label for="admin_password">Password</label>
    <input type="password" id="admin_password" name="admin_password"
           value="<?php echo htmlspecialchars($admin_row['admin_password']); ?>"
           placeholder="Enter new password if you want to change it"
           style="padding-right: 40px;">
    <span class="toggle-password" onclick="togglePasswordVisibility()" title="Show/Hide Password">
        &#128065;
    </span>
</div>


    <!-- Contact Number -->
    <div class="form-group">
        <label for="admin_contact">Contact Number</label>
        <input type="text" id="admin_contact" name="admin_contact" value="<?php echo htmlspecialchars($admin_row['admin_contact_number']); ?>" required>
    </div>

    <!-- Gender -->
    <div class="form-group">
        <label for="admin_gender">Gender</label>
        <select id="admin_gender" name="admin_gender">
            <option value="" <?php if (empty($admin_row['admin_gender'])) echo 'selected'; ?>>Not specified</option>
            <option value="male" <?php if ($admin_row['admin_gender'] === 'male') echo 'selected'; ?>>Male</option>
            <option value="female" <?php if ($admin_row['admin_gender'] === 'female') echo 'selected'; ?>>Female</option>
        </select>
    </div>

    <!-- Submit Button -->
    <input type="submit" name="submitbtn" value="Save Changes" class="submit-btn">
</form>


<script>
    // Preview image function
    function previewAdminImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('adminProfilePreview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    function validateAdminForm() {
        // Add any specific form validation rules here
        return true;
    }



    function togglePasswordVisibility() {
        const passwordField = document.getElementById('admin_password');
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
        } else {
            passwordField.type = 'password';
        }
    }


    function validateAdminForm() {
    const name = document.getElementById("admin_name").value;
    const email = document.getElementById("admin_email").value;
    const password = document.getElementById("admin_password").value;
    const contact = document.getElementById("admin_contact").value;

    // Validate name (at least 5 characters)
    if (name.length < 5) {
        alert("Name must be at least 5 characters long.");
        return false;
    }

    // Validate email (must contain @gmail.com)
    if (!email.endsWith("@gmail.com")) {
        alert("Email must end with '@gmail.com'.");
        return false;
    }

    // Validate password (exactly 5 characters)
    if (password.length < 5) {
        alert("Password must be exactly 5 characters.");
        return false;
    }

    // Validate contact number (xxx-xxxxxxxx or xxx-xxxxxxx format)
    const contactPattern = /^\d{3}-\d{7,8}$/;
    if (!contactPattern.test(contact)) {
        alert("Contact number must be in the format xxx-xxxxxxxx or xxx-xxxxxxx.");
        return false;
    }

    return true; // If all validations pass
}

// Toggle password visibility
function togglePasswordVisibility() {
    const passwordInput = document.getElementById("admin_password");
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
    } else {
        passwordInput.type = "password";
    }
}


</script>

</body>
</html>
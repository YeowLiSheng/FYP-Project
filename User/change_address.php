<?php
session_start(); // Start the session

// Include the database connection file
include("dataconnection.php"); 

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

// Check if the database connection exists
if (!isset($connect) || !$connect) {
    die("Database connection failed.");
}

// Retrieve the user ID from the session
$user_id = $_SESSION['id'];

// Check if the delete button was clicked and handle the delete action
if (isset($_POST['deletebtn'])) {
    // Delete the address from the database
    $delete_query = "DELETE FROM user_address WHERE user_id='$user_id'";
    
    if (mysqli_query($connect, $delete_query)) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Address Deleted',
                    text: 'Address has been deleted successfully.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'edit_profile.php';
                });
            </script>
        </body>
        </html>";
    } else {
        echo "Error deleting address: " . mysqli_error($connect);
    }
    exit; // Stop further execution
}

// Fetch the user's address information based on the user ID
$address_result = mysqli_query($connect, "SELECT * FROM user_address WHERE user_id ='$user_id'");

if ($address_result) {
    if (mysqli_num_rows($address_result) > 0) {
        $address_row = mysqli_fetch_assoc($address_result); // Fetch address data
    } else {
        // No address found, redirect to add address page with a SweetAlert
        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'warning',
                    title: 'No Address Found',
                    text: 'Please add an address before editing.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'add_address.php';
                });
            </script>
        </body>
        </html>";
        exit;
    }
} else {
    echo "Query failed: " . mysqli_error($connect); // Display query error
    exit;
}

// Handle form submission to update address
if (isset($_POST['submitbtn'])) {
    // Get user input and sanitize
    $address = mysqli_real_escape_string($connect, $_POST['address']);
    $state = mysqli_real_escape_string($connect, $_POST['state']);
    $city = mysqli_real_escape_string($connect, $_POST['city']);
    $postcode = mysqli_real_escape_string($connect, $_POST['postcode']);

    // Update the address in the database
    $update_query = "UPDATE user_address SET address='$address', state='$state', city='$city', postcode='$postcode' WHERE user_id='$user_id'";

    if (mysqli_query($connect, $update_query)) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Address Updated',
                    text: 'Address has been updated successfully.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'edit_profile.php';
                });
            </script>
        </body>
        </html>";
    } else {
        echo "Error updating address: " . mysqli_error($connect);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Address</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to the CSS file -->
    <style>
    /* General body and form styling */
    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        min-height: 100vh; /* Ensure the page height is 100% of the viewport */
    }

    .edit-address-form {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 30px;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .edit-address-form h2 {
        text-align: center;
        margin-bottom: 20px;
        font-size: 24px;
        color: #333;
    }

    /* Form group styling */
    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }

    .form-group input[type="text"], .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        color: #333;
        box-sizing: border-box;
        transition: border-color 0.3s;
    }

    /* Input focus effect */
    .form-group input[type="text"]:focus, .form-group select:focus {
        border-color: #007bff;
        outline: none;
    }

    /* Error message styling */
    .error-message {
        color: red;
        font-size: 12px;
        margin-top: 5px;
        display: none; /* Hidden initially */
    }

    /* Submit and delete button styling */
    .submit-btn, .delete-btn {
        background-color: #28a745;
        color: #fff;
        padding: 10px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        width: 100%;
        cursor: pointer;
        transition: background-color 0.3s;
        margin-bottom: 10px;
    }

    .delete-btn {
        background-color: #dc3545;
    }

    .submit-btn:hover {
        background-color: #218838;
    }

    .delete-btn:hover {
        background-color: #c82333;
    }

    /* Responsive design for smaller screens */
    @media (max-width: 480px) {
        .edit-address-form {
            width: 90%;
            padding: 20px;
        }

        .submit-btn, .delete-btn {
            font-size: 14px;
            padding: 8px;
        }
    }
</style>

</head>
<body>
    <form action="" method="POST" class="edit-address-form" onsubmit="return validateForm()">
        <h2>Edit Address</h2>

        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address_row['address'] ?? ''); ?>" >
            <div class="error-message" id="address-error">Please enter your address.</div>
        </div>

        <div class="form-group">
        <label for="state">State</label>
        <select id="state" name="state">
            <option value="">Select your state</option>
            <option value="Johor" <?php echo (isset($address_row['state']) && $address_row['state'] == 'Johor') ? 'selected' : ''; ?>>Johor</option>
            <option value="Kedah" <?php echo (isset($address_row['state']) && $address_row['state'] == 'Kedah') ? 'selected' : ''; ?>>Kedah</option>
            <option value="Kelantan" <?php echo (isset($address_row['state']) && $address_row['state'] == 'Kelantan') ? 'selected' : ''; ?>>Kelantan</option>
            <option value="Malaca" <?php echo (isset($address_row['state']) && $address_row['state'] == 'Malaca') ? 'selected' : ''; ?>>Malaca</option>
            <option value="Negeri Sembilan" <?php echo (isset($address_row['state']) && $address_row['state'] == 'Negeri Sembilan') ? 'selected' : ''; ?>>Negeri Sembilan</option>
            <option value="Pahang" <?php echo (isset($address_row['state']) && $address_row['state'] == 'Pahang') ? 'selected' : ''; ?>>Pahang</option>
            <option value="Penang" <?php echo (isset($address_row['state']) && $address_row['state'] == 'Penang') ? 'selected' : ''; ?>>Penang</option>
            <option value="Perak" <?php echo (isset($address_row['state']) && $address_row['state'] == 'Perak') ? 'selected' : ''; ?>>Perak</option>
            <option value="Perlis" <?php echo (isset($address_row['state']) && $address_row['state'] == 'Perlis') ? 'selected' : ''; ?>>Perlis</option>
            <option value="Selangor" <?php echo (isset($address_row['state']) && $address_row['state'] == 'Selangor') ? 'selected' : ''; ?>>Selangor</option>
            <option value="Terengganu" <?php echo (isset($address_row['state']) && $address_row['state'] == 'Terengganu') ? 'selected' : ''; ?>>Terengganu</option>
            <option value="Kuala Lumpur" <?php echo (isset($address_row['state']) && $address_row['state'] == 'Kuala Lumpur') ? 'selected' : ''; ?>>Kuala Lumpur</option>
            <option value="Labuan" <?php echo (isset($address_row['state']) && $address_row['state'] == 'Labuan') ? 'selected' : ''; ?>>Labuan</option>
            <option value="Putrajaya" <?php echo (isset($address_row['state']) && $address_row['state'] == 'Putrajaya') ? 'selected' : ''; ?>>Putrajaya</option>
            <option value="Sabah" <?php echo (isset($address_row['state']) && $address_row['state'] == 'Sabah') ? 'selected' : ''; ?>>Sabah</option>
            <option value="Sarawak" <?php echo (isset($address_row['state']) && $address_row['state'] == 'Sarawak') ? 'selected' : ''; ?>>Sarawak</option>
        </select>
        <div class="error-message" id="state-error">Please select your state.</div>
        </div>

        <div class="form-group">
            <label for="city">City</label>
            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($address_row['city'] ?? ''); ?>" >
            <div class="error-message" id="city-error">Please enter your city.</div>
        </div>

        <div class="form-group">
            <label for="postcode">Postcode</label>
            <input type="text" id="postcode" name="postcode" value="<?php echo htmlspecialchars($address_row['postcode'] ?? ''); ?>">
            <div class="error-message" id="postcode-error">Postcode must be 5 digits.</div>
        </div>

        <!-- Update Address Button -->
        <input type="submit" name="submitbtn" value="Update Address" class="submit-btn">
        
        <!-- Delete Address Button -->
        <input type="submit" name="deletebtn" value="Delete Address" class="delete-btn">
    </form>

    <script>
        function validateForm() {
            let isValid = true;

            // Clear all error messages initially
            clearErrorMessages();

            // Get form values
            const address = document.getElementById('address').value.trim();
            const state = document.getElementById('state').value.trim();
            const city = document.getElementById('city').value.trim();
            const postcode = document.getElementById('postcode').value.trim();

            // Validation checks
            if (address === "") {
                showError('address-error', 'Please enter your address.');
                isValid = false;
            }
            if (state === "") {
                showError('state-error', 'Please enter your state.');
                isValid = false;
            }
            if (city === "") {
                showError('city-error', 'Please enter your city.');
                isValid = false;
            }
            if (postcode.length !== 5 || isNaN(postcode)) {
                showError('postcode-error', 'Postcode must be 5 digits.');
                isValid = false;
            }

            return isValid; // Prevent form submission if not valid
        }

        function showError(id, message) {
            const errorElement = document.getElementById(id);
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }

        function clearErrorMessages() {
            document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
        }

        // Attach input event listeners to each field to clear errors in real-time
        document.getElementById('address').addEventListener('input', function() {
            if (this.value.trim() !== "") {
                document.getElementById('address-error').style.display = 'none';
            }
        });

        document.getElement

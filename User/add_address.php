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

// Check if the user already has an address
$address_result = mysqli_query($connect, "SELECT * FROM user_address WHERE user_id ='$user_id'");

$hasAddress = false; // Initialize a flag to check if the user has an address

if ($address_result) {
    if (mysqli_num_rows($address_result) > 0) {
        // Address already exists
        $hasAddress = true; // Set the flag to true
    }
} else {
    echo "Query failed: " . mysqli_error($connect); // Display query error
    exit;
}

// Handle form submission for adding a new address
if (isset($_POST['submitbtn'])) {
    // Get user input and sanitize
    $address = mysqli_real_escape_string($connect, $_POST['address']);
    $state = mysqli_real_escape_string($connect, $_POST['state']);
    $city = mysqli_real_escape_string($connect, $_POST['city']);
    $postcode = mysqli_real_escape_string($connect, $_POST['postcode']);

    // Insert the new address record
    $insert_query = "INSERT INTO user_address (user_id, address, state, city, postcode) VALUES ('$user_id', '$address', '$state', '$city', '$postcode')";

    if (mysqli_query($connect, $insert_query)) {
        echo "<script type='text/javascript'>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Success',
                        text: 'Address has been added successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(function() {
                        window.location.href='edit_profile.php';
                    });
                });
              </script>";
    } else {
        echo "<script type='text/javascript'>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error adding address: " . mysqli_error($connect) . "',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
              </script>";
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Address</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add your CSS file -->
    <!-- SweetAlert CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Form Container */
        .edit-address-form {
            max-width: 500px;
            margin: 100px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Form Title */
        .edit-address-form h2 {
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        /* Form Group */
        .form-group {
            margin-bottom: 15px;
            position: relative;
        }

        /* Labels */
        .form-group label {
            font-size: 14px;
            font-weight: 600;
            color: #555;
            display: block;
            margin-bottom: 5px;
        }

        /* Input Fields */
        .form-group input[type="text"] {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .form-group input[type="text"]:focus {
            border-color: #4CAF50;
            outline: none;
        }

        /* Submit Button */
        .submit-btn {
            display: block;
            width: 100%;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            color: white;
            background-color: #4CAF50;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #45a049;
        }

        /* Styled Back Button */
        .back-btn {
            display: inline-block;
            width: 95%;
            text-align: center;
            padding: 12px;
            margin-top: 15px;
            font-size: 16px;
            font-weight: bold;
            color: white;
            background-color: #f44336;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #e53935;
        }

        /* Error Messages */
        .error-message {
            color: red;
            font-size: 12px;
            margin-top: 5px;
            display: none; /* Hidden by default */
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .edit-address-form {
                padding: 15px;
            }

            .submit-btn {
                font-size: 14px;
            }
        }
    </style>
    <script>
        function validateForm() {
            let address = document.getElementById("address").value;
            let state = document.getElementById("state").value;
            let city = document.getElementById("city").value;
            let postcode = document.getElementById("postcode").value;

            let valid = true;

            // Reset error messages
            document.getElementById("address-error").style.display = "none";
            document.getElementById("state-error").style.display = "none";
            document.getElementById("city-error").style.display = "none";
            document.getElementById("postcode-error").style.display = "none";

            // Check address
            if (address.trim() === "") {
                document.getElementById("address-error").style.display = "block";
                valid = false;
            }

            // Check state
            if (state.trim() === "") {
                document.getElementById("state-error").style.display = "block";
                valid = false;
            }

            // Check city
            if (city.trim() === "") {
                document.getElementById("city-error").style.display = "block";
                valid = false;
            }

            // Check postcode
            if (!/^\d{5}$/.test(postcode)) {
                document.getElementById("postcode-error").style.display = "block";
                valid = false;
            }

            return valid; // Return false to prevent form submission if invalid
        }

        function hideErrorMessage(inputId, errorMessageId) {
            const input = document.getElementById(inputId);
            const errorMessage = document.getElementById(errorMessageId);

            // Hide error message if the input is valid
            if (input.value.trim() !== "") {
                errorMessage.style.display = "none";
            } else {
                errorMessage.style.display = "block"; // Show error if empty
            }
        }

        // Add event listeners to hide error messages on input
        window.onload = function() {
            document.getElementById("address").addEventListener("input", function() {
                hideErrorMessage("address", "address-error");
            });

            document.getElementById("state").addEventListener("input", function() {
                hideErrorMessage("state", "state-error");
            });

            document.getElementById("city").addEventListener("input", function() {
                hideErrorMessage("city", "city-error");
            });

            document.getElementById("postcode").addEventListener("input", function() {
                const postcode = document.getElementById("postcode");
                const postcodeError = document.getElementById("postcode-error");

                if (/^\d{5}$/.test(postcode.value)) {
                    postcodeError.style.display = "none";
                } else {
                    postcodeError.style.display = "block"; // Show error if invalid
                }
            });
        }
    </script>
</head>
<body>

<?php if ($hasAddress): ?>
    <script type="text/javascript">
        Swal.fire({
            title: 'Error',
            text: 'You already have an address.',
            icon: 'error',
            confirmButtonText: 'OK'
        }).then(function() {
            window.location.href = 'edit_profile.php'; // Redirect after SweetAlert
        });
    </script>
<?php endif; ?>

<form action="" method="POST" class="edit-address-form" onsubmit="return validateForm()">
    <h2>Add Address</h2>

    <div class="form-group">
        <label for="address">Address</label>
        <input type="text" id="address" name="address" >
        <div class="error-message" id="address-error">Please enter your address.</div>
    </div>

    <div class="form-group">
        <label for="state">State</label>
        <input type="text" id="state" name="state" >
        <div class="error-message" id="state-error">Please enter your state.</div>
    </div>

    <div class="form-group">
        <label for="city">City</label>
        <input type="text" id="city" name="city" >
        <div class="error-message" id="city-error">Please enter your city.</div>
    </div>

    <div class="form-group">
        <label for="postcode">Postcode</label>
        <input type="text" id="postcode" name="postcode" maxlength="5" >
        <div class="error-message" id="postcode-error">Please enter a valid postcode (5 digits).</div>
    </div>

    <button type="submit" name="submitbtn" class="submit-btn">Submit</button>
    <a href="edit_profile.php" class="back-btn">Back</a>
</form>

</body>
</html>

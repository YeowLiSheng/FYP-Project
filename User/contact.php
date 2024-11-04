

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <style>
        /* Basic styling for the form */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        textarea {
            height: 100px;
        }

        .submit-btn {
            display: inline-block;
            padding: 10px 20px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }

        /* Map styling */
        #map {
            width: 100%;
            height: 300px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body class="animsition">




<div class="container">

    <h1>Contact Us</h1>
    
    <!-- Map Section -->
    <div id="map">
        <!-- Embed Google Maps -->
        <iframe
            width="100%"
            height="100%"
            frameborder="0"
            style="border:0"
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3151.8354345097074!2d144.95565211531896!3d-37.816279179751566!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6ad65d43b2c97c4d%3A0x1e9c0f736c0e839b!2sMelbourne%20CBD%2C%20Melbourne%20VIC%2C%20Australia!5e0!3m2!1sen!2sus!4v1600000000000!5m2!1sen!2sus"
            allowfullscreen
            aria-hidden="false"
            tabindex="0">
        </iframe>
    </div>

    <!-- Contact Form -->
    <form action="contact.php" method="POST">
        <?php if (isset($_SESSION['id'])): ?>
            <!-- Display email if user is logged in -->
            <label>Email:</label>
            <input type="text" name="email" value="<?php echo htmlspecialchars($user_email); ?>" readonly>
        <?php else: ?>
            <!-- Show email input if user is not logged in -->
            <label>Email:</label>
            <input type="email" name="email" required>
        <?php endif; ?>

        <label>Message:</label>
        <textarea name="message" required></textarea>

        <button type="submit" name="submitbtn" class="submit-btn">Send Message</button>
    </form>
</div>

</body>
</html>


<?php
session_start();
include 'dataconnection.php'; // Make sure this file establishes a connection to your database

// Check if the form was submitted
if (isset($_POST['submitbtn'])) {
    // Check if user is logged in and retrieve their email and ID
    if (isset($_SESSION['id'])) {
        $user_id = $_SESSION['id']; // Assume this is stored in the session upon login
        $user_email = $_SESSION['email']; // Assuming email is also stored in session
    } else {
        // If user is not logged in, get the email from the form input
        $user_id = null;
        $user_email = $_POST['email'];
    }

    // Get the message from the form
    $message = $_POST['message'];

    // Prepare the SQL statement to insert into the contact_us table
    $stmt = $connect->prepare("INSERT INTO contact_us (user_email, message, user_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $user_email, $message, $user_id);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<script>alert('Successful Send The Message.');window.location.href='contact.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $connect->close();
}
?>


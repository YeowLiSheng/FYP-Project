<?php
// Database connection
include 'dataconnection.php'; // Ensure this file contains the $connect variable
include 'admin_sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $description = $_POST['description'];
    $date = $_POST['date'];

    // Handle file upload
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["picture"]["name"]);
    $uploadOk = move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file);

    if ($uploadOk) {
        // Corrected SQL query without an extra comma
        $sql = "INSERT INTO blog (picture, title, subtitle, description, date) VALUES ('$target_file', '$title', '$subtitle', '$description', '$date')";

        if (mysqli_query($connect, $sql)) {
            echo "<script>alert('Blog added successfully.');window.location.href='add_blog.php';</script>";
        } else {
            echo "Error: " . mysqli_error($connect);
        }
    } else {
        echo "<script>alert('Sorry, there was an error uploading your file.');window.location.href='add_blog.php';</script>";
    }
}

mysqli_close($connect);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Blog</title>
    <style>
        .container {
            margin-top: 70px;
            width: 100%;
            max-width: 900px; /* Increased max-width to make the form a little bigger */
            background: #fff;
            padding: 30px; /* Increased padding for a bigger form */
            border-radius: 10px; /* Softer rounded corners */
            box-shadow: 0px 15px 20px rgba(0, 0, 0, 0.1); /* Beautiful and subtle shadow */
            transition: box-shadow 0.3s ease-in-out; /* Smooth transition for shadow effect */
        }

        .container:hover {
            box-shadow: 0px 20px 30px rgba(0, 0, 0, 0.15); /* More pronounced shadow on hover */
        }

        h2 {
                text-align: center;
                margin-bottom: 20px;
                color: #4CAF50; /* Main color */
                font-size: 36px; /* Larger font for emphasis */
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Smooth and modern font */
                letter-spacing: 2px; /* Increased letter spacing for style */
                text-transform: uppercase; /* Capitalized letters for boldness */
                background: linear-gradient(45deg, #4CAF50, #81C784); /* Gradient background */
                color: transparent; /* Text color becomes transparent */
                background-clip: text; /* Clips the background to the text */
                padding: 10px 0; /* Added padding for spacing */

            }


        label {
            font-weight: bold;
            margin-bottom: 15px;
            display: block;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px; 
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 18px;
        }

        textarea {
            resize: vertical;
            height: 150px;
        }

        button {
            width: 100%;
            padding: 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .image-preview {
            margin-bottom: 30px;
            text-align: center;
            cursor: pointer;
            margin-top: 20px;
            min-height: 180px;
        }

        .image-preview img {
            max-width: 100%;
            height: 300px;
            max-height: 300px;
            width: 700px;
            border-radius: 4px;
        }

        input[type="file"] {
            display: none; /* Hide the file input */
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

  

            button {
                font-size: 16px;
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Add Blog</h2>
        <form action="" method="POST" enctype="multipart/form-data">

            <label for="picture">Picture:</label>
            <input type="file" id="picture" name="picture" accept="image/*" required onchange="previewImage()">
            <div class="image-preview" id="imagePreview" onclick="document.getElementById('picture').click();">
                <!-- The image will be displayed here -->
            </div>

            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="subtitle">Subtitle:</label>
            <input type="text" id="subtitle" name="subtitle" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="5" required></textarea>

            <label for="date">Date (e.g., 12Jan2024):</label>
            <input type="text" id="date" name="date" required>

            <button type="submit">Add Blog</button>
        </form>
    </div>

    <script>
        function previewImage() {
            const file = document.getElementById('picture').files[0];
            const preview = document.getElementById('imagePreview');

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Image Preview">`;
                }

                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        }
    </script>
</body>
</html>

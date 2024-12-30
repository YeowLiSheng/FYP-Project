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
    $target_dir = "blog/"; // Define directory to store the uploaded images

    // Ensure the directory exists, create it if it doesn't
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $filename = basename($_FILES["picture"]["name"]); // Get the filename only
    $target_file = $target_dir . $filename;  // Path where the image will be saved

    $uploadOk = move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file);

    if ($uploadOk) {
        // Store only the filename in the database
        $image_path = $filename; // Store the file name only (e.g., "image.jpg")

        // Insert into the database
        $sql = "INSERT INTO blog (picture, title, subtitle, description, date) 
                VALUES ('$image_path', '$title', '$subtitle', '$description', '$date')";

        if (mysqli_query($connect, $sql)) {
            // Success, display SweetAlert
            echo "<!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Blog added successfully',
                        text: 'The blog has been added to the database.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'view_blog.php';
                    });
                </script>
            </body>
            </html>";
            exit;
        } else {
            // Error in database, show SweetAlert
            echo "<!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error adding blog',
                        text: 'There was an issue while adding the blog. Please try again.',
                        confirmButtonText: 'OK'
                    });
                </script>
            </body>
            </html>";
            exit;
        }
    } else {
        // Error uploading file, show SweetAlert
        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error uploading file',
                    text: 'Sorry, there was an error uploading your file. Please try again.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'add_blog.php';
                });
            </script>
        </body>
        </html>";
        exit;
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
            max-width: 900px;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 15px 20px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease-in-out;
        }

        .container:hover {
            box-shadow: 0px 20px 30px rgba(0, 0, 0, 0.15);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #4CAF50;
            font-size: 36px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            letter-spacing: 2px;
            text-transform: uppercase;
            background: linear-gradient(45deg, #4CAF50, #81C784);
            color: transparent;
            background-clip: text;
            padding: 10px 0;
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
    <div class="container">
        <h2>Add Blog</h2>
        <a href="view_blog.php" class="close-btn" aria-label="Close">&times;</a>
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

</body>
</html>

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

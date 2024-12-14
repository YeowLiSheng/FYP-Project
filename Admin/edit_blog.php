<?php
// Database connection
include 'dataconnection.php'; // Ensure this file contains the $connect variable
include 'admin_sidebar.php';

// Get the blog_id from the URL parameter (e.g., edit_blog.php?blog_id=13)
if (isset($_GET['blog_id'])) {
    $blog_id = $_GET['blog_id'];

    // Retrieve the blog data from the database
    $sql = "SELECT * FROM blog WHERE blog_id = $blog_id";
    $result = mysqli_query($connect, $sql);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $title = $row['title'];
        $subtitle = $row['subtitle'];
        $description = $row['description'];
        $date = $row['date'];
        $picture = $row['picture']; // Current image filename
    } else {
        echo "<script>alert('Blog not found.');window.location.href='view_blog.php';</script>";
    }

    // Update the blog details when the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize input to prevent SQL injection
        $title = mysqli_real_escape_string($connect, $_POST['title']);
        $subtitle = mysqli_real_escape_string($connect, $_POST['subtitle']);
        $description = mysqli_real_escape_string($connect, $_POST['description']);
        $date = mysqli_real_escape_string($connect, $_POST['date']);

        // Handle file upload (optional: update image if a new one is uploaded)
        if ($_FILES["picture"]["name"]) {
            $target_dir = "blog/";

            // Ensure the directory exists, create it if it doesn't
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $filename = basename($_FILES["picture"]["name"]);
            $target_file = $target_dir . $filename;

            $uploadOk = move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file);

            if ($uploadOk) {
                $image_path = $filename;
            } else {
                echo "<script>alert('Error uploading new image.');</script>";
                $image_path = $picture; // Retain the old image if new one fails
            }
        } else {
            $image_path = $picture; // Keep the current image if no new image is uploaded
        }

        // Update the blog in the database
        $sql_update = "UPDATE blog SET picture = '$image_path', title = '$title', subtitle = '$subtitle', description = '$description', date = '$date' WHERE blog_id = $blog_id";

        if (mysqli_query($connect, $sql_update)) {
            echo "<script>alert('Blog updated successfully.');window.location.href='view_blog.php';</script>";
        } else {
            echo "Error: " . mysqli_error($connect);
        }
    }

    mysqli_close($connect);
} else {
    echo "<script>alert('Blog ID missing.');window.location.href='view_blog.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog</title>
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
            display: none;
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

        .close-btn {
            display: inline-block;
            text-decoration: none;
            background-color: #ff4d4d;
            color: #fff;
            font-size: 20px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            width: 40px;
            height: 40px;
            text-align: center;
            line-height: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            position: absolute;
            margin-top: -65px;
            right: 360px;
        }

        .close-btn:hover {
            background-color: #ff1a1a;
            transform: scale(1.1);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Blog</h2>
        <a href="view_blog.php" class="close-btn" aria-label="Close">&times;</a>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="picture">Picture:</label>
            <input type="file" id="picture" name="picture" accept="image/*" onchange="previewImage()">
            <div class="image-preview" id="imagePreview" onclick="document.getElementById('picture').click();">
                <!-- The current image will be displayed here -->
                <?php if ($picture) { ?>
                    <img src="blog/<?php echo $picture; ?>" alt="Image Preview">
                <?php } ?>
            </div>

            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?php echo $title; ?>" required>

            <label for="subtitle">Subtitle:</label>
            <input type="text" id="subtitle" name="subtitle" value="<?php echo $subtitle; ?>" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="5" required><?php echo $description; ?></textarea>

            <label for="date">Date (e.g., 12Jan2024):</label>
            <input type="text" id="date" name="date" value="<?php echo $date; ?>" required>

            <button type="submit">Update Blog</button>
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

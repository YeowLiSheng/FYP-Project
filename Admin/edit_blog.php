<?php
// Database connection
include 'dataconnection.php'; // Ensure this file contains the $connect variable
include 'admin_sidebar.php';

if (isset($_GET['id'])) {
    $blog_id = $_GET['id'];

    // Retrieve blog data from the database
    $sql = "SELECT * FROM blog WHERE blog_id = $blog_id";
    $result = mysqli_query($connect, $sql);
    $row = mysqli_fetch_assoc($result);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $description = $_POST['description'];
    $date = $_POST['date'];

    // Handle file upload
    $target_dir = "blog/"; // Define directory to store the uploaded images
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (isset($_FILES["picture"]) && $_FILES["picture"]["error"] == 0) {
        // Handle image upload
        $filename = basename($_FILES["picture"]["name"]);
        $target_file = $target_dir . $filename;
        $uploadOk = move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file);

        if ($uploadOk) {
            $image_path = $filename;
        } else {
            echo "<script>alert('Sorry, there was an error uploading your file.');window.location.href='edit_blog.php?id=$blog_id';</script>";
            exit;
        }
    } else {
        // If no new image is uploaded, keep the old one
        $image_path = $row['picture'];
    }

    // Update the blog data in the database
    $sql = "UPDATE blog SET 
            picture = '$image_path', 
            title = '$title', 
            subtitle = '$subtitle', 
            description = '$description', 
            date = '$date' 
            WHERE blog_id = $blog_id";

    if (mysqli_query($connect, $sql)) {
        echo "<script>alert('Blog updated successfully.');window.location.href='edit_blog.php?id=$blog_id';</script>";
    } else {
        echo "Error: " . mysqli_error($connect);
    }
}

mysqli_close($connect);
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
        <h2>Edit Blog</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="picture">Picture:</label>
            <input type="file" id="picture" name="picture" accept="image/*" onchange="previewImage()">
            <div class="image-preview" id="imagePreview" onclick="document.getElementById('picture').click();">
                <?php if ($row['picture']): ?>
                    <img src="blog/<?php echo $row['picture']; ?>" alt="Current Blog Image">
                <?php endif; ?>
            </div>

            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" required>

            <label for="subtitle">Subtitle:</label>
            <input type="text" id="subtitle" name="subtitle" value="<?php echo htmlspecialchars($row['subtitle']); ?>" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($row['description']); ?></textarea>

            <label for="date">Date (e.g., 12Jan2024):</label>
            <input type="text" id="date" name="date" value="<?php echo htmlspecialchars($row['date']); ?>" required>

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

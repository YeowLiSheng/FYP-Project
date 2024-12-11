<?php
session_start(); // Start the session at the beginning of the script

// Include your database connection file
include 'dataconnection.php'; 

// Handle admin login
if (isset($_POST["admin_login"])) {
    $id = $_POST["id"];
    $pw = $_POST["pw"];

    $find = "SELECT * FROM admin WHERE admin_id = '$id'";
    $result = mysqli_query($connect, $find);
    
    // Check if the query was successful
    if (!$result) {
        die("Database query failed: " . mysqli_error($connect));
    }
    
    $row = mysqli_fetch_array($result);

    // Check if the admin exists
    if (empty($row)) {
        $_SESSION['login_text'] = "The admin '$id' does not exist";
        $_SESSION['login_icon'] = "error";
        header("location: admin_login.php");
        exit();
    } else {
        // Check if the password is correct
        if ($pw != $row['admin_password']) {
            $_SESSION['login_text'] = "Incorrect password";
            $_SESSION['login_icon'] = "error";
            header("location: admin_login.php");
            exit();
        } else {
            // Check if the admin status is inactive
            if ($row["admin_status"] == 2) {
                $_SESSION["login_text"] = "The admin $id is inactive";
                $_SESSION["login_icon"] = "error";
                header("location: admin_login.php");
                exit();
            } else {
                // Successful login
                $_SESSION['staff_id'] = $row['staff_id'];
                $_SESSION['admin_id'] = $id;
                $_SESSION['login_text'] = "Successful login"; // Success message
                $_SESSION['login_icon'] = "success"; // For success
                header("location: admin_sidebar.php");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOMsI2D0A1dE0IYqX5M4Y6zBniIIc3hXQ1zdLtv" crossorigin="anonymous">

</head>


<style>
    /* Center the login form and set styling */
    .admin_login {
        background-color: rgba(255, 255, 255, 0.85);
        border-radius: 8px;
        padding: 20px;
        width: 100%;
        max-width: 400px;
        position: relative;
        z-index: 2;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    body {
        margin: 0;
        padding: 0;
        height: 100vh;
        overflow: hidden;
        background: linear-gradient(135deg, #ff9a9e, #fad0c4, #fad0c4, #fbc2eb, #a18cd1, #fbc2eb, #fad0c4, #ff9a9e);
        background-size: 200% 200%;
        animation: gradient 15s ease infinite;
        position: relative;
    }

    @keyframes gradient {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    ion-icon {
        cursor: pointer;
    }

</style>

<body>

    <!-- Display session messages -->
    <div class="container mt-3">
        <?php if (isset($_SESSION['login_text'])): ?>
            <div class="alert alert-<?php echo ($_SESSION['login_icon'] === 'error') ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['login_text']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['login_text']); // Clear the message after displaying ?>
        <?php endif; ?>
    </div>

    <form class="admin_login position-absolute top-50 start-50 translate-middle" action="admin_login.php" method="POST">
        <h2 class="text-center">Admin Login</h2>
        <p class="text-center">Make sure that you had added to Admin</p>

        <!-- Admin ID Field -->
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="floatingInput" placeholder="Admin ID" name="id" required>
            <label for="floatingInput">Admin Id</label>
        </div>

        <!-- Password Field with Eye Icon -->
        <div class="form-floating mb-3 position-relative">
            <input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="pw" required>
            <label for="floatingPassword">Password</label>
           
        </div>


        <button type="submit" class="btn btn-primary w-100" name="admin_login" value="a_login">Login</button>
    </form>


</script>

</body>
</html>

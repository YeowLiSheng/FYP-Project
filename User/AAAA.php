<?php
session_start(); // Start the session

// Include the database connection file
include("dataconnection.php"); 

// Check if the user is logged i
if (!isset($_SESSION['id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

// Check if the database connection exists
if (!isset($connect) || !$connect) { // Changed $connect to $conn
    die("Database connection failed.");
}

// Retrieve the user information
$user_id = $_SESSION['id'];
$result = mysqli_query($connect, "SELECT * FROM user WHERE user_id ='$user_id'"); // Changed $connect to $conn

// Check if the query was successful and fetch user data
if ($result && mysqli_num_rows($result) > 0) {
	$user_data = mysqli_fetch_assoc($result);
	$user_name = htmlspecialchars($user_data["user_name"]); // Get the user name
	 $user_email = $user_data['user_email'];
} else {
	echo "User not found.";
	exit;
}

// Initialize total_price before fetching cart items
$total_price = 0;

// Fetch and combine cart items for the logged-in user where the product_id is the same
// Fetch and combine cart items with stock information
$cart_items_query = "
    SELECT sc.product_id, p.product_name, p.product_image, p.product_price, p.product_stock, 
		   sc.color, sc.size,    
		   SUM(sc.qty) AS total_qty, 
           SUM(sc.qty * p.product_price) AS total_price, 
           MAX(sc.final_total_price) AS final_total_price, 
           MAX(sc.voucher_applied) AS voucher_applied
    FROM shopping_cart sc 
    JOIN product p ON sc.product_id = p.product_id 
    WHERE sc.user_id = $user_id 
    GROUP BY sc.product_id, sc.color, sc.size";
$cart_items_result = $connect->query($cart_items_query);

// Calculate total price and final total price
if ($cart_items_result && $cart_items_result->num_rows > 0) {
    while ($cart_item = $cart_items_result->fetch_assoc()) {
        $total_price += $cart_item['total_price'];
    }
}

// Count distinct product IDs in the shopping cart for the logged-in user
$distinct_products_query = "SELECT COUNT(DISTINCT product_id) AS distinct_count FROM shopping_cart WHERE user_id = $user_id";
$distinct_products_result = $connect->query($distinct_products_query);
$distinct_count = 0;

if ($distinct_products_result) {
    $row = $distinct_products_result->fetch_assoc();
    $distinct_count = $row['distinct_count'] ?? 0;
}



// Handle form submission
if (isset($_POST['submitbtn'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $dob = $_POST['dob'];

    // Use the new password if provided; otherwise, keep the old password
    $password = !empty($_POST['password']) ? $_POST['password'] : $row['user_password'];

    // Handle profile image upload
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "uploads/"; // Set your upload directory
        $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
        
        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $image = $target_file; // Set the new image path
        } else {
            $image = $row['user_image']; // Keep the old image if upload fails
        }
    } else {
        $image = $row['user_image']; // Keep the old image if no new image is uploaded
    }

    // Update the user data in the database (including the new or existing password)
    $update_query = "UPDATE user SET user_name='$name', user_email='$email', user_password='$password', user_contact_number='$contact', user_date_of_birth='$dob', user_image='$image' WHERE user_id='$user_id'";
    
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
					title: 'Record Saved',
					text: 'Your profile has been saved successfully.',
					confirmButtonText: 'OK'
				}).then(() => {
					window.location.href = 'edit_profile.php';
				});
			</script>
		</body>
		</html>";
	} else {
		echo "Error updating profile: " . mysqli_error($connect);
	}
	
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>About</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
<!--===============================================================================================-->	
	<link rel="icon" type="image/png" href="images/icons/favicon.png"/>
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/iconic/css/material-design-iconic-font.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/linearicons-v1.0.0/icon-font.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
<!--===============================================================================================-->	
	<link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/animsition/css/animsition.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/perfect-scrollbar/perfect-scrollbar.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" type="text/css" href="css/main.css">
<!--===============================================================================================-->
</head>
<style>
.rainbow-text {
    background: linear-gradient(to right, red, orange, yellow, green, indigo, violet);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent; /* Ensures the text is transparent on WebKit browsers */
    color: transparent; /* Ensures the text is transparent on non-WebKit browsers */
    font-weight: bold;
    background-size: 200%;
    animation: fadeRainbow 2s infinite;
}

@keyframes fadeRainbow {
    0% { background-position: 0%; }
    100% { background-position: 100%; }
}
.hov-img0 {
    width: 100%; /* Adjust width as needed */
    height: 100%; /* Adjust height as needed */
    overflow: hidden; /* Ensures the video doesn’t exceed the container bounds */
}
.responsive-video {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Ensures the video covers the container without stretching */
}
/* Hide all default controls */
.no-controls::-webkit-media-controls,
.no-controls::-webkit-media-controls-enclosure {
    display: none !important;
}

/* Hide controls for other browsers */
.no-controls::-moz-media-controls,
.no-controls::-moz-media-controls-enclosure {
    display: none !important;
}

/* Prevent interaction with the video */
.no-controls {
    pointer-events: none;
}





</style>
<body class="animsition">
	
	<!-- Header -->
	<!-- Header -->
	<header class="header-v4">
		<!-- Header desktop -->
		<div class="container-menu-desktop">
			<!-- Topbar -->
			<div class="top-bar">
				<div class="content-topbar flex-sb-m h-full container">
					<div class="left-top-bar" style="white-space: nowrap; overflow: hidden; display: block; flex: 1; max-width: calc(100% - 300px);">
						<span style="display: inline-block; animation: marquee 20s linear infinite;">
							Free shipping for standard order over $10000 <span style="padding-left: 300px;"></span> 
							New user will get 10% discount!!!<span style="padding-left: 300px;"></span>
							Get 5% discount for any purchasement above $5000 (code: DIS4FIVE)
							<span style="padding-left: 300px;"></span> Free shipping for standard order over $10000 
							<span style="padding-left: 300px;"></span> New user will get 10% discount!!! 
							<span style="padding-left: 300px;"></span> Get 5% discount for any purchasement above $5000 (code: DIS4FIVE)
						</span>
						<style>
							@keyframes marquee {
								0% {
									transform: translateX(0);
								}
								100% {
									transform: translateX(-55%);
								}
							}
						</style>
					</div>

					<div class="right-top-bar flex-w h-full">
						<a href="faq.php" class="flex-c-m trans-04 p-lr-25">
							Help & FAQs
						</a>

						

						<a href="#" class="flex-c-m trans-04 p-lr-25">
							EN
						</a>

						<a href="#" class="flex-c-m trans-04 p-lr-25">
							USD
						</a>




                        <a href="Order.php?user=<?php echo $user_id; ?>" class="flex-c-m trans-04 p-lr-25">
                            <?php
								echo "HI '" . htmlspecialchars($user_data['user_name']);
                            ?>
                        </a>


                        <a href="log_out.php" class="flex-c-m trans-04 p-lr-25">
							LOG OUT
						</a>



					</div>
				</div>
			</div>

			<div class="wrap-menu-desktop how-shadow1">
				<nav class="limiter-menu-desktop container">
					
					<!-- Logo desktop -->		
					<a href="dashboard.php" class="logo">
						<img src="images/YLS2.jpg" alt="IMG-LOGO">
					</a>

					<!-- Menu desktop -->
					<div class="menu-desktop">
						<ul class="main-menu">
							<li>
								<a href="dashboard.php">Home</a>
							</li>

							<li class="active-menu">
								<a href="product.php">Shop</a>
							</li>

                            <li>
								<a href="package.php">Packages</a>
							</li>

							<li class="label1" data-label1="hot">
								<a href="voucher_page.php">Voucher</a>
							</li>

							<li>
								<a href="blog.php">Blog</a>
							</li>

							<li>
								<a href="about.php">About</a>
							</li>

							<li>
								<a href="contact.php">Contact</a>
							</li>
						</ul>
					</div>	

					<!-- Icon header -->
					<div class="wrap-icon-header flex-w flex-r-m">
						<div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 js-show-modal-search">
							<i class="zmdi zmdi-search"></i>
						</div>

						<div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti js-show-cart" data-notify="<?php echo $distinct_count; ?>">
							<i class="zmdi zmdi-shopping-cart"></i>
						</div>

						<a href="#" class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti" >
							<i class="zmdi zmdi-favorite-outline"></i>
						</a>
					</div>
				</nav>
			</div>	
		</div>

		<!-- Modal Search -->
		<div class="modal-search-header flex-c-m trans-04 js-hide-modal-search">
			<div class="container-search-header">
				<button class="flex-c-m btn-hide-modal-search trans-04 js-hide-modal-search">
					<img src="images/icons/icon-close2.png" alt="CLOSE">
				</button>

				<form class="wrap-search-header flex-w p-l-15">
					<button class="flex-c-m trans-04">
						<i class="zmdi zmdi-search"></i>
					</button>
					<input class="plh3" type="text" name="search" placeholder="Search...">
				</form>
			</div>
		</div>
	</header>

<!-- Cart -->
<div class="wrap-header-cart js-panel-cart">
    <div class="s-full js-hide-cart"></div>

    <div class="header-cart flex-col-l p-l-65 p-r-25">
        <div class="header-cart-title flex-w flex-sb-m p-b-8">
            <span class="mtext-103 cl2">
                Your Cart
            </span>

            <div class="fs-35 lh-10 cl2 p-lr-5 pointer hov-cl1 trans-04 js-hide-cart">
                <i class="zmdi zmdi-close"></i>
            </div>
        </div>
        
        <div class="header-cart-content flex-w js-pscroll">
            <ul class="header-cart-wrapitem w-full" id="cart-items">
                <?php
                // Display combined cart items
                $total_price = 0;
                if ($cart_items_result->num_rows > 0) {
                    while($cart_item = $cart_items_result->fetch_assoc()) {
                        $total_price += $cart_item['total_price'];
                        echo '
                        <li class="header-cart-item flex-w flex-t m-b-12">
                            <div class="header-cart-item-img">
                                <img src="images/' . $cart_item['product_image'] . '" alt="IMG">
                            </div>
                            <div class="header-cart-item-txt p-t-8">
                                <a href="#" class="header-cart-item-name m-b-18 hov-cl1 trans-04">
                                    ' . $cart_item['product_name'] . '
                                </a>
                                <span class="header-cart-item-info">
                                    ' . $cart_item['total_qty'] . ' x $' . number_format($cart_item['product_price'], 2) . '
                                </span>
                            </div>
                        </li>';
                    }
                } else {
                    echo '<p>Your cart is empty.</p>';
                }
                ?>
            </ul>
            
            <div class="w-full">
                <div class="header-cart-total w-full p-tb-40">
                    Total: $<span id="cart-total"><?php echo number_format($total_price, 2); ?></span>
                </div>

                <div class="header-cart-buttons flex-w w-full">
                    <a href="shoping-cart.html" class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-r-8 m-b-10">
                        View Cart
                    </a>

                    <a href="shoping-cart.html" class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-b-10">
                        Check Out
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>




<form class="edit-profile-form" action="" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()" style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;">
    <h2 style="text-align: center; color: #333;">Edit Profile</h2>

    <!-- Profile Picture -->
    <div class="profile-image-container" style="text-align: center; margin-bottom: 20px;">
        <label for="profile_image" style="cursor: pointer;">
            <img src="<?php echo isset($user_data['user_image']) ? $user_data['user_image'] : 'default-avatar.jpg'; ?>" alt="Profile Image" class="profile-image" id="profilePreview" style="width: 120px; height: 120px; border-radius: 50%; border: 2px solid #ddd; object-fit: cover;">
        </label>
        <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;" onchange="previewImage(event)">
    </div>

    <!-- Name -->
    <div class="form-group" style="margin-bottom: 15px;">
        <label for="name" style="font-weight: bold; color: #333;">Name</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_data['user_name']); ?>" required oninput="validateName()" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        <small class="error-message" id="nameError" style="display: none; color: red;">Name must be at least 6 characters long.</small>
    </div>

    <!-- Email -->
    <div class="form-group" style="margin-bottom: 15px;">
        <label for="email" style="font-weight: bold; color: #333;">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['user_email']); ?>" required oninput="validateEmail()" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        <small class="error-message" id="emailError" style="display: none; color: red;">Please enter a valid email (must include '@' and '.').</small>
    </div>

    <!-- Contact Number -->
    <div class="form-group" style="margin-bottom: 15px;">
        <label for="contact" style="font-weight: bold; color: #333;">Contact Number</label>
        <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($user_data['user_contact_number']); ?>" required oninput="validateContact()" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        <small class="error-message" id="contactError" style="display: none; color: red;">Format must be xxx-xxxxxxxx or xxx-xxxxxxx.</small>
    </div>

    <!-- Gender -->
    <div class="form-group" style="margin-bottom: 15px;">
        <label for="gender" style="font-weight: bold; color: #333;">Gender</label>
        <select id="gender" name="gender" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            <option value="male" <?php if ($user_data['user_gender'] === 'male') echo 'selected'; ?>>Male</option>
            <option value="female" <?php if ($user_data['user_gender'] === 'female') echo 'selected'; ?>>Female</option>
            <option value="other" <?php if ($user_data['user_gender'] === 'other') echo 'selected'; ?>>Other</option>
        </select>
    </div>

    <!-- Address -->
    <div class="form-group" style="margin-bottom: 15px;">
        <label for="address" style="font-weight: bold; color: #333;">Address</label>
        <?php
        $address_result = mysqli_query($connect, "SELECT * FROM user_address WHERE user_id ='$user_id'");
        $user_address = '';

        if ($address_result && mysqli_num_rows($address_result) > 0) {
            $address_data = mysqli_fetch_assoc($address_result);
            $user_address = htmlspecialchars($address_data['address'] . ", " . $address_data['city'] . ", " . $address_data['state'] . ", " . $address_data['postcode']);
        }
        ?>
        <input type="text" id="address" name="address" value="<?php echo $user_address; ?>" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background-color: #f0f0f0; cursor: not-allowed;">
        <!-- Buttons to Add/Edit Address -->
        <a href="add_address.php?id=<?php echo $user_id; ?>" class="edit-button" style="text-decoration: none;">
            <button type="button" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Add Address</button>
        </a>
        <a href="change_address.php?id=<?php echo $user_id; ?>" class="edit-button" style="text-decoration: none;">
            <button type="button" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">Edit Address</button>
        </a>
    </div>

    <!-- Password -->
    <div class="form-group" style="margin-bottom: 15px;">
        <label for="password" style="font-weight: bold; color: #333;">Password</label>
        <a href="verify_password.php" class="edit-button" style="text-decoration: none;">
            <button type="button" style="padding: 10px 20px; background-color: #ffc107; color: white; border: none; border-radius: 4px; cursor: pointer;">Change Password</button>
        </a>
    </div>

    <!-- Date of Birth -->
    <div class="form-group" style="margin-bottom: 15px;">
        <label for="dob" style="font-weight: bold; color: #333;">Date of Birth</label>
        <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($user_data['user_date_of_birth']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
    </div>

    <!-- Submit Button -->
    <input type="submit" name="submitbtn" value="Save Changes" class="submit-btn" style="width: 100%; padding: 12px 0; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">
</form>


<script>
    // Image preview function
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('profilePreview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }

	function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.querySelector('.eye-icon');
    
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeIcon.innerHTML = '<i class="fas fa-eye-slash"></i>'; // Change icon to eye-slash
        eyeIcon.classList.add('clicked');
    } else {
        passwordInput.type = "password";
        eyeIcon.innerHTML = '<i class="fas fa-eye"></i>'; // Change icon back to eye
        eyeIcon.classList.remove('clicked');
    }
}

    function validateName() {
        const nameField = document.getElementById('name');
        const nameError = document.getElementById('nameError');
        const isValid = nameField.value.length >= 6;

        nameError.style.display = isValid ? 'none' : 'block';
        return isValid;
    }

    function validateEmail() {
        const emailField = document.getElementById('email');
        const emailError = document.getElementById('emailError');
        const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value);

        emailError.style.display = isValid ? 'none' : 'block';
        return isValid;
    }

    function validatePassword() {
        const passwordField = document.getElementById('password');
        const passwordError = document.getElementById('passwordError');
        const password = passwordField.value;

        const hasUppercase = /[A-Z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        const isValidLength = password.length >= 8;

        const isValid = hasUppercase && hasNumber && hasSpecialChar && isValidLength;
        passwordError.style.display = isValid ? 'none' : 'block';
        return isValid;
    }

    function validateContact() {
        const contactField = document.getElementById('contact');
        const contactError = document.getElementById('contactError');
        const contactPattern = /^\d{3}-\d{7,8}$/;

        const isValid = contactPattern.test(contactField.value);
        contactError.style.display = isValid ? 'none' : 'block';
        return isValid;
    }

    function validateForm() {
        const isNameValid = validateName();
        const isEmailValid = validateEmail();
        const isPasswordValid = validatePassword();
        const isContactValid = validateContact();

        if (!isNameValid) {
            document.getElementById('name').focus();
            return false;
        }
        if (!isEmailValid) {
            document.getElementById('email').focus();
            return false;
        }
        if (!isPasswordValid) {
            document.getElementById('password').focus();
            return false;
        }
        if (!isContactValid) {
            document.getElementById('contact').focus();
            return false;
        }

        return true;
    }
</script>
	
		

	<!-- Footer -->
	<footer class="bg3 p-t-75 p-b-32">
		<div class="container">
			<div class="row">
				<div class="col-sm-6 col-lg-3 p-b-50">
					<h4 class="stext-301 cl0 p-b-30">
						Categories
					</h4>

					<ul>
						<li class="p-b-10">
							<a href="#" class="stext-107 cl7 hov-cl1 trans-04">
								Women
							</a>
						</li>

						<li class="p-b-10">
							<a href="#" class="stext-107 cl7 hov-cl1 trans-04">
								Men
							</a>
						</li>

						<li class="p-b-10">
							<a href="#" class="stext-107 cl7 hov-cl1 trans-04">
								Shoes
							</a>
						</li>

						<li class="p-b-10">
							<a href="#" class="stext-107 cl7 hov-cl1 trans-04">
								Watches
							</a>
						</li>
					</ul>
				</div>

				<div class="col-sm-6 col-lg-3 p-b-50">
					<h4 class="stext-301 cl0 p-b-30">
						Help
					</h4>

					<ul>
						<li class="p-b-10">
							<a href="#" class="stext-107 cl7 hov-cl1 trans-04">
								Track Order
							</a>
						</li>

						<li class="p-b-10">
							<a href="#" class="stext-107 cl7 hov-cl1 trans-04">
								Returns 
							</a>
						</li>

						<li class="p-b-10">
							<a href="#" class="stext-107 cl7 hov-cl1 trans-04">
								Shipping
							</a>
						</li>

						<li class="p-b-10">
							<a href="#" class="stext-107 cl7 hov-cl1 trans-04">
								FAQs
							</a>
						</li>
					</ul>
				</div>

				<div class="col-sm-6 col-lg-3 p-b-50">
					<h4 class="stext-301 cl0 p-b-30">
						GET IN TOUCH
					</h4>

					<p class="stext-107 cl7 size-201">
						Any questions? Let us know in store at 8th floor, 379 Hudson St, New York, NY 10018 or call us on (+1) 96 716 6879
					</p>

					<div class="p-t-27">
						<a href="#" class="fs-18 cl7 hov-cl1 trans-04 m-r-16">
							<i class="fa fa-facebook"></i>
						</a>

						<a href="#" class="fs-18 cl7 hov-cl1 trans-04 m-r-16">
							<i class="fa fa-instagram"></i>
						</a>

						<a href="#" class="fs-18 cl7 hov-cl1 trans-04 m-r-16">
							<i class="fa fa-pinterest-p"></i>
						</a>
					</div>
				</div>

				<div class="col-sm-6 col-lg-3 p-b-50">
					<h4 class="stext-301 cl0 p-b-30">
						Newsletter
					</h4>

					<form>
						<div class="wrap-input1 w-full p-b-4">
							<input class="input1 bg-none plh1 stext-107 cl7" type="text" name="email" placeholder="email@example.com">
							<div class="focus-input1 trans-04"></div>
						</div>

						<div class="p-t-18">
							<button class="flex-c-m stext-101 cl0 size-103 bg1 bor1 hov-btn2 p-lr-15 trans-04">
								Subscribe
							</button>
						</div>
					</form>
				</div>
			</div>

			<div class="p-t-40">
				<div class="flex-c-m flex-w p-b-18">
					<a href="#" class="m-all-1">
						<img src="images/icons/icon-pay-01.png" alt="ICON-PAY">
					</a>

					<a href="#" class="m-all-1">
						<img src="images/icons/icon-pay-02.png" alt="ICON-PAY">
					</a>

					<a href="#" class="m-all-1">
						<img src="images/icons/icon-pay-03.png" alt="ICON-PAY">
					</a>

					<a href="#" class="m-all-1">
						<img src="images/icons/icon-pay-04.png" alt="ICON-PAY">
					</a>

					<a href="#" class="m-all-1">
						<img src="images/icons/icon-pay-05.png" alt="ICON-PAY">
					</a>
				</div>

				<p class="stext-107 cl6 txt-center">
					<!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved | Made with <i class="fa fa-heart-o" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Colorlib</a> &amp; distributed by <a href="https://themewagon.com" target="_blank">ThemeWagon</a>
<!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->

				</p>
			</div>
		</div>
	</footer>


	<!-- Back to top -->
	<div class="btn-back-to-top" id="myBtn">
		<span class="symbol-btn-back-to-top">
			<i class="zmdi zmdi-chevron-up"></i>
		</span>
	</div>

<!--===============================================================================================-->	
	<script src="vendor/jquery/jquery-3.2.1.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/animsition/js/animsition.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/bootstrap/js/popper.js"></script>
	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/select2/select2.min.js"></script>
	<script>
		$(".js-select2").each(function(){
			$(this).select2({
				minimumResultsForSearch: 20,
				dropdownParent: $(this).next('.dropDownSelect2')
			});
		})
	</script>
<!--===============================================================================================-->
	<script src="vendor/MagnificPopup/jquery.magnific-popup.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/perfect-scrollbar/perfect-scrollbar.min.js"></script>
	<script>
		$('.js-pscroll').each(function(){
			$(this).css('position','relative');
			$(this).css('overflow','hidden');
			var ps = new PerfectScrollbar(this, {
				wheelSpeed: 1,
				scrollingThreshold: 1000,
				wheelPropagation: false,
			});

			$(window).on('resize', function(){
				ps.update();
			})
		});
	</script>
<!--===============================================================================================-->
	<script src="js/main.js"></script>
	
</body>
</html>
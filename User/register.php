<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="styles.css">

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
	<link rel="stylesheet" type="text/css" href="vendor/daterangepicker/daterangepicker.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/slick/slick.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/MagnificPopup/magnific-popup.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/perfect-scrollbar/perfect-scrollbar.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" type="text/css" href="css/main.css">
<!--===============================================================================================-->


<!--eye icon-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://www.google.com/recaptcha/api.js?hl=en" async defer></script>

    
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
        body {
            font-family: Arial, sans-serif;
            background-color: white;
            margin: 0;
            padding: 0;
        }

        #registrationForm {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333333;
        }

        .field {
            margin-bottom: 15px;
            position: relative;
			display: fixed;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"],
        input[type="date"],
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .error {
            color: red;
            font-size: 0.9em;
            display: none; /* Initially hide error messages */
        }

        .gender-container {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        .gender-label {
            margin-right: 10px;
        }

	
.eye-icon {
    position: absolute;
    right: 10px; /* Space from the right edge */
    top: 50px;
    transform: translateY(-50%); /* Center vertically */
    cursor: pointer; /* Pointer cursor for the eye icon */
    font-size: 18px; /* Increase the font size */
    color: #888; /* Default color */
    transition: color 0.3s ease; /* Smooth transition */
}

.eye-icon:hover {
    color: #333; /* Darker color on hover */
}

.eye-icon i {
    font-size: 20px; /* Adjust size if necessary */
    transition: transform 0.3s ease; /* Smooth transformation */
}

.eye-icon.clicked i {
    transform: rotate(180deg); /* Flip the eye icon when clicked */
}

		input[type="password"]:focus + .eye-icon, 
		input[type="text"]:focus + .eye-icon {
			color: #28a745; /* Green color when input is focused */
		}



		input[type="password"], input[type="text"] {
			padding-right: 35px; /* Space for the eye icon */
		}

    </style>
</head>


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




                        <a href="login.php" class="flex-c-m trans-04 p-lr-25">
							Account
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

					

</div>


    <form id="registrationForm" method="POST" action="">
        <h2>Register Account</h2>
        <p style="text-align: center; margin-top: 20px;">
            Already have an account? <a href="login.php" style="color: #28a745; text-decoration: none;">Log in</a>
        </p>

        <div class="field">
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" required oninput="checkEmail()">
            <span id="emailError" class="error">Please enter a valid email (must include '@' AND '.com')</span>
        </div>

        <div class="field">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required oninput="checkName()">
            <span id="nameError" class="error">Name must be at least 6 characters long.</span>
        </div>

        <div class="field">
            <label for="contact">Contact Number:</label>
            <input type="text" id="contact" name="contact" required oninput="checkContact()">
            <span id="contactError" class="error">Format must be xxx-xxxxxxx OR xxx-xxxxxxxx</span>
        </div>

        <div class="field">
            <label>Gender:</label>
            <div class="gender-container">
                <span class="gender-label">Female</span>
                <input type="radio" name="gender" value="female" id="genderFemale" required onchange="hideGenderError()">
                <span class="gender-label">Male</span>
                <input type="radio" name="gender" value="male" id="genderMale" required onchange="hideGenderError()">
            </div>
            <span id="genderError" class="error">Please select your gender.</span>
        </div>

        <div class="field">
            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" required oninput="checkDob()">
            <span id="dobError" class="error">Please enter a valid date of birth.</span>
            <span id="dobFutureError" class="error">Date of birth cannot be in the future.</span>
        </div>

		<div class="field">
			<label for="password">Password:</label>
			<input type="password" id="password" name="password" required oninput="checkPassword()">
			<span id="passwordToggle" class="eye-icon" onclick="togglePassword('password', this)">
				<i class="fas fa-eye"></i> <!-- Visible Eye Icon -->
			</span>
			<span id="passwordError" class="error">Password must include 1 uppercase letter, 1 number, 1 special character, and be 8 characters long.</span>
		</div>

		<div class="field">
			<label for="confirmPassword">Confirm Password:</label>
			<input type="password" id="confirmPassword" name="confirmPassword" required oninput="checkConfirmPassword()">
			<span id="confirmPasswordToggle" class="eye-icon" onclick="togglePassword('confirmPassword', this)">
				<i class="fas fa-eye"></i> <!-- Visible Eye Icon -->
			</span>
			<span id="confirmPasswordError" class="error">Passwords do not match.</span>
		</div>

		<div class="form-group">
       <div class="g-recaptcha" data-sitekey="6Ld-vZAqAAAAADm1iGivIk3mWjuo2ejhIjhMan0w"></div>
       <span id="captcha_error" class="text-danger"></span>
      </div>


        <p>
            <input type="submit" name="signupbtn" value="Sign Up">
        </p>
    </form>

	<script>
    function checkEmail() {
    const email = document.getElementById("email").value;
    const emailError = document.getElementById("emailError");
    const validEmail = /.*@.*\.com$/;  // 修改为只检查包含 @ 和 .com

    emailError.style.display = validEmail.test(email) ? "none" : "block";
}


    function checkName() {
        const name = document.getElementById("name").value;
        const nameError = document.getElementById("nameError");
        nameError.style.display = name.length >= 6 ? "none" : "block";
    }

    function checkContact() {
        const contact = document.getElementById("contact").value;
        const contactError = document.getElementById("contactError");
        const validContact = /^\d{3}-\d{7,8}$/;

        contactError.style.display = validContact.test(contact) ? "none" : "block";
    }

    function hideGenderError() {
        const genderError = document.getElementById("genderError");
        genderError.style.display = "none";
    }

    function checkDob() {
        const dobInput = document.getElementById("dob");
        const dobError = document.getElementById("dobError");
        const dobFutureError = document.getElementById("dobFutureError");

        const selectedDate = new Date(dobInput.value);
        const currentDate = new Date();

        // Reset error messages
        dobError.style.display = "none";
        dobFutureError.style.display = "none";

        if (!dobInput.value) {
            dobError.style.display = "block";
            return;
        }

        // Check if selected date is in the future
        if (selectedDate > currentDate) {
            dobFutureError.style.display = "block";
        }
    }

    function checkPassword() {
        const password = document.getElementById("password").value;
        const passwordError = document.getElementById("passwordError");
        const validPassword = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/;

        passwordError.style.display = validPassword.test(password) ? "none" : "block";
    }

    function checkConfirmPassword() {
        const password = document.getElementById("password").value;
        const confirmPassword = document.getElementById("confirmPassword").value;
        const confirmPasswordError = document.getElementById("confirmPasswordError");

        confirmPasswordError.style.display = password === confirmPassword ? "none" : "block";
    }

	function togglePassword(inputId, toggleIcon) 
	{
		const passwordInput = document.getElementById(inputId);
		const inputType = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
		passwordInput.setAttribute('type', inputType);

		// Toggle eye icon
		const icon = toggleIcon.querySelector('i');
		if (inputType === 'password') {
			icon.classList.remove('fa-eye-slash');
			icon.classList.add('fa-eye');
		} else {
			icon.classList.remove('fa-eye');
			icon.classList.add('fa-eye-slash');
		}
	}


    function setMaxDate() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById("dob").setAttribute('max', today);
    }

    // Call setMaxDate when the page loads
    window.onload = setMaxDate;

    function validateForm() {
        let hasError = false;

        // Check each field and display corresponding error messages
        const emailInput = document.getElementById("email");
        const emailError = document.getElementById("emailError");
        if (!emailInput.value || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
            emailError.style.display = "block";
            hasError = true;
        }

        const nameInput = document.getElementById("name");
        const nameError = document.getElementById("nameError");
        if (!nameInput.value || nameInput.value.length < 6) {
            nameError.style.display = "block";
            hasError = true;
        }

        const contactInput = document.getElementById("contact");
        const contactError = document.getElementById("contactError");
        if (!contactInput.value || !/^\d{3}-\d{7,8}$/.test(contactInput.value)) {
            contactError.style.display = "block";
            hasError = true;
        }

        const genderError = document.getElementById("genderError");
        if (!document.querySelector('input[name="gender"]:checked')) {
            genderError.style.display = "block";
            hasError = true;
        }

        const dobInput = document.getElementById("dob");
        const dobError = document.getElementById("dobError");
        const dobFutureError = document.getElementById("dobFutureError");
        const selectedDate = new Date(dobInput.value);
        const currentDate = new Date();
        if (!dobInput.value) {
            dobError.style.display = "block";
            hasError = true;
        } else if (selectedDate > currentDate) {
            dobFutureError.style.display = "block";
            hasError = true;
        }

        const passwordInput = document.getElementById("password");
        const passwordError = document.getElementById("passwordError");
        if (!passwordInput.value || !/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/.test(passwordInput.value)) {
            passwordError.style.display = "block";
            hasError = true;
        }

        const confirmPasswordInput = document.getElementById("confirmPassword");
        const confirmPasswordError = document.getElementById("confirmPasswordError");
        if (passwordInput.value !== confirmPasswordInput.value) {
            confirmPasswordError.style.display = "block";
            hasError = true;
        }

        // If there are errors, scroll to the first one
        if (hasError) {
            scrollToFirstError();
        }

        return !hasError;
    }

    // Ensure form validation before submission
    document.getElementById("registrationForm").onsubmit = function() {
        return validateForm();
    };

    function scrollToFirstError() {
        const errors = document.querySelectorAll('.error');
        for (let error of errors) {
            if (error.style.display === 'block') {
                const field = error.previousElementSibling; // Get the associated input field
                field.focus(); // Focus on the input field
                field.style.border = '2px solid red'; // Highlight the input field with a red border
                error.scrollIntoView({ behavior: 'smooth', block: 'center' }); // Scroll to the field
                break; // Only scroll to the first error found
            }
        }
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

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

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




<?php
// Database connection
include 'dataconnection.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST["signupbtn"])) {
    // Retrieve form data
    $email = mysqli_real_escape_string($connect, $_POST["email"]); 
    $name = mysqli_real_escape_string($connect, $_POST["name"]); 
    $contact = mysqli_real_escape_string($connect, $_POST["contact"]);
    $gender = mysqli_real_escape_string($connect, $_POST["gender"]);  
    $dob = mysqli_real_escape_string($connect, $_POST["dob"]); 
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];

    $now = new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur'));
    $currentDateTime = $now->format('Y-m-d H:i:s');

    $secret_key = '6Ld-vZAqAAAAAPQuvjldDRNuWB9MDnZ78CPYRSzo';

    // Validate reCAPTCHA
    if (empty($_POST['g-recaptcha-response'])) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'CAPTCHA Required',
                    text: 'Please complete the CAPTCHA verification.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'register.php';
                });
            </script>
        </body>
        </html>";
        exit();
    }

    $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $_POST['g-recaptcha-response']);
    $response_data = json_decode($response);

    if (!$response_data->success) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Verification Failed',
                    text: 'CAPTCHA verification failed. Please try again.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'register.php';
                });
            </script>
        </body>
        </html>";
        exit();
    }

    // Check if email already exists
    $verify_email_query = mysqli_query($connect, "SELECT * FROM user WHERE user_email='$email'");
    if (mysqli_num_rows($verify_email_query) > 0) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Email Already Exists',
                    text: 'The email has already been used. Please choose another email.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'register.php';
                });
            </script>
        </body>
        </html>";
    } 
    // Check if contact number already exists
    else {
        $verify_contact_query = mysqli_query($connect, "SELECT * FROM user WHERE user_contact_number='$contact'");
        if (mysqli_num_rows($verify_contact_query) > 0) {
            echo "<!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Contact Number Already Exists',
                        text: 'The contact number has already been used. Please choose another contact number.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'register.php';
                    });
                </script>
            </body>
            </html>";
        } 
        // Proceed with registration if both email and contact are unique
        else if ($password != $confirmPassword) {
            echo "<!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Password Mismatch',
                        text: 'The password and confirm password must match.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'register.php';
                    });
                </script>
            </body>
            </html>";
        } else {
			// Insert data into the database with user_status = 0 initially
			$insert_query = mysqli_query($connect, "INSERT INTO user (user_email, user_name, user_contact_number, user_gender, user_date_of_birth, user_password, user_join_time, user_status) 
			VALUES ('$email', '$name', '$contact', '$gender', '$dob', '$password', '$currentDateTime', 0)");
			
			if ($insert_query) {
				// Update user_status to 1 after successful registration
				$update_query = mysqli_query($connect, "UPDATE user SET user_status = 1 WHERE user_email = '$email'");
				
				if ($update_query) {
					echo "<!DOCTYPE html>
					<html>
					<head>
						<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
					</head>
					<body>
						<script>
							Swal.fire({
								icon: 'success',
								title: 'Registration Successful',
								text: 'You have successfully registered.',
								confirmButtonText: 'OK'
							}).then(() => {
								window.location.href = 'login.php';
							});
						</script>
					</body>
					</html>";
				} else {
					// Handle the case where the user_status update fails
					echo "<!DOCTYPE html>
					<html>
					<head>
						<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
					</head>
					<body>
						<script>
							Swal.fire({
								icon: 'warning',
								title: 'Registration Partially Successful',
								text: 'Your registration was successful, but some settings could not be updated.',
								confirmButtonText: 'OK'
							}).then(() => {
								window.location.href = 'login.php';
							});
						</script>
					</body>
					</html>";
				}
			} else {
				echo "<!DOCTYPE html>
				<html>
				<head>
					<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
				</head>
				<body>
					<script>
						Swal.fire({
							icon: 'error',
							title: 'Registration Failed',
							text: 'Please try again later.',
							confirmButtonText: 'OK'
						}).then(() => {
							window.location.href = 'register.php';
						});
					</script>
				</body>
				</html>";
			}
		}
		
    }
}
?>

<?php
session_start();

if (isset($_POST["loginbtn"])) {
    $con = mysqli_connect('localhost', 'root', '', 'fyp', 3306);
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit();
    }
    mysqli_set_charset($con, "utf8");

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
                    window.location.href = 'login.php';
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
                    window.location.href = 'login.php';
                });
            </script>
        </body>
        </html>";
        exit();
    }

    // Sanitize inputs
    $email = trim(mysqli_real_escape_string($con, $_POST["email"]));
    $password = trim(mysqli_real_escape_string($con, $_POST["password"]));

    // Fetch user details
    $query = "SELECT * FROM user WHERE user_email = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        if ($row['user_status'] == 0) {
            // User is not active
            echo "<!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Account Inactive',
                        text: 'Your account is not active. Please contact support.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                </script>
            </body>
            </html>";
        } elseif ($password === $row['user_password']) {
            // Successful login
            $_SESSION['user_name'] = $row['user_name'];
            $_SESSION['id'] = $row['user_id'];
            echo "<!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Successful',
                        text: 'Welcome to the dashboard!',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'dashboard.php';
                    });
                </script>
            </body>
            </html>";
            exit();
        } else {
            // Invalid credentials
            echo "<!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Credentials',
                        text: 'Invalid Email or Password.',
                        confirmButtonText: 'OK'
                    });
                </script>
            </body>
            </html>";
        }
    } else {
        // No such user
        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'User Not Found',
                    text: 'No account found with this email.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'login.php';
                });
            </script>
        </body>
        </html>";
    }

    $stmt->close();
    $con->close();
}
?>


<?php

	if(empty($_POST['g-recaptcha-response']))
	{
	$captcha_error = 'Captcha is required';
	}
	else
	{
	$secret_key = '6Ld-vZAqAAAAAPQuvjldDRNuWB9MDnZ78CPYRSzo';

	$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret_key.'&response='.$_POST['g-recaptcha-response']);

	$response_data = json_decode($response);

	if(!$response_data->success)
	{
	$captcha_error = 'Captcha verification failed';
	}
	}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">

	

	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://www.google.com/recaptcha/api.js?hl=en" async defer></script>

	
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
								<a href="promotion.php">Promotion</a>
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
								<a href="contact_us.php">Contact</a>
							</li>
						</ul>
					</div>	

					

</div>




    <form id="loginForm" method="POST" action="login.php" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); max-width: 400px; margin: 100px auto; padding: 20px;">
    <h2 style="text-align: center; color: #333333;">Login</h2>
    <p style="text-align: center; margin-top: 20px;">
        Don't have an account? <a href="register.php" style="color: #28a745; text-decoration: none;">Register</a>
    </p>

    <div class="field" style="margin-bottom: 15px; position: relative;">
        <label for="email" style="display: block; margin-bottom: 5px; font-weight: bold;">Email:</label>
        <input type="text" id="email" name="email" required oninput="checkEmail()" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        <span id="emailError" class="error" style="color: red; font-size: 0.9em; display: none;">Please enter a valid email (must include '@' AND '.')</span>
    </div>

    <!-- Include Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <div class="field" style="margin-bottom: 15px; position: relative;">
        <label for="password" style="display: block; margin-bottom: 5px; font-weight: bold;">Password:</label>
        <input type="password" id="password" name="password" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; padding-right: 30px;">
        <span id="passwordToggle" class="eye-icon" onclick="togglePassword('password', this)" style="cursor: pointer; position: absolute; right: 20px; top: 55%; user-select: none; font-size: 18px; color: #007bff;">
            <i class="fas fa-eye"></i>
        </span>
        <span id="passwordError" class="error" style="color: red; font-size: 0.9em; display: none;">Please enter the correct password.</span>
    </div>

    <div class="form-group" style="margin-bottom: 15px;">
        <div class="g-recaptcha" data-sitekey="6Ld-vZAqAAAAADm1iGivIk3mWjuo2ejhIjhMan0w"></div>
        <span id="captcha_error" style="color: red; font-size: 0.9em;"></span>
    </div>

    <p>
        <input type="submit" name="loginbtn" value="Log In" style="width: 100%; padding: 10px; border: none; border-radius: 4px; background-color: #28a745; color: white; font-weight: bold; cursor: pointer;">
    </p>

    <div class="forgot-password" style="text-align: center; margin-top: 15px;">
        <p><a href="forget_password.php" style="color: #28a745; text-decoration: none;">Forgot Password?</a></p>
    </div>
</form>


    <script>




        function checkEmail() {
            const email = document.getElementById("email").value;
            const emailError = document.getElementById("emailError");
            const validEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            emailError.style.display = validEmail.test(email) ? "none" : "block";
        }

        function togglePassword(inputId, toggleIcon) 
		{
			const passwordInput = document.getElementById(inputId);
			const icon = toggleIcon.querySelector('i');
			const inputType = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
			passwordInput.setAttribute('type', inputType);
			
			// Toggle eye icon between open and closed
			if (inputType === 'password') {
				icon.classList.remove('fa-eye-slash');
				icon.classList.add('fa-eye');
			} else {
				icon.classList.remove('fa-eye');
				icon.classList.add('fa-eye-slash');
			}
		}


        function validateForm() {
            let hasError = false;

            // Check email validity
            const emailInput = document.getElementById("email");
            const emailError = document.getElementById("emailError");
            if (!emailInput.value || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
                emailError.style.display = "block";
                hasError = true;
            } else {
                emailError.style.display = "none"; // Hide error if valid
            }

            return !hasError;
        }

        // Ensure form validation before submission
        document.getElementById("loginForm").onsubmit = function() {
            return validateForm();
        };
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
							<a href="product.php?category_id=1" class="stext-107 cl7 hov-cl1 trans-04">
								Women's Bag
							</a>
						</li>

						<li class="p-b-10">
							<a href="product.php?category_id=2" class="stext-107 cl7 hov-cl1 trans-04">
								Men's Bag
							</a>
						</li>

						<li class="p-b-10">
							<a href="product.php?category_id=3" class="stext-107 cl7 hov-cl1 trans-04">
								Accessories
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
							<a href="Order.php?user=<?php echo $user_id; ?>" class="stext-107 cl7 hov-cl1 trans-04">
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
							<a href="faq.php" class="stext-107 cl7 hov-cl1 trans-04">
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





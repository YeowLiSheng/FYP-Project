<?php include "head.php" ?>
<!DOCTYPE html>

<html dir="ltr" lang="en-US" class="ready">

<head>


	<!-- BULMA -->
	<link defer href="https://techzone.com.my/catalog/view/theme/aio/stylesheet/bulma.css" rel="stylesheet"
		type="text/css" />
	<link defer href="https://techzone.com.my/catalog/view/theme/aio/plugins/bulma-extensions/bulma-checkradio.min.css"
		rel="stylesheet" type="text/css" />


	<!-- MAIN STYLESHEET -->
	<link defer href="https://techzone.com.my/catalog/view/theme/aio/stylesheet/aio.css?ver=1710050486" rel="stylesheet"
		type="text/css" />

	<!-- CAROUSEL -->
	<link defer href="https://techzone.com.my/catalog/view/theme/aio/plugins/carousel/slick.css" rel="stylesheet"
		type="text/css" />
	<link defer href="https://techzone.com.my/catalog/view/theme/aio/plugins/carousel/slick-theme.css" rel="stylesheet"
		type="text/css" />

	<!-- FONT AWESOME -->
	<link defer rel="stylesheet"
		href="https://techzone.com.my/catalog/view/theme/aio/stylesheet/fontawesome-5.6.3/css/all.min.css">

	<!-- MATERIAL DESIGN ICON -->
	<link defer rel="stylesheet"
		href="https://techzone.com.my/catalog/view/theme/aio/stylesheet/materialdesignicons-3.3.92/materialdesignicons.min.css">

	<!-- STAATLICHES -->
	<link defer rel="stylesheet"
		href="https://techzone.com.my/catalog/view/theme/aio/stylesheet/staatliches/staatliches.css">

	<!-- ANIMATION -->
	<link defer href="https://techzone.com.my/catalog/view/theme/aio/stylesheet/animate.css" rel="stylesheet"
		type="text/css" />

	<!-- BULMA CALENDAR -->
	<link defer href="https://techzone.com.my/catalog/view/theme/aio/plugins/bulma-calendar/bulma-calendar.min.css"
		rel="stylesheet" type="text/css" />

	<!-- JQUERY -->
	<script src="catalog/view/theme/aio/js/jquery-3.3.1.min.js"></script>
	<script defer type="text/javascript" src="catalog/view/javascript/jquery/ui/external/jquery.cookie.js"></script>

	<!-- TOTAL STORAGE -->
	<script defer type="text/javascript"
		src="https://techzone.com.my/catalog/view/javascript/jquery/jquery.total-storage.min.js"></script>

	<!-- FANCYBOX -->
	<link defer rel="stylesheet"
		href="https://techzone.com.my/catalog/view/theme/aio/plugins/fancybox3/jquery.fancybox.min.css">
	<script async
		src="https://techzone.com.my/catalog/view/theme/aio/plugins/fancybox3/jquery.fancybox.min.js"></script>

	<!-- ELEVATEZOOM -->
	<script async
		src="https://techzone.com.my/catalog/view/theme/aio/plugins/elevatezoom-plus/jquery.easing.min.js"></script>
	<script async
		src="https://techzone.com.my/catalog/view/theme/aio/plugins/elevatezoom-plus/jquery.mousewheel.js"></script>
	<script async
		src="https://techzone.com.my/catalog/view/theme/aio/plugins/elevatezoom-plus/jquery.ez-plus.js"></script>

	<!-- LAZYLOAD -->
	<script src="https://techzone.com.my/catalog/view/theme/aio/plugins/jquery.lazy-master/jquery.lazy.min.js"></script>
	<script
		src="https://techzone.com.my/catalog/view/theme/aio/plugins/jquery.lazy-master/jquery.lazy.plugins.min.js?ver=1.0"></script>



	<!-- GOOGLE RECAPTCHA -->
	<script defer async src='https://www.google.com/recaptcha/api.js'></script>



	<!-- CUSTOMIZE -->
	<link defer id="customize_css"
		href="https://techzone.com.my/catalog/view/multi_store/techzone/aio_customize_css.css?ver=1710050486"
		rel="stylesheet" type="text/css" />


	<style>
		.btn-wishlist,
		.btn-compare {
			display: none;
		}
	</style>


	<!-- TITLE -->
	<title>Register</title>

	<script src="https://techzone.com.my/catalog/view/javascript/fbpixel-conversion-api.js"></script>

</head>
<style>
	.newpw_require ul {
		padding: 0;
		margin: 0 0;
		list-style: none;
	}

	.newpw_require ul li {
		margin-bottom: 8px;
		color: red;
		/* font-weight: 700; */
	}

	.newpw_require ul li.active {
		display: none;
	}

	.newpw_require ul li span::before {
		display: inline;
	}

	.newpw_require ul li.active span:before {
		display: none;
	}

	.btn-wishlist,
	.btn-compare {

		display: none;
	}

	.title {
		color: black;
		text-transform: none !important;
	}

	input.button.btn-login {
		background-color: black;
	}

	a.button.view-password {
		background-color: black;
	}

	.txt-interactt {
		color: skyblue !important;
	}

	.body-style button,
	.body-style .button {
		border-radius: 0px;
		text-transform: capitalize;
		background-color: black;
	}

	.field-group .field {
		width: 100%;
	}
</style>

<body class="body-style wide  clamp-1">



	<section id="account-register" class="section container account-access">
		<div id="contents">
			<div id="main-content">
				<div class="holder">
					<div id="register">
						<div class="account-access-header">
							<div class="title">Register Account</div>
							<div class="title-message">Already have an account? <a class="txt-interactt txt-underline"
									href="user_login.php">Log in</a></div>
						</div>
						<form id="form1" name="form1" method="post" action="update_user.php">
							<input type="hidden" name="register_token" value="TVRjeE1EQXdOalF3T0E9PQ">
							<div class="form-body">
								<div class="field">
									<label class="label">Email Address *</label>
									<div class="control">
										<input type="text" class="input" id="email" name="email" value="" />
									</div>
									<div class="newpw_require">
										<ul>
											<li class="letter1"><span></span></li>
											<li class="email"><span></span></li>
											<li class="email-format"><span>Please enter a valid email address.</span></li>
										</ul>
									</div>
								</div>
								<div class="field">
									<label class="label">Name *</label>
									<div class="control">
										<input type="text" class="input" id="name" name="Name" value="" />
									</div>
									<div class="newpw_require">
										<ul>
											<li class="6_len"><span></span>6 characters</li>
										</ul>
									</div>
								</div>
								<div class="field">
									<label class="label">Contact Number *</label>
									<div class="control">
										<input type="text" class="input" id="telephone" name="telephone" value="" />
									</div>
									<div class="newpw_require">
										<ul>

											<li class="15_len2"><span></span>10 number(EXP:0XX-XXXXXXX)</li>

										</ul>
									</div>
								</div>
								<div class="field-group">
									<div class="field">
										<label class="label">
											Password *</label>
										<div class="field has-addons">
											<div class="control addon-fix">
												<input id="pw_valid" type="password" class="input " name="password"
													value="" required>
											</div>
											<div class="control">
												<a class="button view-password1">
													<span><i class="mdi mdi-eye-off"></i></span>
												</a>
											</div>
										</div>
										<script>
											document.addEventListener("DOMContentLoaded", function () {

												document.querySelector(".view-password1").addEventListener("click", function () {
													var passwordInput = document.getElementsByName("password")[0];
													var eyeIcon = document.querySelector(".view-password i");


													if (passwordInput.type === "password") {
														passwordInput.type = "text";
														eyeIcon.classList.remove("mdi-eye-off");
														eyeIcon.classList.add("mdi-eye");
													} else {
														passwordInput.type = "password";
														eyeIcon.classList.remove("mdi-eye");
														eyeIcon.classList.add("mdi-eye-off");
													}
												});
											});


										</script>
										<div class="newpw_require">
											<ul>
												<li class="letter"><span></span>1 letter</li>
												<li class="num"><span></span>1 number</li>
												<li class="special"><span></span>1 special character</li>
												<li class="14_len"><span></span>15 characters</li>
											</ul>
										</div>
									</div>
								</div>

							</div>

							<div class="myaccount-content">
								<!-- PASSWORD -->
								<div class="field">
									<label class="label">
										Comfirm Password *</label>
									<div class="field has-addons">
										<div class="control addon-fix">
											<input id="cf_pass" type="password" class="input " name="cpassword" value=""
												required>
										</div>
										<div class="control">
											<a class="button view-password3">
												<span><i class="mdi mdi-eye-off"></i></span>
											</a>
										</div>
									</div>
									<div class="newpw_require">
										<ul>

										</ul>
									</div>
								</div>

								<div class="form-footer">
									<div class="field">
										<input type="hidden" name="agree" value="1" />
										<input type="submit" class="button btn-login" id="btn_submit" name="register"
											value="Confirm " class="button" />

									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>


		<script>
			document.addEventListener("DOMContentLoaded", function () {

				document.querySelector(".view-password3").addEventListener("click", function () {
					var passwordInput = document.getElementsByName("cpassword")[0];
					var eyeIcon = document.querySelector(".view-password i");


					if (passwordInput.type === "password") {
						passwordInput.type = "text";
						eyeIcon.classList.remove("mdi-eye-off");
						eyeIcon.classList.add("mdi-eye");
					} else {
						passwordInput.type = "password";
						eyeIcon.classList.remove("mdi-eye");
						eyeIcon.classList.add("mdi-eye-off");
					}
				});
			});


		</script>

	</section>
	<script type="text/javascript"
		src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
	<script>
		function validateEmail(email) {
			var re = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
			return re.test(email);
		}

		$('#email').on('keyup', function () {

			email_value = $(this).val();
			if (email_value.match(/[a-z]/g)) {
				$('.letter1').addClass('active');
			}
			else {
				$('.letter1').removeClass('active');
			}


			if (email_value.match(/[@]/g)) {
				$('.email').addClass('active');
			}
			else {
				$('.email').removeClass('active');
			}

			if (validateEmail(email_value)) {
				$('.email-format').addClass('active');
			} else {
				$('.email-format').removeClass('active');
			}

		})

		$('#name').on('keyup', function () {

			name_value = $(this).val();
			if (name_value.length == 6 || name_value.length > 6) {
				$('.6_len').addClass('active');
			}
			else {
				$('.6_len').removeClass('active');
			}


		})



		$('#telephone').on('keyup', function () {
			telephone_value = $(this).val();
			if (telephone_value.match(/^0\d{2}-\d{7,8}$/)) {
				$('.15_len2').addClass('active');
			} else {
				$('.15_len2').removeClass('active');
			}
		});

		$('#pw_valid').on('keyup', function () {
			pw_valid_value = $(this).val();

			if (pw_valid_value.match(/[a-z]/g)) {
				$('.letter').addClass('active');
			}
			else {
				$('.letter').removeClass('active');
			}


			if (pw_valid_value.match(/[0-9]/g)) {
				$('.num').addClass('active');
			}
			else {
				$('.num').removeClass('active');
			}


			if (pw_valid_value.match(/[!@#$%^&*]/g)) {
				$('.special').addClass('active');
			}
			else {
				$('.special').removeClass('active');
			}


			if (pw_valid_value.length >= 14) {
				$('.14_len').addClass('active');
			}
			else {
				$('.14_len').removeClass('active');
			}
		})



		$('#btn_submit').on("click", function (e) {
			e.preventDefault();
			var actives = false;
			$('.newpw_require ul li').each(function () {
				if (!$(this).hasClass('active')) {
					actives = true;
					return false;
				}
			});

			if (actives) {
				$('.newpw_require ul li:not(.active)').effect("shake", { times: 2 }, 500);
			} else {
				$('form').submit();
			}
		});

	</script>
</body>

</html>


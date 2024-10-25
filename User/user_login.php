<!DOCTYPE html>
<html dir="ltr" lang="en-US" class="ready">
<head>
    <title>Account Login</title>
    <link defer href="https://www.techzone.com.my/catalog/view/theme/aio/stylesheet/bulma.css" rel="stylesheet" type="text/css" />
    <link defer href="https://www.techzone.com.my/catalog/view/theme/aio/stylesheet/aio.css?ver=1709874806" rel="stylesheet" type="text/css" />
    <script src="https://www.techzone.com.my/catalog/view/javascript/jquery/ui/external/jquery.cookie.js"></script>
    <link defer rel="stylesheet" href="https://www.techzone.com.my/catalog/view/theme/aio/plugins/fancybox3/jquery.fancybox.min.css">
    <script src="https://www.techzone.com.my/catalog/view/theme/aio/plugins/fancybox3/jquery.fancybox.min.js"></script>
</head>
<body class="body-style wide clamp-1">
    <section id="account-login" class="section container account-access">
        <div id="contents">
            <div id="main-content">
                <div class="holder">
                    <div id="login">
                        <div class="account-access-header">
                            <div class="title" style="color:black;">Log in</div>
                            <div class="title-message">New Customer? <a class="txt-interactt txt-underline" href="register.php">Register here</a></div>
                        </div>
                        <!-- LOGIN FORM -->
                        <form id="form1" name="form1" method="post" action="login.php">
                            <div class="form-body">
                                <!-- EMAIL -->
                                <div class="field">
                                    <label class="label">Email Address</label>
                                    <div class="control">
                                        <input type="text" class="input" name="email" required />
                                    </div>
                                </div>
                                <!-- PASSWORD -->
                                <div class="field">
                                    <label class="label">Password</label>
                                    <div class="field has-addons">
                                        <div class="control addon-fix">
                                            <input type="password" class="input" name="password" required />
                                        </div>
                                        <div class="control">
                                            <a class="button view-password"><i class="mdi mdi-eye-off"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- SUBMIT BUTTON -->
                            <div class="form-footer field">
                                <input type="submit" class="button btn-login" name="loginbtn" value="Login" />
                                <div class="forget-password">
                                    <a href="forgot_password.php" class="txt-interactt txt-underline">Forgot password?</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelector(".view-password").addEventListener("click", function() {
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
</body>
</html>

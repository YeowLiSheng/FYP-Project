<?php include ("dataconnection.php");
session_start();
if (isset($_SESSION['v_alert'])) {
    echo $_SESSION['v_alert'];
    unset($_SESSION['v_alert']);
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US" class="">

<head>





    <!-- BULMA -->
    <link defer href="https://www.techzone.com.my/catalog/view/theme/aio/stylesheet/bulma.css" rel="stylesheet"
        type="text/css" />
    <link defer
        href="https://www.techzone.com.my/catalog/view/theme/aio/plugins/bulma-extensions/bulma-checkradio.min.css"
        rel="stylesheet" type="text/css" />


    <!-- MAIN STYLESHEET -->
    <link defer href="https://www.techzone.com.my/catalog/view/theme/aio/stylesheet/aio.css?ver=1710120688"
        rel="stylesheet" type="text/css" />

    <!-- CAROUSEL -->
    <link defer href="https://www.techzone.com.my/catalog/view/theme/aio/plugins/carousel/slick.css" rel="stylesheet"
        type="text/css" />
    <link defer href="https://www.techzone.com.my/catalog/view/theme/aio/plugins/carousel/slick-theme.css"
        rel="stylesheet" type="text/css" />

    <!-- FONT AWESOME -->
    <link defer rel="stylesheet"
        href="https://www.techzone.com.my/catalog/view/theme/aio/stylesheet/fontawesome-5.6.3/css/all.min.css">

    <!-- MATERIAL DESIGN ICON -->
    <link defer rel="stylesheet"
        href="https://www.techzone.com.my/catalog/view/theme/aio/stylesheet/materialdesignicons-3.3.92/materialdesignicons.min.css">

    <!-- STAATLICHES -->
    <link defer rel="stylesheet"
        href="https://www.techzone.com.my/catalog/view/theme/aio/stylesheet/staatliches/staatliches.css">

    <!-- ANIMATION -->
    <link defer href="https://www.techzone.com.my/catalog/view/theme/aio/stylesheet/animate.css" rel="stylesheet"
        type="text/css" />

    <!-- BULMA CALENDAR -->
    <link defer href="https://www.techzone.com.my/catalog/view/theme/aio/plugins/bulma-calendar/bulma-calendar.min.css"
        rel="stylesheet" type="text/css" />

    <!-- JQUERY -->
    <script src="https://www.techzone.com.my/catalog/view/theme/aio/js/jquery-3.3.1.min.js"></script>
    <script defer type="text/javascript"
        src="https://www.techzone.com.my/catalog/view/javascript/jquery/ui/external/jquery.cookie.js"></script>

    <!-- TOTAL STORAGE -->
    <script defer type="text/javascript"
        src="https://www.techzone.com.my/catalog/view/javascript/jquery/jquery.total-storage.min.js"></script>

    <!-- FANCYBOX -->
    <link defer rel="stylesheet"
        href="https://www.techzone.com.my/catalog/view/theme/aio/plugins/fancybox3/jquery.fancybox.min.css">
    <script async
        src="https://www.techzone.com.my/catalog/view/theme/aio/plugins/fancybox3/jquery.fancybox.min.js"></script>

    <!-- ELEVATEZOOM -->
    <script async
        src="https://www.techzone.com.my/catalog/view/theme/aio/plugins/elevatezoom-plus/jquery.easing.min.js"></script>
    <script async
        src="https://www.techzone.com.my/catalog/view/theme/aio/plugins/elevatezoom-plus/jquery.mousewheel.js"></script>
    <script async
        src="https://www.techzone.com.my/catalog/view/theme/aio/plugins/elevatezoom-plus/jquery.ez-plus.js"></script>

    <!-- LAZYLOAD -->
    <script
        src="https://www.techzone.com.my/catalog/view/theme/aio/plugins/jquery.lazy-master/jquery.lazy.min.js"></script>
    <script
        src="https://www.techzone.com.my/catalog/view/theme/aio/plugins/jquery.lazy-master/jquery.lazy.plugins.min.js?ver=1.0"></script>



    <!-- GOOGLE RECAPTCHA -->
    <script defer async src='https://www.google.com/recaptcha/api.js'></script>



    <!-- CUSTOMIZE -->
    <link defer id="customize_css"
        href="https://www.techzone.com.my/catalog/view/multi_store/techzone/aio_customize_css.css?ver=1710120688"
        rel="stylesheet" type="text/css" />


    <style>
        .btn-wishlist,
        .btn-compare {
            display: none;
        }

        .tabs {
            -webkit-overflow-scrolling: touch;
            align-items: stretch;
            display: flex;
            font-size: 1rem;
            justify-content: space-between;
            overflow: hidden;
        }

        .body-style button,
        .body-style .button {
            border-radius: 0px;
            text-transform: capitalize;
            background-color: black;
        }

        .title,
        .tabs li.is-active a,
        .modal-card-title {
            color: black;
            text-transform: unset;
        }

        .module-product .frame,
        .tab-product .frame {
            border-radius: 0px;
            border: none;
            background-color: rgba(255, 255, 255, 1);
            vertical-align: bottom;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }

        .price .price-new {
            color:black;
        }
    </style>




    <script src="https://www.techzone.com.my/catalog/view/javascript/fbpixel-conversion-api.js"></script>

</head>

<body class="body-style wide  clamp-1">





    <div id="modal-checkout-cartmodal"></div>




    <div id="wrapper" class="clearfix">

        <!-- HEADER -->
        <div id="header" class="uni-head-1  ">
            <div class="container">
                <!-- BURGER - MAIN MENU -->
                <span id="burger-mainmenu" class="navbar-burger burger burger-mainmenu">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
                <div class="el_1">
                    <div class="holder">
                        <div id="logo" class="">
                            <a href="...">
                                <img class="header-logo" src="../image/skt.png" title="fyp" alt="fyp" />

                                <img class="mobile-header-logo" src="../image/logo.png" title="fyp" alt="fyp" />
                                <img class="sticky-logo main-logo" src="../image/logo.png" title="fyp" alt="fyp" />

                            </a>
                        </div>
                    </div>
                </div>
                <div class="el_2">
                    <div class="holder">
                        <!-- NAVIGATION BAR -->
                        <div id="navi-bar" class="navbar-menu">
                            <div class="navbar">

                                <a class="navbar-item" href="main_page.php"><span>Home</span></a>

                                <div class="navbar-item has-dropdown is-hoverable">


                                    <a class="navbar-item" href="product_list.php"><span>Products</span></a>
                                    <i class="accordion"></i>
                                </div>


                                <a class="navbar-item" href="build_home.php"><span>PCbuild</span></a>
                                <?php
                                if (isset($_SESSION['ID'])) { ?>
                                    <a class="navbar-item"
                                        href="voucher.php?ID=<?php echo $_SESSION['ID'] ?>"><span>Voucher</span></a>
                                    <?php
                                }
                                ?>
                                <?php
                                if (isset($_SESSION['ID'])) { ?>
                                    <a class="navbar-item" href="contact_us.php?ID=<?php echo $_SESSION['ID']; ?>">Contact
                                        Us</a>
                                    <?php
                                } else { ?>
                                    <a class="navbar-item" href="contact_us1.php"><span>Contact Us</span></a>
                                    <?php
                                } ?>

                                <a class="navbar-item" href="About_us.php"><span>About Us</span></a>
                            </div>
                        </div>
                        <!-- END NAVIGATION BAR -->
                    </div>
                </div>

                <!-- SEARCH -->
                <div id="search-toggle">
                    <div class="search-bar-container">
                        <a href="search.php">
                            <i class="mdi mdi-magnify"></i>
                        </a>
                    </div>
                </div>


                <!-- CURRENCY SELECTION -->
                <!-- ###AIO### -->
                <div id="currency">
                    <div class="dropdown is-right is-hoverable">
                        <div class="dropdown-trigger">
                            <a class="currency-dropdown" aria-haspopup="true">
                                <span>MYR</span>
                            </a>
                        </div>
                        <div class="dropdown-menu" role="menu">
                            <div class="dropdown-content">
                                <form action="https://www.techzone.com.my/index.php?route=module/currency" method="post"
                                    enctype="multipart/form-data" id="currency_form">
                                    <a class="dropdown-item" href="javascript:;"
                                        onclick="$('input[name=\'currency_code\']').attr('value', 'MYR'); $('#currency_form').submit();">
                                        MYR </a>
                                    <input class="s_hidden" type="hidden" name="currency_code" value="" />
                                    <input class="s_hidden" type="hidden" name="redirect"
                                        value="https://www.techzone.com.my/index.php?route=common/home" />
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SHOPPING CART -->
                <!-- ###AIO### -->

                <?php
                if (isset($_SESSION["ID"])) { ?>
                    <div id="shopping-cart">
                        <div id="checkout-cart">
                            <span class="cart-info">
                                0 item(s) RM0.00 </span>
                            <i id="clickme" class="mdi mdi-cart"></i>
                        </div>

                        <div class="checkout-overlay"></div>

                        <div class="shopping-cart-frame">
                            <div class="title">
                                <span>Cart</span>
                                <i id="btn-close-cart" onclick="closeSide()" class="mdi mdi-close"></i>
                            </div>
                            <div class="empty-cart">
                                <div>
                                    <i class="icon-empty-cart"></i>

                                </div>
                                <div>
                                    Your shopping cart is empty! </div>
                            </div>
                        </div>

                        <div class="count-frame">
                            <?php

                            $id = isset($_SESSION["ID"]) ? $_SESSION["ID"] : null;
                            if ($id) {
                                // If session ID is set, fetch cart count
                                $result = mysqli_query($connect, "SELECT * FROM cart, product WHERE cart.product_id = product.product_id AND user_id = $id AND status!='payed'");
                                $count = mysqli_num_rows($result);
                                if ($count != 0) {
                                    echo "<span class=\"shopping-cart-count \">$count</span>";
                                } else {
                                    echo "<span class=\"shopping-cart-count \">0</span>";
                                }
                            } else {
                                // If session ID is not set, display 0
                                echo "<span class=\"shopping-cart-count \">0</span>";
                            }
                            ?>

                        </div>
                        <script>
                            // Get the DOM element for the cart icon
                            var cartIcon = document.getElementById('clickme');

                            // Add an event listener for click events
                            cartIcon.addEventListener('click', function () {
                                // Redirect to the shopping_cart.php page
                                window.location.href = 'shoping_cart.php';
                            });
                        </script>


                    </div>
                <?php } ?>
                <!-- ACCOUNT -->
                <div id="myaccount">
                    <div class="dropdown is-right is-hoverable">
                        <div class="dropdown-trigger">
                            <a class="account-dropdown" aria-haspopup="true">
                                <i class="mdi mdi-account"></i>
                            </a>
                        </div>
                        <div class="dropdown-menu" role="menu">
                            <div class="dropdown-content">

                                <?php
                                if (isset($_SESSION['ID'])) {

                                    echo '<div class="dropdown-item">Hi,' . $_SESSION["user_name"] . '</div>';
                                    ?>
                                    <a class="dropdown-item" href="myaccount.php?ID=<?php echo $_SESSION['ID']; ?>">My
                                        Account</a>
                                    <a class="dropdown-item mobile" href="...">My Profile</a>
                                    <a class="dropdown-item mobile" href="...s">My Addresses</a>
                                    <a class="dropdown-item mobile" href="...">Change Password</a>
                                    <a class="dropdown-item" href="orderlist.php?ID=<?php echo $_SESSION['ID']; ?>">My
                                        Order</a>
                                    <a class="dropdown-item" href="reward_point.php?ID=<?php echo $_SESSION['ID']; ?>">My
                                        Reward Point</a>
                                    <a class="dropdown-item" href="my_voucher.php ">My
                                        Voucher</a>
                                    <a class="dropdown-item" href="Logout.php">Logout</a>
                                    <?php
                                } else { ?>
                                    <a class="dropdown-item" href="Login.php">Login</a>
                                    <a class="dropdown-item" href="register.php">Register</a>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CMS BLOCK -->

                <div id="cms_2" class="cms-block">
                    <div class="cms-content cms_2 ">
                        <div class="cms-icon">
                            <i class="fas fa-headphones-alt"></i>
                        </div>
                        <div class="cms-text">
                            <pre>03-456 7554</pre>
                        </div>
                    </div>
                </div>

                <div id="cms_3" class="cms-block">
                    <div class="cms-content cms_3 hidden">
                        <div class="cms-icon">
                            <i class="fas fa-headphones-alt"></i>
                        </div>
                        <div class="cms-text">
                            <pre>03-456 7554</pre>
                        </div>
                    </div>
                </div>
                <!-- END CMS BLOCK -->

                <div class="flex-divider-1"></div>
                <div class="flex-divider-2"></div>
            </div>
        </div>

        <!-- NOTIFICATION -->
        <div id="notification" class="modal">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <button class="delete" aria-label="close"></button>
                </header>
                <section class="modal-card-body">
                    <div class="notification-info"></div>
                </section>
                <footer class="modal-card-foot">
                    <div class="buttons">
                        <button type="button" class="button" onclick="closeModals();">Ok</button>
                    </div>
                </footer>
            </div>
        </div>


</body>

</html>
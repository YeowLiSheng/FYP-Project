<?php session_start(); ?>
<?php @include 'dataseconnection.php';?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="admin_sidebar.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>

<style>
    .sidebar-item span {
        position: relative;
        bottom: 3px;
    }


    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

::after,
::before {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

#sidebar
{
    position:fixed;
    top:60px;
    height:95%;
    overflow-y: scroll;
    -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;  /* Firefox */
}
#sidebar::-webkit-scrollbar
{
    display:none;
}

.topbar
{
    position:fixed;
    background:#fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
    width:100%;
    height:60px;
    padding:0 20px;
    display:grid;
    grid-template-columns: 7fr 1fr;
    align-items: center;
    z-index:1;
}
.topbar img
{
    width:30px;
    height:30px;
}
.user
{
    position:relative;
    width:50px;
    height:50px;
}

.user i
{
    position:absolute;
}
a {
    text-decoration: none;
}

li {
    list-style: none;
    line-height: 30px;
}

h1 {
    font-weight: 600;
    font-size: 1.5rem;
}

body {
    font-family: 'Poppins', sans-serif;
}

.wrapper {
    display: flex;
}

.main {
    min-height: 100%;
    width:100%;
    position:relative;
    top:60px;
    overflow: hidden;
    transition: all 0.35s ease-in-out;
    background-color: #fafbfe;
}

#sidebar {
    width: 70px;
    min-width: 70px;
    z-index: 100;
    transition: all .155s ease-in-out;
    background-color: #0e2238;
    display: flex;
    flex-direction: column;
}

.main
{
    margin-left:5%;
}
#sidebar.expand{
    width: 210px;
    min-width: 210px;
}

.toggle-btn{
    background-color: transparent;
    cursor: pointer;
    border: 0;
    padding: 1rem 1.5rem;
}

.sidebar-logo a
{
    cursor:context-menu;
}
.toggle-btn i {
    font-size: 1.5rem;
    color: #FFF;
}
.sidebar-logo {
    margin: auto 0;
}

.sidebar-logo a {
    color: #FFF;
    font-size: 1.15rem;
    font-weight: 600;
}

#admin_icon
{
    background-color: white;
    color:black;
    font-size: 34px;
    align-items: center;
}
#sidebar:not(.expand) .sidebar-logo,
#sidebar:not(.expand) a.sidebar-link span {
    display: none;
}

.sidebar-nav {
    padding: 2rem 0;
    flex: 1 1 auto;
}

a.sidebar-link {
    padding: .625rem 1.625rem;
    color: #FFF;
    display: block;
    font-size: 0.9rem;
    white-space: nowrap;
    border-left: 3px solid transparent;
}

.sidebar-link i {
    font-size: 1.1rem;
    margin-right: .75rem;
}

a.sidebar-link:hover {
    background-color: rgba(255, 255, 255, .075);
    border-left: 3px solid #3b7ddd;
}

.sidebar-item {
    position: relative;
}

#sidebar:not(.expand) .sidebar-item .sidebar-dropdown {
    position: absolute;
    top: 0;
    left: 70px;
    background-color: #0e2238;
    padding: 0;
    min-width: 15rem;
    display: none;
}

#sidebar:not(.expand) .sidebar-item:hover .has-dropdown+.sidebar-dropdown {
    display: block;
    max-height: 15em;
    width: 100%;
    opacity: 1;
}

#sidebar.expand .sidebar-link[data-bs-toggle="collapse"]::after {
    border: solid;
    border-width: 0 .075rem .075rem 0;
    content: "";
    display: inline-block;
    padding: 2px;
    position: absolute;
    right: 1.5rem;
    top: 1.4rem;
    transform: rotate(-135deg);
    transition: all .2s ease-out;
}

#sidebar.expand .sidebar-link[data-bs-toggle="collapse"].collapsed::after {
    transform: rotate(45deg);
    transition: all .2s ease-out;
}

.sidebar-link collapsed
{
    background-color: #fafbfe;
}
</style>

<body>
    <?php
    if (!isset($_SESSION["admin_id"])) {
        ?>
        <script>
            Swal.fire({
                position: "middle",
                icon: "warning",
                title: "You are required to login first",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
            setTimeout(function () {
                window.location.href = "admin_login.php";
            }, 2200);
        </script>
        <?php
        exit();
    }
    ?>
    <?php
    if (isset($_SESSION['title']) && $_SESSION['title'] != '') {
        ?>
        <script>
            Swal.fire({
                title: "<?php echo $_SESSION['title']; ?>",
                text: "<?php echo $_SESSION['text']; ?>",
                icon: "<?php echo $_SESSION['icon']; ?>"
            });
        </script>
        <?php
        unset($_SESSION['img']);
        unset($_SESSION['title']);
        unset($_SESSION['text']);
        unset($_SESSION['icon']);
    }
    ?>
    <div class="wrapper">
        <div class="topbar">
            <div class="logo">
                <img src="../User/images/YLS2.jpg" style="width:110px; height:60px;">
            </div>
            <div class="user">
                <div class="dropdown">
                    <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                        data-bs-toggle="dropdown" aria-expanded="false"
                        style="background:none; color:black;border-radius:20px;">
                        <ion-icon name="apps-outline" style="font-size:110%; position:relative; top:3.5px;"></ion-icon>
                        <!-- <img src="image/<//?php echo $_SESSION['pic']; ?>"> -->
                        <?php echo $_SESSION['admin_id']; ?>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                        <li><a class="dropdown-item" href="admin_edit_profile.php?staff_id=<?php echo $_SESSION['id']; ?>">Edit
                                Profile</a></li>
                        <li><a class="dropdown-item" href="admin_log_out.php">Logout</a></li>
                    </ul>
                </div>
            </div><!-- topbar-->

            <aside id="sidebar" class="sidebar">
                <div class="d-flex">
                    <button class="toggle-btn" type="button">
                        <i class="lni lni-grid-alt"></i>
                    </button>
                    <div class="sidebar-logo">
                        <a href="admin_landing.php">Admin</a>
                    </div>
                </div>

                <!-- <ul class="sidebar-nav">
                <li class="sidebar-item">
                <a href="#" class="sidebar-link" id="admin">
                        <img src= "image/< ? php echo $_SESSION['pic'];?>" style="margin-right: 0 0 10px 10px;">
                         < ? php echo $ _SES SION['admin_id']; ? > -->
                <!-- </a>
                </li>
                <hr> -->
                <li class="sidebar-item">
                    <a href="admin_dashboard.php" class="sidebar-link" id="admin">
                        <i class="lni lni-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="view_admin.php" class="sidebar-link" id="admin">
                        <i class="lni lni-user"></i>
                        <span>Staff</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="view_customer.php" class="sidebar-link">
                        <i class="lni lni-users"></i>
                        <span>User</span>
                    </a>
                </li>



                <li class="sidebar-item">
                    <a href="admin_b.php" class="sidebar-link">
                        <i class="lni lni-bootstrap"></i>
                        <span>Brand</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="admin_faq.php" class="sidebar-link">
                        <i class="fas fa-question-circle"></i>
                        <span>FAQ</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="admin_product.php" class="sidebar-link">
                        <i class="lni lni-cart-full"></i>
                        <span>Product</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="manage_order.php" class="sidebar-link">
                        <i class="lni lni-list"></i>
                        <span>Order</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="salesreport.php" class="sidebar-link">
                        <i class="lni lni-stats-up"></i>
                        <span>Sales Report</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="admin_voucher.php" class="sidebar-link">
                        <i class="lni lni-offer"></i>
                        <span>Voucher</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="admin_feedback.php" class="sidebar-link">
                        <i class="lni lni-comments"></i>
                        <span>Feedback</span>

                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="view_contact_us.php" class="sidebar-link">
                        <i class="lni lni-keyboard"></i>
                        <span>Contact Us</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="view_blog.php" class="sidebar-link">
                        <i class="lni lni-stackoverflow"></i>
                        <span>Blog</span>
                    </a>
                </li>

                </ul>
            </aside>
        </div>
</body>

</html>

<script>
    const sidebar = document.querySelector("#sidebar");

    sidebar.addEventListener("mouseover", function () {
        sidebar.classList.add("expand");
    });

    sidebar.addEventListener("mouseleave", function () {
        sidebar.classList.remove("expand");
    });



</script>
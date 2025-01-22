<?php 
    include 'dataconnection.php';
    $sql = "SELECT category_id FROM product"; 
    $result = $connect->query($sql);

    $categoryCounts = [1 => 0, 2 => 0, 3 => 0];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (isset($categoryCounts[$row['category_id']])) {
                $categoryCounts[$row['category_id']]++;
            }
        }
    } 
?>

<?php include 'admin_sidebar.php' ?>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.css" />
  
<script src="https://cdn.datatables.net/2.1.3/js/dataTables.js"></script>

<script>
    $(document).ready(function(){
        $('#myTable').DataTable();
    });
</script>
<style>
    /* Styling for the table headers */
    #myTable th {
        background-color: #28a745;
        color: #fff; /* White text color */
        padding: 10px; /* Add padding for better spacing */
        text-align: left; /* Align text to the left */
        font-weight: bold; /* Make the text bold */
        border-bottom: 2px solid #555; /* Add a border at the bottom */
    }

    #myTable th:hover {
        background-color: #218838;
        cursor: pointer; /* Show pointer cursor for interactivity */
    }

    .card {
        padding: 16px;
    }

    .table img {
        width: 100px;
        height: auto;
    }

    .table tr {
        background-color: grey;
    }

    .lr {
        display: flex;
    }

    .ss {
        display: flex;
        align-items: space-between;
    }

    .input {
        width: 1040px;
        height: 50px;
        border-radius: 10px;
        text-indent: 40px;
    }

   

    .ss .btn {
        float: right;
    }

    .form-control,
    .form-select,
    .form-check {
        border-width: 3px;
    }

    tr {
        cursor: pointer;
    }

    .modal-edit input,
    .modal-edit textarea,
    .modal-edit select {
        border: 1.5px solid black;
    }

    .searchbar {
        position: relative;
    }

    .magni {
        position: absolute;
        top: 10%;
        font-size: 30px;
        left: 5.7px;
    }

    #check_price,
    #check_stock,
    #check_i,
    #check_name,
    #check_type,
    #check_cate,
    #check_qv1,
    #check_qv2,
    #check_qv3,
    #check_color1,
    #check_color2,
    #check_size1,
    #check_size2,
    #check_tags,
    #check_desc 
    {
        color: red;
        font-size: 0.9em;
    }

    .top .filter {
        display: flex;
        align-items: center;
        margin-top: 20x;
    }

    .filter select {
        width: 15%;
        border: 1.5px solid black;
    }

    .filter label {
        margin: 0 10px 0 10px;
    }
    #category-boxes {
    display: flex;
    justify-content: space-around; /* Ensures even spacing between boxes */
    align-items: center;
    gap: 20px; /* Adjust space between boxes */
    padding: 20px;
}
/* 设置 Export 按钮的背景色 */
.btn-primary {
        background-color: #4CAF50 !important;
        border-color: #4CAF50 !important;
    }

    .btn-primary:hover {
        background-color: #45a049 !important;
        border-color: #45a049 !important;
    }

    .btn-group {
        background-color: #4CAF50;
        display: inline-block;
        position: relative;
    }

    /* 设置下拉菜单项颜色 */
    .dropdown-menu {
        display: none;
        position: absolute;
        background: #fff;
        border: 1px solid #dcdde1;
        border-radius: 5px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        z-index: 10;
        margin-top: 5px;
    }

    .dropdown-item {
        background-color: #4CAF50; /* 按钮颜色 */
        padding: 10px 15px;
        text-decoration: none;
        color: #fff; /* 设置文字颜色为白色 */
        cursor: pointer;
        display: block;
        transition: background-color 0.2s;
    }

    .dropdown-item:hover {
        background-color: #45a049; /* 悬停时的深绿色 */
        color: white; /* 保持文字为白色 */
    }
.category-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 150px; /* Adjust the size to make it square */
    height: 150px; /* Same as width for a square shape */
    background-color: #f4f4f4; /* Light gray background */
    border: 2px solid #ddd; /* Light border */
    border-radius: 10px; /* Rounded corners */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Adds a shadow */
    text-align: center;
    font-family: Arial, sans-serif;
    transition: transform 0.3s, box-shadow 0.3s; /* Smooth hover effect */
}

.category-box:hover {
    transform: scale(1.05); /* Slight zoom on hover */
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2); /* Enhanced shadow on hover */
}

.category-box span.icon-cat {
    font-size: 38px; /* Icon size */
    margin-bottom: 2px;
    margin-top: 10px;
    color: #333; /* Icon color */
}

.category-box span {
    font-size: 18px; /* Count font size */
    color: #555;
}

.category-box p {
    font-size: 20px; /* Category name font size */
    color: black;
    margin-top: 5px;
}

#category-container {
    text-align: center; /* Center-aligns the title and boxes */
    padding: 20px;
    border: 2px solid black;
    border-radius: 10px;
    margin-top: 40px;
}

#category-title {
    font-size: 24px; /* Adjust the size of the title */
    font-weight: bold; /* Makes the title bold */
    color: #333; /* Title color */
    margin-bottom: 20px; /* Space between title and category boxes */
    font-family: Arial, sans-serif; /* Consistent font style */
}

</style>

<script type="text/JavaScript">

function add_check() {
    event.preventDefault();
    var no_error = true;

    var i = document.p_form.img.value; // Product Image
    var n = document.p_form.product_name.value; // Product Name
    var tags = document.p_form.tags.value; // Tags (used instead of `type`)
    var d = document.p_form.desc.value; // Product Description
    var price = document.p_form.price.value; // Product Price
    var qty = document.p_form.qty.value; // Stock Quantity
    var c = document.p_form.category.value; // Category
    var color1 = document.p_form.color1.value; // First Color Option
    var size1 = document.p_form.size1.value; // First Size Option
    var quickView1 = document.p_form.quick_view1.value; // Quick View 1
    var quickView2 = document.p_form.quick_view2.value; // Quick View 2
    var quickView3 = document.p_form.quick_view3.value; // Quick View 3

    // Validate Category
    if (c == "") {
        document.getElementById("check_cate").innerHTML = "Select one category";
        no_error = false;
    } else {
        document.getElementById("check_cate").innerHTML = "";
    }

    // Validate Product Image
    if (i == "") {
        document.getElementById("check_i").innerHTML = "Product image is required";
        no_error = false;
    } else {
        document.getElementById("check_i").innerHTML = "";
    }

    // Validate Product Name (with AJAX to check uniqueness)
    function validate_name() {
        return new Promise((resolve, reject) => {
            if (n == "") {
                document.getElementById("check_name").innerHTML = "Product name is required";
                no_error = false;
                resolve();
            } else {
                $.ajax({
                    url: 'run_query.php',
                    method: 'POST',
                    data: { p_n: n },
                    success: function (response) {
                        if (response.trim() === "exists") {
                            document.getElementById("check_name").innerHTML =
                                "This product name is already taken";
                            no_error = false;
                        } else {
                            document.getElementById("check_name").innerHTML = "";
                        }
                        resolve();
                    },
                    error: function () {
                        reject();
                    }
                });
            }
        });
    }

    // Validate Tags (instead of Product Type)
    if (tags == "") {
        document.getElementById("check_tags").innerHTML = "Tags are required";
        no_error = false;
    } else {
        document.getElementById("check_tags").innerHTML = "";
    }

    // Validate Product Description
    if (d == "") {
        document.getElementById("check_desc").innerHTML =
            "Product description is required";
        no_error = false;
    } else {
        document.getElementById("check_desc").innerHTML = "";
    }

    // Validate Price
    if (price == "") {
        document.getElementById("check_price").innerHTML = "Price is required";
        no_error = false;
    } else if (isNaN(price) || price < 1) {
        document.getElementById("check_price").innerHTML =
            "Please enter a valid price (at least RM1)";
        no_error = false;
    } else {
        document.getElementById("check_price").innerHTML = "";
    }

    // Validate Stock
    if (qty == "") {
        document.getElementById("check_stock").innerHTML = "Stock is required";
        no_error = false;
    } else if (isNaN(qty) || qty < 0) {
        document.getElementById("check_stock").innerHTML =
            "Please enter a valid stock (non-negative)";
        no_error = false;
    } else {
        document.getElementById("check_stock").innerHTML = "";
    }

    // Validate Colors
    if (color1 == "") {
        document.getElementById("check_color1").innerHTML = "Color 1 is required";
        no_error = false;
    } else {
        document.getElementById("check_color1").innerHTML = "";
    }
    
    // Validate Sizes
    if (size1 == "") {
        document.getElementById("check_size1").innerHTML = "Size 1 is required";
        no_error = false;
    } else {
        document.getElementById("check_size1").innerHTML = "";
    }

    // Validate Quick View Fields
    if (quickView1 == "") {
        document.getElementById("check_qv1").innerHTML =
            "Quick View 1 is required";
        no_error = false;
    } else {
        document.getElementById("check_qv1").innerHTML = "";
    }

    if (quickView2 == "") {
        document.getElementById("check_qv2").innerHTML =
            "Quick View 2 is required";
        no_error = false;
    } else {
        document.getElementById("check_qv2").innerHTML = "";
    }

    if (quickView3 == "") {
        document.getElementById("check_qv3").innerHTML =
            "Quick View 3 is required";
        no_error = false;
    } else {
        document.getElementById("check_qv3").innerHTML = "";
    }

    if (qty == "") {
        document.getElementById("check_stock").innerHTML =
            "Stock is required";
        no_error = false;
    } else {
        document.getElementById("check_stock").innerHTML = "";
    }

    if (price == "") {
        document.getElementById("check_price").innerHTML =
            "Price is required";
        no_error = false;
    } else {
        document.getElementById("check_price").innerHTML = "";
    }

    // Submit if no errors
    validate_name()
        .then(() => {
            if (no_error) {
                document.getElementById("p_form").submit();
            }
        })
        .catch(() => {
            console.error("An error occurred during name validation.");
        });
}

</script>

<body>
    <div class="main p-3">
        <div class="head" style="display:flex;">
            <i class="lni lni-cart-full" style="font-size:50px;"></i>
            <h1 style="margin: 12px 0 0 30px;">Product</h1>
            <hr>
        </div>
        <hr>
        <div class="top">
            <form method="POST" action="" class="searchbar">
                <div class="ss">
                    <ion-icon class="magni" name="search-outline"></ion-icon>
                    <input type="text" class="input" placeholder="Search with name" name="search">
                </div>
                <?php
                // Fetch categories and product statuses
                $c = mysqli_query($connect, "SELECT * FROM category");
                $s = mysqli_query($connect, "SELECT * FROM product_status");
                ?>
                <div class="filter" style="margin-top:8px;">
                    <label>Filter1 by:</label>
                    <select class="form-select" name="filter1" aria-label="Default select example">
                        <option value="" selected>-General-</option>
                        <optgroup label="Category:">
                            <?php
                            while ($row_c = mysqli_fetch_assoc($c)) {
                                ?>
                                <option value="c_<?php echo $row_c["category_id"]; ?>"><?php echo $row_c["category_name"]; ?>
                                </option>
                                <?php
                            }
                            ?>
                        </optgroup>
                        <optgroup label="Tags:">
                            <?php
                            // Fetch distinct tags from the product table
                            $tags = mysqli_query($connect, "SELECT DISTINCT tags FROM product");
                            while ($row_tags = mysqli_fetch_assoc($tags)) {
                                ?>
                                <option value="tags_<?php echo $row_tags["tags"]; ?>"><?php echo $row_tags["tags"]; ?>
                                </option>
                                <?php
                            }
                            ?>
                        </optgroup>
                    </select>

                    <label>Filter2 by:</label>
                    <select class="form-select" name="filter2" aria-label="Default select example">
                        <option value="" selected>-General-</option>
                        <optgroup label="Color:">
                            <?php
                            // Fetch unique colors from product_variant table
                            $colors = mysqli_query($connect, "SELECT DISTINCT color FROM product_variant");
                            while ($row_colors = mysqli_fetch_assoc($colors)) {
                                ?>
                                <option value="color_<?php echo $row_colors["color"]; ?>"><?php echo $row_colors["color"]; ?>
                                </option>
                                <?php
                            }
                            ?>
                        </optgroup>
                        <optgroup label="Stock:">
                            <option value="in_stock">In stock</option>
                            <option value="out_stock">Out of stock</option>
                        </optgroup>
                        <optgroup label="Product Status:">
                            <?php
                            while ($row_s = mysqli_fetch_assoc($s)) {
                                ?>
                                <option value="status_<?php echo $row_s["p_status_id"]; ?>">
                                    <?php echo $row_s["product_status"]; ?>
                                </option>
                                <?php
                            }
                            ?>
                        </optgroup>
                    </select>

                    <label>Sort by:</label>
                    <select class="form-select" name="sort_p" aria-label="Default select example">
                        <option selected>-General-</option>
                        <option value="a">A to Z</option>
                        <option value="b">Z to A</option>
                        <option value="c">Highest price</option>
                        <option value="d">Lowest price</option>
                    </select>

                    

                    <button type="submit" name="search_product" class="btn btn-dark" style="margin-left:30px; width:110px;">Search</button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal"
                        style="background-color: #218838; margin-left:240px; height:50px;">Add Product</button>
                </div>
            </form>
            <div class="btn-group" style="background-color: #4CAF50;">

    <button type="button" class="btn-btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        Export:
    </button>
    <ul class="dropdown-menu">
        <li><button type="button" class="dropdown-item" onclick="exportPDF()">PDF</button></li>
        <li><button type="button" class="dropdown-item" onclick="exportExcel()">Excel</button></li>
    </ul>
</div>
        </div>
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
            unset($_SESSION['title']);
            unset($_SESSION['text']);
            unset($_SESSION['icon']);
        }
        ?>


        <!-- modal start-->
        <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">Add Product</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <!-- Modal body -->
                    <form id="p_form" name="p_form" action="a_product.php" method="POST">
                        <div class="modal-body">
                            <div class="row">
                                <!-- product title -->
                                <div class="col-md-12">
                                    <div class="form-group mb-4">
                                        <label for="prodcuct_title">Product:</label>
                                        <input type="text" class="form-control" name="product_name"
                                            placeholder="product name">
                                        <span id="check_name"></span>
                                    </div>
                                </div>
                                <!-- Colors -->
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="color1">Color 1:</label>
                                        <input type="text" class="form-control" id="color1" name="color1" placeholder="Primary color">
                                        <span id="check_color1"></span>
                                    </div>
                                </div>
                                <!-- Sizes -->
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="size1">Size 1:</label>
                                        <input type="text" class="form-control" id="size1" name="size1" placeholder="Primary size">
                                        <span id="check_size1"></span>
                                    </div>
                                </div>

                                <!-- Tags -->
                                <div class="col-md-12">
                                    <div class="form-group mb-4">
                                        <label for="tags">Tags:</label>
                                        <input type="text" class="form-control" id="tags" name="tags" placeholder="e.g., Fashion, Lifestyle">
                                        <span id="check_tags"></span>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group mb-4">
                                        <label class="form-label" for="customFile">Product Image</label>
                                        <input type="file" class="form-control" id="customFile" name="img" />
                                        <span id="check_i"></span>
                                    </div>
                                </div>
                                
                                <!-- Quick View -->
                                <div class="col-md-12">
                                    <div class="form-group mb-4">
                                        <label for="quick_view1">Quick View 1:</label>
                                        <input type="file" class="form-control" id="quick_view1" name="quick_view1" placeholder="Quick View Detail 1">
                                        <span id="check_qv1"></span>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="quick_view2">Quick View 2:</label>
                                        <input type="file" class="form-control" id="quick_view2" name="quick_view2" placeholder="Quick View Detail 2">
                                        <span id="check_qv2"></span>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="quick_view3">Quick View 3:</label>
                                        <input type="file" class="form-control" id="quick_view3" name="quick_view3" placeholder="Quick View Detail 3">
                                        <span id="check_qv3"></span>
                                    </div>
                                </div>

                                <!-- category -->
                                <div class="col-md-5">
                                    <div class="form-group mb-4">
                                        <label>Category:</label>
                                        <select class="form-select" id="category" aria-label="Default select example"
                                            name="cate" ></select>
                                    </div>
                                    <span id="check_cate"></span>
                                </div>

                                <script>
                                    $(document).ready(function () {
                                        // Automatically fetch categories when the modal is opened
                                        $('#myModal').on('show.bs.modal', function () {
                                            $.ajax({
                                                url: 'run_query.php', // The PHP file that returns the categories
                                                method: 'POST',
                                                data: { fetch_categories: true }, // Send the specific fetch_categories flag
                                                success: function (data) {
                                                    $('#category').html(data); // Populate the dropdown with categories
                                                },
                                                error: function (xhr, status, error) {
                                                    console.error('AJAX Error:', status, error); // Handle AJAX errors
                                                }
                                            });
                                        });
                                    });
                                </script>


                                <div class="col-md-12">
                                    <div class="form-group mb-4">
                                        <div class="form-group">
                                            <label for="exampleFormControlTextarea1">Description</label>
                                            <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"
                                                placeholder="product description" name="desc"></textarea>
                                        </div>
                                        <span id="check_desc"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label class="form-label" for="price">Price:</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text">USD</span>
                                            <input type="text" class="form-control" id="price" name="price"
                                                placeholder="00.00">
                                        </div>
                                        <span id="check_price"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label class="form-label" for="qty">Stock:</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="qty" name="qty">
                                            <span class="input-group-text">pcs</span>
                                        </div>
                                        <span id="check_stock"></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <button type="button" id="add_color_button" class="btn btn-secondary">Add Another Color</button>
                                </div>

                                <!-- Hidden section for the second color -->
                                <div id="additional_color_section" style="display:none;">
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label for="color2">Color 2:</label>
                                            <input type="text" class="form-control" id="color2" name="color2" placeholder="Second color">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label for="quick_view4">Quick View 1:</label>
                                            <input type="file" class="form-control" id="quick_view4" name="quick_view4" placeholder="Quick View Detail">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label for="quick_view5">Quick View 2:</label>
                                            <input type="file" class="form-control" id="quick_view5" name="quick_view5" placeholder="Quick View Detail">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label for="quick_view6">Quick View 3:</label>
                                            <input type="file" class="form-control" id="quick_view6" name="quick_view6" placeholder="Quick View Detail">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label for="stock2">Stock:</label>
                                            <input type="text" class="form-control" id="stock2" name="stock2" placeholder="Stock for Color 2">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="save_product">
                        <!-- Modal footer -->
                        <div class="modal-footer">
                            <button onclick="add_check()" class="btn btn-primary" name="save_product"><i
                                    class="lni lni-checkmark"></i></button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i
                                    class="lni lni-close"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- modal end-->

        <div id="category-container">
                <h2 id="category-title">Our Categories</h2>
            <div id="category-boxes">
                <div class="category-box">
                    <span class="icon-cat"> <ion-icon name="bag-handle-outline"></ion-icon></span>
                    <span id="WomenCount">0</span>
                    <p>Women Bag</p>
                </div>
                <div class="category-box">
                    <span class="icon-cat"><ion-icon name="bag-outline"></ion-icon></span>
                    <span id="MenCount">0</span>
                    <p>Men Bag</p>
                </div>
                <div class="category-box">
                    <span class="icon-cat"><ion-icon name="glasses-outline"></ion-icon></span>
                    <span id="AccessoriesCount">0</span>
                    <p>Accessories</p>
                </div>
            </div>
        </div>

        <hr>
        <?php
        $query = "SELECT 
         product.product_id, 
         product.product_name,
         product_variant.variant_id,
         product_variant.Quick_view1,
         product_variant.Quick_view2,
         product_variant.Quick_view3,
         product.product_des AS product_desc, 
         product.product_image AS image, 
         product.product_price AS price, 
         product_variant.stock AS stock,
         product_status.product_status, 
         category.category_name,
         product.tags,
         product_variant.color,
         product_variant.size
         FROM product
         JOIN category ON product.category_id = category.category_id
         JOIN product_status ON product.product_status = product_status.p_status_id
         JOIN product_variant ON product.product_id = product_variant.product_id";

        if (isset($_POST["search_product"])) {
            $search = $_POST["search"];
            $query .= " WHERE product_name LIKE '%$search%'";

            // Filter1
            $filter1 = $_POST["filter1"];
            if (!empty($filter1)) {
                $p1 = explode('_', $filter1);
                $filter_type = $p1[0];
                $filter_value = intval($p1[1]);
                if ($filter_type == 'c') { // Category filter
                    $query .= " AND product.category_id = '$filter_value'";
                } else if ($filter_type == 'tags') { // Tags filter
                    $tag = $p1[1]; // Tags are likely strings, not integers
                    $query .= " AND product.tags = '$tag'";
                }
            }

            // Filter2
            $filter2 = $_POST["filter2"];
            if (!empty($filter2)) {
                if ($filter2 == 'in_stock') {
                    $query .= " AND product.stock > 0";
                } else if ($filter2 == 'out_stock') {
                    $query .= " AND product.stock = 0";
                } else {
                    $p2 = explode('_', $filter2);
                    $filter_type = $p2[0];
                    $filter_value = $p2[1]; // String for color or status
                    if ($filter_type == 'color') { // Color filter
                        $query .= " AND (product.color1 = '$filter_value' OR product.color2 = '$filter_value')";
                    } else if ($filter_type == 'status') { // Product status filter
                        $status = intval($filter_value);
                        $query .= " AND product.product_status = $status";
                    }
                }
            }

            // Sort
            $query .= " ORDER BY product.product_status"; // Default order
            $sort_p = $_POST["sort_p"];
            if (!empty($sort_p)) {
                if ($sort_p == 'a') {
                    $query .= ", product.product_name";
                }
                if ($sort_p == 'b') {
                    $query .= ", product.product_name DESC";
                }
                if ($sort_p == 'c') {
                    $query .= ", product.price DESC";
                }
                if ($sort_p == 'd') {
                    $query .= ", product.price";
                }
            }
        } else {
            $query .= " ORDER BY product.product_status"; // Default order if no search or filters
        }

        $result = mysqli_query($connect, $query);
        $count_row = mysqli_num_rows($result);
        ?>

        <div class="card">
        <p><b>Showing <?php echo $count_row ?> results.</b></p>
        <table class="table table-striped table-hover" id="myTable">
            <thead>
                <tr>
                    <th scope="col">Product</th>
                    <th scope="col">Name</th>
                    <th scope="col">Tags</th>
                    <th scope="col">Colors</th>
                    <th scope="col">Category</th>
                    <th scope="col">Price</th>
                    <th scope="col">Stock (QTY)</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <form action="a_product.php" method="POST" id="pd">
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr>
                                <div class="modal fade" id="v<?php echo $row["product_id"]; ?>" tabindex="-1"
                                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg" style="width:40%;">
                                        <div class="modal-content">
                                            <!-- Modal Header -->
                                            <div class="modal-header">
                                                <h4 class="modal-title">View Product</h4>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <!-- Modal body -->
                                            <div class="modal-body">
                                                <div class="up">
                                                    <img src="../User/images/<?php echo $row['Quick_view1'] ?>"
                                                        style="max-height:200px; width:auto;display: block;margin-left: auto; margin-right: auto;" />
                                                    <hr>
                                                    <div class="p_info">
                                                        <div class="form-group">
                                                            <b><?php echo $row['product_name'] ?></b>
                                                            <hr><br>
                                                        </div>
                                                        <div class="lr">
                                                            <div class="v_left">
                                                                <div class="form-group mb-4">
                                                                    <label style="margin-right:25px;">
                                                                        <b>Tags</b>
                                                                    </label>
                                                                    <?php echo $row['tags'] ?>
                                                                </div>

                                                                <div class="form-group mb-4">
                                                                    <label style="margin-right:9px;">
                                                                        <b>Colors</b>
                                                                    </label>
                                                                    <?php echo $row['color'] ?>
                                                                </div>

                                                                <div class="form-group mb-4">
                                                                    <label style="margin-right:9px;">
                                                                        <b>Sizes</b>
                                                                    </label>
                                                                    <?php echo $row['size'] ?>
                                                                </div>

                                                                <div class="form-group mb-4">
                                                                    <label style="margin-right:5px;">
                                                                        <b>Category</b>
                                                                    </label>
                                                                    <?php echo str_replace("_", " ", $row['category_name']); ?>
                                                                </div>
                                                            </div>
                                                            <div class="v_right" style="margin-left:22px;">
                                                                <div class="form-group mb-4">
                                                                    <label style="margin-right:16.5px;">
                                                                        <b>Stock</b>
                                                                    </label>
                                                                    <?php echo $row['stock'] ?>
                                                                </div>

                                                                <div class="form-group mb-4">
                                                                    <label style="margin-right:19px;">
                                                                        <b>Price</b>
                                                                    </label>
                                                                    RM<?php echo $row['price'] ?>
                                                                </div>

                                                                <div class="form-group mb-4">
                                                                    <label style="margin-right:9px;">
                                                                        <b>Status</b>
                                                                    </label>
                                                                    <?php echo $row['product_status'] ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label><b>Description:</b></label><br>
                                                    <?php echo $row['product_desc'] ?>
                                                </div>
                                            </div>
                                            <!-- Modal footer -->
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-danger"
                                                    data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- modal end-->


                                <!-- First table row -->
                                    <td data-bs-toggle="modal" data-bs-target="#v<?php echo $row["variant_id"]; ?>">
                                        <img src="../User/images/<?php echo $row['Quick_view1'] ?>" style="max-height:100px; max-width:auto;" />
                                    </td>

                                    <td data-bs-toggle="modal" data-bs-target="#v<?php echo $row["product_id"]; ?>">
                                        <?php echo $row['product_name'] ?>
                                    </td>

                                    <td data-bs-toggle="modal" data-bs-target="#v<?php echo $row["product_id"]; ?>">
                                        <?php echo $row['tags'] ?>
                                    </td>

                                    <td data-bs-toggle="modal" data-bs-target="#v<?php echo $row["product_id"]; ?>">
                                        <?php echo $row['color'] ?>
                                    </td>

                                    <td data-bs-toggle="modal" data-bs-target="#v<?php echo $row["product_id"]; ?>">
                                        <?php echo str_replace("_", " ", $row['category_name']); ?>
                                    </td>

                                    <td data-bs-toggle="modal" data-bs-target="#v<?php echo $row["product_id"]; ?>">
                                        USD <?php echo $row['price'] ?>
                                    </td>

                                    <td data-bs-toggle="modal" data-bs-target="#v<?php echo $row["product_id"]; ?>">
                                        <?php echo $row['stock'] ?><br>
                                        <div style="font-size:80%; color:<?php echo ($row['stock'] < 1) ? 'red' : 'green'; ?>">
                                            <?php echo ($row['stock'] < 1) ? 'Out of Stock' : 'In Stock'; ?>
                                        </div>
                                    </td>

                                    <?php
                                    if ($row['product_status'] == "Available") { ?>
                                        <td data-bs-toggle="modal" data-bs-target="#v<?php echo $row["product_id"]; ?>" style="color:#0EAF09;">
                                            <?php echo $row['product_status'] ?>
                                        </td>
                                    <?php } else { ?>
                                        <td data-bs-toggle="modal" data-bs-target="#v<?php echo $row["product_id"]; ?>" style="color:red;">
                                            <?php echo $row['product_status'] ?>
                                        </td>
                                    <?php 
                                } 
                                ?>
                </form>                
                <td class="button-action">
                                    <!-- Validation Script -->
                                    <script>
                                        function add_check<?php echo $row['product_id'] ?>() {
                                            event.preventDefault();
                                            var no_error = true;

                                            // Input values
                                            var n = document.e_form<?php echo $row['product_id'] ?>.product_name<?php echo $row['product_id'] ?>.value;
                                            var d = document.e_form<?php echo $row['product_id'] ?>.desc<?php echo $row['product_id'] ?>.value;
                                            var price = document.e_form<?php echo $row['product_id'] ?>.price<?php echo $row['product_id'] ?>.value;
                                            var qty = document.e_form<?php echo $row['product_id'] ?>.qty<?php echo $row['product_id'] ?>.value;

                                            // Validation logic
                                            if (n == "") {
                                                document.getElementById("check_name<?php echo $row['product_id'] ?>").innerHTML = "Product name is required";
                                                no_error = false;
                                            } else {
                                                document.getElementById("check_name<?php echo $row['product_id'] ?>").innerHTML = "";
                                            }

                                            if (d == "") {
                                                document.getElementById("check_desc<?php echo $row['product_id'] ?>").innerHTML = "Product description is required";
                                                no_error = false;
                                            } else {
                                                document.getElementById("check_desc<?php echo $row['product_id'] ?>").innerHTML = "";
                                            }

                                            if (price == "") {
                                                document.getElementById("check_price<?php echo $row['product_id'] ?>").innerHTML = "Price is required";
                                                no_error = false;
                                            } else if (isNaN(price) || price < 1) {
                                                document.getElementById("check_price<?php echo $row['product_id'] ?>").innerHTML = "Enter a valid price (min RM1)";
                                                no_error = false;
                                            } else {
                                                document.getElementById("check_price<?php echo $row['product_id'] ?>").innerHTML = "";
                                            }

                                            if (qty == "") {
                                                document.getElementById("check_stock<?php echo $row['product_id'] ?>").innerHTML = "Stock is required";
                                                no_error = false;
                                            } else if (isNaN(qty) || qty < 0) {
                                                document.getElementById("check_stock<?php echo $row['product_id'] ?>").innerHTML = "Enter a valid stock (>= 0)";
                                                no_error = false;
                                            } else {
                                                document.getElementById("check_stock<?php echo $row['product_id'] ?>").innerHTML = "";
                                            }

                                            // Submit if no errors
                                            if (no_error) {
                                                document.getElementById("e_form<?php echo $row['product_id'] ?>").submit();
                                            }
                                        }
                                    </script>
                                <!-- _____________________________________EDIT__________________________________________-->
                                <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                    <button type="button" class="btn btn-dark" data-bs-toggle="modal"
                                            data-bs-target="#e<?php echo $row["variant_id"]; ?>"
                                            style="border-right: 1.25px solid white;">
                                        <i class="lni lni-pencil-alt"></i>
                                    </button>

                                    <!-- Modal for editing product variants -->
                                    <div class="modal fade modal-edit" id="e<?php echo $row["variant_id"]; ?>" tabindex="-1"
                                        aria-labelledby="exampleModalLabel" aria-hidden="true" style="border:1px solid black;">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <!-- Modal Header -->
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Edit Product Variant</h4>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <!-- Modal Body -->
                                                <form id="e_form<?php echo $row['variant_id']; ?>" name="e_form<?php echo $row['variant_id']; ?>" action="a_product.php" method="POST" enctype="multipart/form-data">
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <!-- Product Title -->
                                                            <div class="col-md-12">
                                                                <div class="form-group mb-4">
                                                                    <label for="product_title">Product Name:</label>
                                                                    <input type="text" class="form-control" name="product_name" placeholder="Enter product name" value="<?php echo $row["product_name"]; ?>">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-5">
                                                                <div class="form-group mb-4">
                                                                    <label>Category:</label>
                                                                    <select class="form-select" id="edit-category<?php echo $row["variant_id"]; ?>" name="cate">
                                                                        <?php
                                                                        $selected_cate = $row["category_name"];
                                                                        $select = mysqli_query($connect, "SELECT * FROM category where category_name = '$selected_cate'");
                                                                        while ($rowc = mysqli_fetch_assoc($select)) {
                                                                            ?>
                                                                            <option value="<?php echo $rowc['category_id']; ?>" checked>
                                                                                <?php echo $rowc['category_name']; ?>
                                                                            </option>
                                                                            <?php
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="form-group mb-4">
                                                                    <label class="form-label" for="imgInput<?php echo $row['image']; ?>">Product Image</label>
                                                                    <input type="file" class="form-control" id="imgInput<?php echo $row['image']; ?>" name="img">
                                                                    <input type="hidden" name="old-img" value="<?php echo $row['image'] ?>">
                                                                </div>
                                                            </div>

                                                            <!-- Description -->
                                                            <div class="col-md-12">
                                                                <div class="form-group mb-4">
                                                                    <label for="product_desc">Description:</label>
                                                                    <textarea class="form-control" name="desc" placeholder="Enter product description"><?php echo $row["product_desc"]; ?></textarea>
                                                                </div>
                                                            </div>

                                                            <!-- Variant Images -->
                                                            <div class="col-md-4">
                                                                <label for="Quick_View1">Quick View 1:</label>
                                                                <input type="file" class="form-control" id="imgInput<?php echo $row['Quick_view1']; ?>" name="quick_view1">
                                                                <input type="hidden" name="old-quick_view1" value="<?php echo $row['Quick_view1'] ?>">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label for="Quick_View2">Quick View 2:</label>
                                                                <input type="file" class="form-control" id="imgInput<?php echo $row['Quick_view2']; ?>" name="quick_view2">
                                                                <input type="hidden" name="old-quick_view2" value="<?php echo $row['Quick_view2'] ?>">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label for="Quick_View3">Quick View 3:</label>
                                                                <input type="file" class="form-control" id="imgInput<?php echo $row['Quick_view3']; ?>" name="quick_view3">
                                                                <input type="hidden" name="old-quick_view3" value="<?php echo $row['Quick_view3'] ?>">
                                                            </div>

                                                            <!-- Price and Stock -->
                                                            <div class="col-md-6">
                                                                <label for="product_price">Price:</label>
                                                                <div class="input-group mb-3">
                                                                    <span class="input-group-text">RM</span>
                                                                    <input type="text" class="form-control" name="price" value="<?php echo $row["price"]; ?>">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label for="product_stock">Stock:</label>
                                                                <input type="text" class="form-control" name="qty" value="<?php echo $row["stock"]; ?>">
                                                            </div>

                                                            <!-- Variant-Specific Fields (Color, Size) -->
                                                            <div class="col-md-6">
                                                                <label for="color1">Color:</label>
                                                                <input type="text" class="form-control" name="color1" value="<?php echo $row["color"]; ?>">
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label for="size1">Size:</label>
                                                                <input type="text" class="form-control" name="size1" value="<?php echo $row["size"]; ?>">
                                                            </div>

                                                            <!-- Tags -->
                                                            <div class="col-md-12">
                                                                <label for="tags">Tags:</label>
                                                                <input type="text" class="form-control" name="tags" placeholder="Enter tags (comma-separated)" value="<?php echo $row["tags"]; ?>">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Hidden Inputs for variant and product identification -->
                                                    <input type="hidden" name="product_id" value="<?php echo $row["product_id"]; ?>">
                                                    <input type="hidden" name="variant_id" value="<?php echo $row["variant_id"]; ?>">
                                                    <input type="hidden" name="edit_variant">

                                                    <!-- Modal Footer -->
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-success">Update Variant</button>
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                    <?php
                                    if ($row["product_status"] == "Available") { // 1 = Available, 0 = Unavailable
                                        ?>
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                            style="border-left: 1.25px solid white;"
                                            data-bs-target="#av<?php echo $row["product_id"]; ?>">
                                            <i class="lni lni-close"></i>
                                        </button>
                                        <?php
                                    } else if ($row["product_status"] == "Unavailable") {
                                        ?>
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                            style="border-left: 1.25px solid white;"
                                            data-bs-target="#unav<?php echo $row["product_id"]; ?>">
                                            <i class="lni lni-checkmark" style="margin-top:5px;"></i>
                                        </button>
                                        <?php
                                    }
                                    ?>


                                    <div class="modal fade" id="av<?php echo $row["product_id"]; ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">
                                                        Current status: <b style="color:#0EAF09;">Available</b>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    Set this product to status: <b style="color:red;">Unavailable</b>?<br>
                                                    <img src="../User/images/<?php echo $row["image"] ?>" alt="Product Image" class="img-fluid">
                                                    <p><?php echo $row["product_name"] ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="a_product.php?action=unavailable&product_id=<?php echo $row["product_id"]; ?>">
                                                        <button type="button" class="btn btn-primary">Yes</button>
                                                    </a>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal for setting product to available -->
                                    <div class="modal fade" id="unav<?php echo $row["product_id"]; ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">
                                                        Current status: <b style="color:red;">Unavailable</b>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    Set this product to status: <b style="color:#0EAF09;">Available</b>?<br>
                                                    <img src="../User/images/<?php echo $row["image"] ?>" alt="Product Image" class="img-fluid">
                                                    <p><?php echo $row["product_name"] ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="a_product.php?action=available&product_id=<?php echo $row["product_id"]; ?>">
                                                     <button type="button" class="btn btn-primary">Yes</button>
                                                    </a>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- end unav modal -->
                                </div>
                            </td>
                            </tr>
                            <?php
                            }
                        }
                        ?>
                </tbody>
            </table>
            <!-- <script>
                $(document).ready(function () {
                    $('input[name="search"]').on('keyup', function () {
                        var value = $(this).val();
                        $.ajax({
                            url: 'run_query.php',
                            method: 'POST',
                            data: { product: value },
                            success: function (response) {
                                $('#table-body').html(response);
                            }
                        });
                    });
                });
            </script> -->
        </div><!-- end of card-->
    </div><!-- end of main-->
</body>

<script>
    const categoryCounts = <?php echo json_encode($categoryCounts); ?>;
    document.getElementById('WomenCount').innerText = categoryCounts[1];
    document.getElementById('MenCount').innerText = categoryCounts[2];
    document.getElementById('AccessoriesCount').innerText = categoryCounts[3];
    const stt = document.querySelector('.status');
    const statusForm = document.getElementById('pd');

    let blue = true;
    stt.addEventListener('click', (pd) => {
        event.preventDefault();
        if (blue) {
            stt.style.backgroundColor = '#ffac09';
        }
        else {
            stt.style.backgroundColor = 'blue';
        }
        blue = !blue;
    })


</script>
<script>
    document.getElementById('add_color_button').addEventListener('click', function() {
        // Show the additional color section when the button is clicked
        document.getElementById('additional_color_section').style.display = 'block';
    });







    document.getElementById("export-pdf").addEventListener("click", exportPDF);
        document.getElementById("export-excel").addEventListener("click", exportExcel);

        function exportPDF() {
            window.location.href = "generate_product.php";

        }

 
        function exportExcel() {
    const wb = XLSX.utils.book_new();
    wb.Props = {
        Title: "Order List",
        Author: "YLS Atelier",
    };

    // Prepare data for the table with formatted dates
    const table = document.querySelector(".table");
    const rows = Array.from(table.querySelectorAll("tbody tr")).map(row => {
        const cells = Array.from(row.querySelectorAll("td"));
        // Format the Order Time column (index 2)
        const orderTimeIndex = 2;
        if (cells[orderTimeIndex]) {
            const rawDate = new Date(cells[orderTimeIndex].textContent.trim());
            const formattedDate = rawDate.toLocaleString("en-GB", { 
                year: 'numeric', 
                month: '2-digit', 
                day: '2-digit', 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            }).replace(",", ""); // Remove comma for proper formatting
            cells[orderTimeIndex].textContent = formattedDate;
        }
        return cells.map(cell => cell.textContent);
    });

    // Add headers
    const headers = Array.from(table.querySelectorAll("thead th")).map(header => header.textContent.trim());
    rows.unshift(headers);

    // Create worksheet from updated data
    const ws = XLSX.utils.aoa_to_sheet(rows);

    // Set column widths
    ws['!cols'] = [
        { wch: 15 }, // Order# column
        { wch: 20 }, // Customer Name column
        { wch: 25 }, // Order Time column
        { wch: 50 }, // Shipped To column
        { wch: 15 }, // Total column
        { wch: 20 }, // Order Status column
    ];

    // Append the sheet to the workbook
    XLSX.utils.book_append_sheet(wb, ws, "Orders");

    // Save the workbook
    XLSX.writeFile(wb, "Order_List.xlsx");
}

</script>
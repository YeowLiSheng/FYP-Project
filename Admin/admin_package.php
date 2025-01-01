<?php
ob_start();
include 'admin_sidebar.php';
include 'dataconnection.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle Add Package
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_package'])) {
    $packageName = mysqli_real_escape_string($connect, $_POST['package_name']);
    $packageDescription = mysqli_real_escape_string($connect, $_POST['package_description']);
    $product1 = mysqli_real_escape_string($connect, $_POST['product1']);
    $product2 = mysqli_real_escape_string($connect, $_POST['product2']);
    $product3 = !empty($_POST['product3']) ? mysqli_real_escape_string($connect, $_POST['product3']) : null;
    $packagePrice = mysqli_real_escape_string($connect, $_POST['package_price']);

    $query = "INSERT INTO product_package (package_name, package_description, product1_id, product2_id, product3_id, package_price)
              VALUES ('$packageName', '$packageDescription', '$product1', '$product2', " . ($product3 ? "'$product3'" : "NULL") . ", '$packagePrice')";

    if (!mysqli_query($connect, $query)) {
        echo "Error: " . mysqli_error($connect);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle active/deactive Package
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['deactivate_package'])) {
        $packageId = (int)$_POST['deactivate_package'];
        $query = "UPDATE product_package SET package_status = 2 WHERE package_id = $packageId";
        if (!mysqli_query($connect, $query)) {
            echo "Error: " . mysqli_error($connect);
        }
    } elseif (isset($_POST['activate_package'])) {
        $packageId = (int)$_POST['activate_package'];
        $query = "UPDATE product_package SET package_status = 1 WHERE package_id = $packageId";
        if (!mysqli_query($connect, $query)) {
            echo "Error: " . mysqli_error($connect);
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle Edit Package
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_package'])) {
    $packageId = mysqli_real_escape_string($connect, $_POST['package_id']);
    $packageName = mysqli_real_escape_string($connect, $_POST['package_name']);
    $packageDescription = mysqli_real_escape_string($connect, $_POST['package_description']);
    $product1 = mysqli_real_escape_string($connect, $_POST['product1']);
    $product2 = mysqli_real_escape_string($connect, $_POST['product2']);
    $product3 = !empty($_POST['product3']) ? mysqli_real_escape_string($connect, $_POST['product3']) : null;
    $packagePrice = mysqli_real_escape_string($connect, $_POST['package_price']);

    $query = "UPDATE product_package SET 
              package_name = '$packageName',
              package_description = '$packageDescription',
              product1_id = '$product1',
              product2_id = '$product2',
              product3_id = " . ($product3 ? "'$product3'" : "NULL") . ",
              package_price = '$packagePrice'
              WHERE package_id = '$packageId'";

    if (!mysqli_query($connect, $query)) {
        echo "Error: " . mysqli_error($connect);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<body>
    <div class="main p-3">
        <div class="head" style="display:flex;">
            <i class="lni lni-package" style="font-size:50px;"></i>
            <h1 style="margin: 12px 0 0 30px;">Product Package Management</h1>
        </div>
        <hr>

        <div class="card" style="width:100%;">
            <div class="card-head" style="margin-bottom:30px;">
                <button type="button" class="btn btn-success float-start" data-bs-toggle="modal" data-bs-target="#addPackageModal">Add Package</button>
            </div>

            <!-- Add Package Modal -->
            <div class="modal" id="addPackageModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="modal-header">
                                <h4 class="modal-title">Add Product Package</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <label>Package Name</label>
                                <input type="text" name="package_name" class="form-control" required>
                                
                                <label>Package Description</label>
                                <textarea name="package_description" class="form-control" required></textarea>
                                
                                <label>Package Image</label>
                                <input type="file" name="package_image" class="form-control" accept="image/*" required>
                                
                                <label>Product 1</label>
                                <select name="product1" class="form-control" required>
                                    <option value="">Select Product</option>
                                    <?php
                                    $products = mysqli_query($connect, "SELECT product_id, product_name FROM product");
                                    while ($product = mysqli_fetch_assoc($products)) {
                                        echo "<option value='" . $product['product_id'] . "'>" . $product['product_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                                
                                <label>Product 2</label>
                                <select name="product2" class="form-control" required>
                                    <option value="">Select Product</option>
                                    <?php
                                    $products = mysqli_query($connect, "SELECT product_id, product_name FROM product");
                                    while ($product = mysqli_fetch_assoc($products)) {
                                        echo "<option value='" . $product['product_id'] . "'>" . $product['product_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                                
                                <label>Product 3 (Optional)</label>
                                <select name="product3" class="form-control">
                                    <option value="">Select Product</option>
                                    <?php
                                    $products = mysqli_query($connect, "SELECT product_id, product_name FROM product");
                                    while ($product = mysqli_fetch_assoc($products)) {
                                        echo "<option value='" . $product['product_id'] . "'>" . $product['product_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                                
                                <label>Package Price</label>
                                <input type="text" name="package_price" class="form-control" required>
                                
                                <label>Package Stock</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-secondary" onclick="changeStock(-1)">-</button>
                                    <input type="number" name="package_stock" id="package_stock" class="form-control" value="1" min="1" required>
                                    <button type="button" class="btn btn-secondary" onclick="changeStock(1)">+</button>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="add_package" class="btn btn-primary">Add Package</button>
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Package Modal -->
            <div class="modal" id="editPackageModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" id="edit_package_id" name="package_id">
                            <div class="modal-header">
                                <h4 class="modal-title">Edit Product Package</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <label>Package Name</label>
                                <input type="text" id="edit_package_name" name="package_name" class="form-control" required>
                                
                                <label>Package Description</label>
                                <textarea id="edit_package_description" name="package_description" class="form-control" required></textarea>
                                
                                <label>Package Image</label>
                                <div>
                                    <img id="current_package_image" src="" alt="Current Image" style="width: 100px; height: 100px;">
                                    <input type="file" name="new_package_image" class="form-control" accept="image/*">
                                </div>
                                
                                <label>Package Stock</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-secondary" onclick="changeStock(-1, 'edit_package_stock')">-</button>
                                    <input type="number" id="edit_package_stock" name="package_stock" class="form-control" min="1" required>
                                    <button type="button" class="btn btn-secondary" onclick="changeStock(1, 'edit_package_stock')">+</button>
                                </div>
                                
                                <!-- Other fields remain unchanged -->
                                <label>Product 1</label>
                                <select id="edit_product1" name="product1" class="form-control" required>
                                    <option value="">Select Product</option>
                                    <?php
                                    $products = mysqli_query($connect, "SELECT product_id, product_name FROM product");
                                    while ($product = mysqli_fetch_assoc($products)) {
                                        echo "<option value='" . $product['product_id'] . "'>" . $product['product_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                                <label>Product 2</label>
                                <select id="edit_product2" name="product2" class="form-control" required>
                                    <option value="">Select Product</option>
                                    <?php
                                    $products = mysqli_query($connect, "SELECT product_id, product_name FROM product");
                                    while ($product = mysqli_fetch_assoc($products)) {
                                        echo "<option value='" . $product['product_id'] . "'>" . $product['product_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                                <label>Product 3 (Optional)</label>
                                <select id="edit_product3" name="product3" class="form-control">
                                    <option value="">Select Product</option>
                                    <?php
                                    $products = mysqli_query($connect, "SELECT product_id, product_name FROM product");
                                    while ($product = mysqli_fetch_assoc($products)) {
                                        echo "<option value='" . $product['product_id'] . "'>" . $product['product_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                                <label>Package Price</label>
                                <input type="text" id="edit_package_price" name="package_price" class="form-control" required>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="edit_package" class="btn btn-primary">Save Changes</button>
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Package ID</th>
                        <th>Package Name</th>
                        <th>Image</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM product_package";
                    $result = mysqli_query($connect, $query);
                    while ($package = mysqli_fetch_assoc($result)) {
                    ?>
                        <tr>
                            <td><?php echo $package['package_id']; ?></td>
                            <td><?php echo $package['package_name']; ?></td>
                            <td>
                                <img src="../User/images/<?php echo $package['package_image']; ?>" alt="Package Image" style="width: 100px; height: 100px;">
                            </td>
                            <td><?php echo $package['package_description']; ?></td>
                            <td><?php echo number_format($package['package_price'], 2); ?></td>
                            <td><?php echo $package['package_stock']; ?></td>
                            <td>
                                <?php echo $package['package_status'] == 1 ? "Active" : "Inactive"; ?>
                            </td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <?php if ($package['package_status'] == 1) { ?>
                                        <button type="submit" name="deactivate_package" value="<?php echo $package['package_id']; ?>" class="btn btn-warning">Deactivate</button>
                                    <?php } else { ?>
                                        <button type="submit" name="activate_package" value="<?php echo $package['package_id']; ?>" class="btn btn-success">Activate</button>
                                    <?php } ?>
                                </form>
                                <button type="button" class="btn btn-info" onclick="editPackage(
                                    <?php echo $package['package_id']; ?>,
                                    '<?php echo $package['package_name']; ?>',
                                    '<?php echo $package['package_description']; ?>',
                                    '<?php echo $package['product1_id']; ?>',
                                    '<?php echo $package['product2_id']; ?>',
                                    '<?php echo $package['product3_id']; ?>',
                                    '<?php echo $package['package_price']; ?>',
                                    '<?php echo $package['package_stock']; ?>',
                                    '<?php echo $package['package_image']; ?>'
                                )">Edit</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
<script>
    function changeStock(delta) {
        const stockInput = document.getElementById("package_stock");
        let currentValue = parseInt(stockInput.value);
        currentValue = Math.max(1, currentValue + delta); // Ensure stock value is at least 1
        stockInput.value = currentValue;
    }
</script>
<script>
    function changeStock(delta, inputId) {
        const stockInput = document.getElementById(inputId);
        let currentValue = parseInt(stockInput.value);
        currentValue = Math.max(1, currentValue + delta); // Ensure stock value is at least 1
        stockInput.value = currentValue;
    }

    function editPackage(packageId, packageName, packageDescription, product1, product2, product3, packagePrice, packageStock, packageImage) {
        document.getElementById("edit_package_id").value = packageId;
        document.getElementById("edit_package_name").value = packageName;
        document.getElementById("edit_package_description").value = packageDescription;
        document.getElementById("edit_product1").value = product1;
        document.getElementById("edit_product2").value = product2;
        document.getElementById("edit_product3").value = product3;
        document.getElementById("edit_package_price").value = packagePrice;
        document.getElementById("edit_package_stock").value = packageStock;
        document.getElementById("current_package_image").src = packageImage;
        new bootstrap.Modal(document.getElementById("editPackageModal")).show();
    }
</script>
<?php mysqli_close($connect); ?>

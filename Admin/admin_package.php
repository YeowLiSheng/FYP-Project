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

// Handle Delete Package
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_package'])) {
    $packageId = mysqli_real_escape_string($connect, $_POST['delete_package']);
    $query = "DELETE FROM product_package WHERE package_id = '$packageId'";
    if (!mysqli_query($connect, $query)) {
        echo "Error: " . mysqli_error($connect);
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

<head>
<script>
    function editPackage(packageId, packageName, packageDescription, product1, product2, product3, packagePrice) {
        document.getElementById("edit_package_id").value = packageId;
        document.getElementById("edit_package_name").value = packageName;
        document.getElementById("edit_package_description").value = packageDescription;
        document.getElementById("edit_product1").value = product1;
        document.getElementById("edit_product2").value = product2;
        document.getElementById("edit_product3").value = product3;
        document.getElementById("edit_package_price").value = packagePrice;
        new bootstrap.Modal(document.getElementById("editPackageModal")).show();
    }
</script>
</head>

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
                        <form method="POST">
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
                        <th>Package Name</th>
                        <th>Package Description</th>
                        <th>Products</th>
                        <th>Images</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $packages = mysqli_query($connect, "SELECT * FROM product_package");
                    while ($package = mysqli_fetch_assoc($packages)) {
                        $products = [];
                        $images = [];
                        if ($package['product1_id']) {
                            $p1 = mysqli_fetch_assoc(mysqli_query($connect, "SELECT product_name, product_image FROM product WHERE product_id = " . $package['product1_id']));
                            $products[] = $p1['product_name'];
                            $images[] = $p1['product_image'];
                        }
                        if ($package['product2_id']) {
                            $p2 = mysqli_fetch_assoc(mysqli_query($connect, "SELECT product_name, product_image FROM product WHERE product_id = " . $package['product2_id']));
                            $products[] = $p2['product_name'];
                            $images[] = $p2['product_image'];
                        }
                        if ($package['product3_id']) {
                            $p3 = mysqli_fetch_assoc(mysqli_query($connect, "SELECT product_name, product_image FROM product WHERE product_id = " . $package['product3_id']));
                            $products[] = $p3['product_name'];
                            $images[] = $p3['product_image'];
                        }
                        ?>
                        <tr>
                            <td><?php echo $package['package_name']; ?></td>
                            <td><?php echo $package['package_description']; ?></td>
                            <td><?php echo implode(', ', $products); ?></td>
                            <td>
                                <?php foreach ($images as $image) {
                                    echo "<img src='$image' alt='Product Image' style='width: 50px; height: 50px;'> ";
                                } ?>
                            </td>
                            <td><?php echo "$" . number_format($package['package_price'], 2); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <button type="submit" name="delete_package" value="<?php echo $package['package_id']; ?>" class="btn btn-danger">Delete</button>
                                </form>
                                <button type="button" class="btn btn-info" onclick="editPackage(
                                    <?php echo $package['package_id']; ?>,
                                    '<?php echo $package['package_name']; ?>',
                                    '<?php echo $package['package_description']; ?>',
                                    '<?php echo $package['product1_id']; ?>',
                                    '<?php echo $package['product2_id']; ?>',
                                    '<?php echo $package['product3_id']; ?>',
                                    '<?php echo $package['package_price']; ?>'
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
<?php mysqli_close($connect); ?>

<?php 
include 'admin_sidebar.php';
include 'dataconnection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['generate_voucher'])) {
        $voucher_code = trim($_POST['voucher_code']);
        $discount_rate = trim($_POST['discount_rate']);
        $usage_limit = trim($_POST['usage_limit']);
        $minimum_amount = trim($_POST['minimum_amount']);
        $voucher_des = trim($_POST['voucher_des']);
        $voucher_pic = $_FILES['voucher_pic']['name'];

        $target_dir = "../User/images/";
        $target_file = $target_dir . basename($voucher_pic);

        if (move_uploaded_file($_FILES['voucher_pic']['tmp_name'], $target_file)) {
            $query = "INSERT INTO voucher (voucher_code, discount_rate, usage_limit, minimum_amount, voucher_des, voucher_pic, voucher_status) VALUES ('$voucher_code', '$discount_rate', '$usage_limit', '$minimum_amount', '$voucher_des', '$voucher_pic', 'Active')";

            if (mysqli_query($connect, $query)) {
                echo "<script>alert('Voucher generated successfully.');</script>";
            } else {
                echo "<script>alert('Error generating voucher.');</script>";
            }
        } else {
            echo "<script>alert('Error uploading image.');</script>";
        }
    }

    if (isset($_POST['update_voucher'])) {
        $voucher_code = trim($_POST['voucher_code']);
        $discount_rate = trim($_POST['discount_rate']);
        $usage_limit = trim($_POST['usage_limit']);
        $minimum_amount = trim($_POST['minimum_amount']);
        $voucher_des = trim($_POST['voucher_des']);

        $voucher_pic = $_FILES['voucher_pic']['name'];
        $pic_update = "";

        if (!empty($voucher_pic)) {
            $target_dir = "../User/images/";
            $target_file = $target_dir . basename($voucher_pic);
            if (move_uploaded_file($_FILES['voucher_pic']['tmp_name'], $target_file)) {
                $pic_update = ", voucher_pic = '$voucher_pic'";
            }
        }

        $query = "UPDATE voucher SET discount_rate = '$discount_rate', usage_limit = '$usage_limit', minimum_amount = '$minimum_amount', voucher_des = '$voucher_des' $pic_update WHERE voucher_code = '$voucher_code'";

        if (mysqli_query($connect, $query)) {
            echo "<script>alert('Voucher updated successfully.');</script>";
        } else {
            echo "<script>alert('Error updating voucher.');</script>";
        }
    }

    if (isset($_POST['activate_voucher'])) {
        $voucher_code = trim($_POST['activate_voucher']);
        $query = "UPDATE voucher SET voucher_status = 'Active' WHERE voucher_code = '$voucher_code'";
        mysqli_query($connect, $query);
    }

    if (isset($_POST['deactivate_voucher'])) {
        $voucher_code = trim($_POST['deactivate_voucher']);
        $query = "UPDATE voucher SET voucher_status = 'Inactive' WHERE voucher_code = '$voucher_code'";
        mysqli_query($connect, $query);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <script>
        function filterTable() {
            const input = document.getElementById("searchInput").value.toLowerCase();
            const rows = document.querySelectorAll(".table tbody tr");

            rows.forEach(row => {
                const code = row.cells[1].textContent.toLowerCase();
                const desc = row.cells[5].textContent.toLowerCase();
                row.style.display = (code.includes(input) || desc.includes(input)) ? "" : "none";
            });
        }
    </script>
    <style>
        .pagination { display: flex; justify-content: center; margin-top: 20px; }
        .pagination button { margin: 0 5px; padding: 5px 10px; border: 1px solid #ccc; background-color: #fff; }
        .pagination button:hover { background-color: #007bff; color: #fff; }
    </style>
</head>
<body>
<div class="main p-3">
    <div class="dashboard-overview">
        <div class="overview-card">
            <h5>Total Vouchers</h5>
            <h3><?php echo mysqli_num_rows(mysqli_query($connect, "SELECT * FROM voucher")); ?></h3>
        </div>
        <div class="overview-card">
            <h5>Active Vouchers</h5>
            <h3><?php echo mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as count FROM voucher WHERE voucher_status = 'Active'"))['count']; ?></h3>
        </div>
        <div class="overview-card">
            <h5>Inactive Vouchers</h5>
            <h3><?php echo mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as count FROM voucher WHERE voucher_status = 'Inactive'"))['count']; ?></h3>
        </div>
    </div>

    <div class="card">
        <div class="card-head mb-3">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateModal">Generate Voucher</button>
        </div>
        <div class="modal" id="generateModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h4 class="modal-title">Generate Voucher</h4>
                            <button class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="text" name="voucher_code" placeholder="Voucher Code" class="form-control mb-3" required>
                            <input type="text" name="discount_rate" placeholder="Discount Rate (%)" class="form-control mb-3" required>
                            <input type="number" name="usage_limit" placeholder="Usage Limit" class="form-control mb-3" required>
                            <input type="text" name="minimum_amount" placeholder="Minimum Amount" class="form-control mb-3" required>
                            <textarea name="voucher_des" placeholder="Description" class="form-control mb-3"></textarea>
                            <input type="file" name="voucher_pic" class="form-control mb-3" required>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="generate_voucher" class="btn btn-primary">Generate</button>
                            <button class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <input type="text" id="searchInput" onkeyup="filterTable()" class="form-control mb-3" placeholder="Search Vouchers">
        <table class="table">
            <thead>
                <tr>
                    <th>Picture</th>
                    <th>Code</th>
                    <th>Rate</th>
                    <th>Limit</th>
                    <th>Min Amount</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM voucher";
                $result = mysqli_query($connect, $query);

                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td><img src='../User/images/" . $row['voucher_pic'] . "' style='width: 100px;'></td>";
                    echo "<td>" . $row['voucher_code'] . "</td>";
                    echo "<td>" . $row['discount_rate'] . "%</td>";
                    echo "<td>" . $row['usage_limit'] . "</td>";
                    echo "<td>" . $row['minimum_amount'] . "</td>";
                    echo "<td>" . $row['voucher_des'] . "</td>";
                    echo "<td>" . $row['voucher_status'] . "</td>";
                    echo "<td>";
                    echo "<form method='POST' style='display:inline;'>";
                    echo ($row['voucher_status'] === 'Active')
                        ? "<button name='deactivate_voucher' value='" . $row['voucher_code'] . "' class='btn btn-danger'>Deactivate</button>"
                        : "<button name='activate_voucher' value='" . $row['voucher_code'] . "' class='btn btn-success'>Activate</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

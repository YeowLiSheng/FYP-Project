<?php include 'admin_sidebar.php';
include 'dataconnection.php';
?>

<head>
<script>
    function add_check(event) {
        event.preventDefault(); // Prevent form submission
        var no_error = true;

        var vc = document.s_form.voucher_code.value.trim();
        var rt = document.s_form.discount_rate.value.trim();
        var ul = document.s_form.usage_limit.value.trim();
        var ma = document.s_form.minimum_amount.value.trim();
        var d = document.s_form.voucher_des.value.trim();
        var pic = document.s_form.voucher_pic.value.trim();

        // Voucher Code validation
        if (vc === "") {
            document.getElementById("check_vc").innerHTML = "Please enter a code";
            no_error = false;
        } else {
            document.getElementById("check_vc").innerHTML = "";
        }

        // Discount Rate validation
        if (rt === "") {
            document.getElementById("check_rt").innerHTML = "Please enter a discount rate";
            no_error = false;
        } else if (isNaN(rt) || rt <= 0) {
            document.getElementById("check_rt").innerHTML = "Discount rate must be a positive number";
            no_error = false;
        } else {
            document.getElementById("check_rt").innerHTML = "";
        }

        // Usage Limit validation
        if (ul === "") {
            document.getElementById("check_ul").innerHTML = "Please enter a usage limit";
            no_error = false;
        } else if (isNaN(ul) || ul <= 0) {
            document.getElementById("check_ul").innerHTML = "Usage limit must be a positive number";
            no_error = false;
        } else {
            document.getElementById("check_ul").innerHTML = "";
        }

        // Minimum Amount validation
        if (ma === "") {
            document.getElementById("check_ma").innerHTML = "Please enter a minimum amount";
            no_error = false;
        } else if (isNaN(ma) || ma < 0) {
            document.getElementById("check_ma").innerHTML = "Minimum amount must be a non-negative number";
            no_error = false;
        } else {
            document.getElementById("check_ma").innerHTML = "";
        }

        // Description validation
        if (d === "") {
            document.getElementById("check_des").innerHTML = "Please enter a description";
            no_error = false;
        } else {
            document.getElementById("check_des").innerHTML = "";
        }

        // Voucher Picture validation
        if (pic === "") {
            document.getElementById("check_pic").innerHTML = "Please select a voucher picture";
            no_error = false;
        } else {
            document.getElementById("check_pic").innerHTML = "";
        }

        // Submit the form if no errors are found
        if (no_error) {
            document.getElementById("s_form").submit();
        }
    }
</script>

</head>
<style>
    .card {
        padding: 20px;
    }
</style>

<body>
    <div class="main p-3">
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
        <div class="head" style="display:flex;">
            <i class="lni lni-offer" style="font-size:50px;"></i>
            <h1 style="margin: 12px 0 0 30px;">Voucher</Category:Components>
            </h1>
            <hr>
        </div>
        <hr>
        <hr>
        <div class="card" style="width:50%;">
            <div class="card-head" style="margin-bottom:30px;">
                <button type="button" class="btn btn-success float-start" data-bs-toggle="modal"
                    data-bs-target="#myModal">Generate Voucher</button>
            </div>
            <div class="modal" id="myModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title">Voucher</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <form action="a_voucher.php" method="POST" id="s_form" name="s_form">
                            <!-- Modal body -->
                            <div class="modal-body">
                                <label for="voucher_code">Voucher Code</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="voucher_code" name="voucher_code" required>
                                </div>
                                <div>
                                    <span id="check_vc" style="color:red"></span>
                                </div>

                                <label for="rate">Discount Rate</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="discount_rate" name="discount_rate">
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div>
                                    <span id="check_rt" style="color:red"></span>
                                </div>
                                
                                <label for="usage_limit">Usage Limit</label>
                                <div class="input-group mb-3">
                                    <input type="number" class="form-control" id="usage_limit" name="usage_limit" required>
                                </div>
                                <div>
                                    <span id="check_ul" style="color:red"></span>
                                </div>
                                
                                <label for="minimum_amount">Minimum Amount</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="minimum_amount" name="minimum_amount" required>
                                </div>
                                <div>
                                    <span id="check_ma" style="color:red"></span>
                                </div>
                                
                                <label for="voucher_des">Description</label>
                                <div class="input-group mb-3">
                                    <textarea class="form-control" id="voucher_des" name="voucher_des"></textarea>                                    
                                </div>
                                <div>
                                    <span id="check_des" style="color:red"></span>
                                </div>
                                
                                <label for="minimum_amount">Voucher Picture</label>
                                <div class="input-group mb-3">
                                    <input type="file" class="form-control" id="voucher_pic" name="voucher_pic" required>
                                </div>
                                <div>
                                    <span id="check_pic" style="color:red"></span>
                                </div>
                            </div>
                            <input type="hidden" name="voucher">
                            <!-- Modal footer -->
                            <div class="modal-footer">
                                <button onclick="add_check(event);" class="btn btn-primary" name="voucher">Generate</button>
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div><!--content end-->
                </div><!-- modal-dialog-centered end-->
            </div><!-- modal end-->

            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Voucher Picture</th>
                        <th scope="col">Voucher Code</th>
                        <th scope="col">Discount Rate</th>
                        <th scope="col">Usage Limit</th>
                        <th scope="col">Minimum Amount</th>
                        <th scope="col">Description</th>
                        <th scope="col">Status</th>  
                    </tr>
                </thead>
                <?php
                $s = "SELECT * FROM voucher";
                $s_run = mysqli_query($connect, $s);
                ?>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($s_run)) {
                        ?>
                        <tr>
                            <td><img src="../User/images/<?php echo $row['voucher_pic']; ?>" alt="Voucher Picture" style="width: 100px; height: auto;" /></td>
                            <td><?php echo $row["voucher_code"]; ?></td>
                            <td><?php echo $row["discount_rate"] . "%"; ?></td>
                            <td><?php echo $row["usage_limit"]; ?></td>
                            <td><?php echo "$" . number_format($row["minimum_amount"], 2); ?></td>
                            <td><?php echo $row["voucher_des"]; ?></td>
                            <td><?php echo $row["voucher_status"]; ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>

        </div><!-- end of card-->
    </div><!-- end of main-->
</body>
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
    document.addEventListener("DOMContentLoaded", function () {
    const table = document.querySelector(".table");
    const rowsPerPage = 5;
    const rows = table.querySelectorAll("tbody tr");
    const pageCount = Math.ceil(rows.length / rowsPerPage);
    const pagination = document.createElement("div");
    pagination.classList.add("pagination");

    for (let i = 1; i <= pageCount; i++) {
        const button = document.createElement("button");
        button.innerHTML = i;
        button.onclick = function () {
            paginateTable(i);
        };
        pagination.appendChild(button);
    }
    table.parentNode.appendChild(pagination);
    paginateTable(1);

    function paginateTable(page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        rows.forEach((row, index) => {
            row.style.display = index >= start && index < end ? "" : "none";
        });
    }
});

document.getElementById("export-pdf").addEventListener("click", exportPDF);
        document.getElementById("export-excel").addEventListener("click", exportExcel);

        function exportPDF() {
            window.location.href = "generate_product.php";

        }

 
       function exportExcel() {
    const wb = XLSX.utils.book_new();
    wb.Props = {
        Title: "Product List",
        Author: "YLS Atelier",
    };

    // Select the table with product data
    const table = document.querySelector(".table");
    const rows = Array.from(table.querySelectorAll("tbody tr")).map(row => {
        const cells = Array.from(row.querySelectorAll("td"));
        // Exclude the first (Product Image) and last (Actions) columns
        return cells.slice(1, cells.length - 1).map(cell => cell.textContent.trim());
    });

    // Extract headers from the table, excluding the first (Product Image) and last (Actions) columns
    const headers = Array.from(table.querySelectorAll("thead th")).map((header, index) => {
        // Exclude the first and last columns
        return (index !== 0 && index !== table.querySelectorAll("thead th").length - 1) ? header.textContent.trim() : null;
    }).filter(header => header !== null);

    rows.unshift(headers);

    // Create worksheet from the extracted data
    const ws = XLSX.utils.aoa_to_sheet(rows);

    // Set appropriate column widths for the product data, excluding the first and last columns
    ws['!cols'] = [
        { wch: 30 }, // Product Name
        { wch: 25 }, // Tags
        { wch: 20 }, // Colors
        { wch: 20 }, // Category
        { wch: 15 }, // Price
        { wch: 20 }, // Stock (QTY)
        { wch: 15 }  // Status
    ];

    // Append the sheet to the workbook
    XLSX.utils.book_append_sheet(wb, ws, "Products");

    // Save the workbook as an Excel file
    XLSX.writeFile(wb, "Product_List.xlsx");
}

</script>

</head>
<style>
    .card {
        padding: 20px;
    }
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }
    .pagination button {
        margin: 0 5px;
        padding: 5px 10px;
        border: 1px solid #ccc;
        background-color: #fff;
    }
    .pagination button:hover {
        background-color: #007bff;
        color: #fff;
    }
    /* Dashboard overview styling */
    .dashboard-overview {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }

        .overview-card {
            flex: 1;
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .overview-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .overview-card h5 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .overview-card h3 {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
        }

        @media (max-width: 768px) {
            .dashboard-overview {
                flex-direction: column;
                gap: 15px;
            }
        }

  .btn-btn-warning
  {
    background-color: #28a745; /* Green background */
    color: white; /* White text */
    border: 1px solid #28a745; /* Border color matching the background */
    padding: 10px 20px; /* Padding for better button size */
    font-size: 16px; /* Font size */
    font-weight: bold; /* Bold text */
    border-radius: 5px; /* Rounded corners */
    transition: background-color 0.3s ease, transform 0.2s ease; /* Smooth transition effects */
  }

  .btn-btn-warning:hover {
   
}

.btn-btn-danger
{
    background-color: #ff4d4d; /* Red background */
    color: white; /* White text */
    border: 1px solid #28a745; /* Border color matching the background */
    padding: 10px 20px; /* Padding for better button size */
    font-size: 16px; /* Font size */
    font-weight: bold; /* Bold text */
    border-radius: 5px; /* Rounded corners */
    transition: background-color 0.3s ease, transform 0.2s ease; /* Smooth transition effects */
  }




.btn-btn-success
{
    
    background-color: #28a745; /* Green background */
    color: white; /* White text */
    border: 1px solid #28a745; /* Border color matching the background */
    padding: 10px 20px; /* Padding for better button size */
    font-size: 16px; /* Font size */
    font-weight: bold; /* Bold text */
    border-radius: 5px; /* Rounded corners */
    transition: background-color 0.3s ease, transform 0.2s ease; /* Smooth transition effects */
} 


.btn-btn-primary
{
    background-color: #28a745; /* Green background */
    color: white; /* White text */
    border: 1px solid #28a745; /* Border color matching the background */
    padding: 10px 20px; /* Padding for better button size */
    font-size: 16px; /* Font size */
    font-weight: bold; /* Bold text */
    border-radius: 5px; /* Rounded corners */
    transition: background-color 0.3s ease, transform 0.2s ease; /* Smooth transition effects */
}


.btn-btn-secondary
{
    background-color: #ff4d4d; /* Red background */
    color: white; /* White text */
    border: 1px solid #28a745; /* Border color matching the background */
    padding: 10px 20px; /* Padding for better button size */
    font-size: 16px; /* Font size */
    font-weight: bold; /* Bold text */
    border-radius: 5px; /* Rounded corners */
    transition: background-color 0.3s ease, transform 0.2s ease; /* Smooth transition effects */
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
        <!-- Dashboard Overview Section -->
        <div class="dashboard-overview">
            <div class="overview-card">
                <h5>Total Vouchers</h5>
                <h3><?php echo mysqli_num_rows(mysqli_query($connect, "SELECT * FROM voucher")); ?></h3>
            </div>
            <div class="overview-card">
                <h5>Active Vouchers</h5>
                <h3>
                    <?php
                    $active_query = "SELECT COUNT(*) as count FROM voucher WHERE voucher_status = 'Active'";
                    $active_result = mysqli_query($connect, $active_query);
                    echo mysqli_fetch_assoc($active_result)['count'];
                    ?>
                </h3>
            </div>
            <div class="overview-card">
                <h5>Inactive Vouchers</h5>
                <h3>
                    <?php
                    $inactive_query = "SELECT COUNT(*) as count FROM voucher WHERE voucher_status = 'Inactive'";
                    $inactive_result = mysqli_query($connect, $inactive_query);
                    echo mysqli_fetch_assoc($inactive_result)['count'];
                    ?>
                </h3>
            </div>
        </div>
        
        <hr>
        <div class="card" style="width:100%;">
        <div class="card-head" style="margin-bottom:30px;">
    <!-- Button Group (Export) and Generate Voucher Button -->
    <div class="d-flex justify-content-start align-items-center">
        
        <!-- Generate Voucher Button -->
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#myModal">Generate Voucher</button>
        
        <!-- Spacer (empty space between the buttons) -->
        <div class="ms-3"></div>

        <!-- Export Dropdown -->
        <div class="btn-group" style="background-color: #4CAF50;">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                Export:
            </button>
            <ul class="dropdown-menu">
                <li><button type="button" class="dropdown-item" onclick="exportPDF()">PDF</button></li>
                <li><button type="button" class="dropdown-item" onclick="exportExcel()">Excel</button></li>
            </ul>
        </div>
    </div>
</div>
            
            <div class="mb-3">
                <input type="text" id="searchInput" onkeyup="filterTable()" class="form-control" placeholder="Search Vouchers by Code or Description">
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
                                <button onclick="add_check(event);" class="btn-btn-primary" name="voucher">Generate</button>
                                <button type="button" class="btn-btn-danger" data-bs-dismiss="modal">Cancel</button>
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
                        <th scope="col">Action</th>  
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
                            <td>
                                <!-- Edit Button to Open Modal -->
                                <button type="button" class="btn-btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['voucher_code']; ?>">Edit</button>

                                <!-- Activate/Deactivate Button -->
                                <form action="a_voucher.php" method="POST" style="display:inline;">
                                    <?php if ($row["voucher_status"] === "Active") { ?>
                                        <button type="submit" name="deactivate_voucher" value="<?php echo $row['voucher_code']; ?>" class="btn-btn-danger">Deactivate</button>
                                    <?php } else { ?>
                                        <button type="submit" name="activate_voucher" value="<?php echo $row['voucher_code']; ?>" class="btn-btn-success">Activate</button>
                                    <?php } ?>
                                </form>
                            </td>
                        </tr>
                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?php echo $row['voucher_code']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <!-- Modal Header -->
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel">Edit Voucher: <?php echo $row['voucher_code']; ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <!-- Modal Body -->
                                    <form action="a_voucher.php" method="POST" enctype="multipart/form-data">
                                        <div class="modal-body">
                                            <input type="hidden" name="voucher_code" value="<?php echo $row['voucher_code']; ?>">

                                            <label for="discount_rate">Discount Rate</label>
                                            <input type="text" class="form-control" name="discount_rate" value="<?php echo $row['discount_rate']; ?>"><br>

                                            <label for="usage_limit">Usage Limit</label>
                                            <input type="number" class="form-control" name="usage_limit" value="<?php echo $row['usage_limit']; ?>"><br>

                                            <label for="minimum_amount">Minimum Amount</label>
                                            <input type="text" class="form-control" name="minimum_amount" value="<?php echo $row['minimum_amount']; ?>"><br>

                                            <label for="voucher_des">Description</label>
                                            <textarea class="form-control" name="voucher_des"><?php echo $row['voucher_des']; ?></textarea><br>

                                            <label for="voucher_pic">Voucher Picture</label>
                                            <input type="file" class="form-control" name="voucher_pic">
                                            <small>Leave empty if no change is needed</small>
                                        </div>
                                        <!-- Modal Footer -->
                                        <div class="modal-footer">
                                            <button type="submit" name="update_voucher" class="btn-btn-primary">Save Changes</button>
                                            <button type="button" class="btn-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </tbody>
            </table>

        </div><!-- end of card-->
    </div><!-- end of main-->
</body>
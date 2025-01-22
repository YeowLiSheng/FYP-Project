<?php
include 'dataconnection.php';
include 'admin_sidebar.php';

$category = "SELECT category_name FROM category";
$categoryresult=$connect->query($category);

$review = "
    SELECT 
        COALESCE(p.product_id, pp.promotion_id) AS item_id,
        CASE 
            WHEN p.product_id IS NOT NULL THEN p.product_name
            ELSE pp.promotion_name
        END AS item_name,
        CASE 
            WHEN p.product_id IS NOT NULL THEN p.product_image
            ELSE pp.promotion_image
        END AS item_image,
        c.category_name,
        COUNT(r.review_id) AS total_reviews,
        ROUND(AVG(r.rating), 1) AS avg_rating,
        MAX(r.created_at) AS latest_review
    FROM order_details od
    INNER JOIN product_variant pv ON od.variant_id = pv.variant_id
    LEFT JOIN product p ON pv.product_id = p.product_id
    LEFT JOIN promotion_product pp ON pv.promotion_id = pp.promotion_id
    LEFT JOIN category c ON (p.category_id = c.category_id OR pp.category_id = c.category_id)
    INNER JOIN reviews r ON od.detail_id = r.detail_id
    WHERE r.status = 'active'
    GROUP BY item_id, item_name, item_image, c.category_name
    ORDER BY latest_review DESC
";

    $reviewresult = $connect->query($review);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(120deg, #f5f7fa, #e4e9f0);
            margin: 0;
            padding: 0;
        }

        .main {
            margin-left: 78px;
            padding: 15px;
        }

        h1 {
            color: #2c3e50;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        h1 ion-icon {
            font-size: 32px;
            color: #3498db;
        }

        .search-container {
            margin-bottom: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-container input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid #dcdde1;
            border-radius: 5px;
            outline: none;
            font-size: 14px;
            background: white;
        }

        .search-container button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            background: #3498db;
            color: white;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .search-container button:hover {
            background: #1d6fa5;
        }

        .search-container ion-icon {
            font-size: 20px;
            color: #7f8c8d;
        }
        .btn-group {
    display: inline-block;
    position: relative;
}

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
    padding: 10px 15px;
    text-decoration: none;
    color: #2c3e50;
    cursor: pointer;
    display: block;
    transition: background-color 0.2s;
}

.dropdown-item:hover {
    background-color: #ecf0f1;
}


        .control-bar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            border-radius: 8px;
            padding: 15px 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            gap: 15px;
        }

        .control-bar .filter-group {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .control-bar select, .control-bar input {
            padding: 10px 12px;
            border: 1px solid #dcdde1;
            border-radius: 5px;
            outline: none;
            font-size: 14px;
            background: white;
            transition: all 0.3s;
        }

        .control-bar select:hover, .control-bar input:hover {
            border-color: #3498db;
        }

        .date-range {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .date-range label {
            font-size: 14px;
            color: #2c3e50;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            overflow: hidden;
            border-radius: 10px;
            margin-top: 10px;
            table-layout: fixed;
        }

        .table th, .table td {
            padding: 15px;
            text-align: center;
            border: 1px solid #dcdde1;
            word-wrap: break-word;
        }

        .table th {
            background-color: #4CAF50; /* Green background */
            color: white;
            font-weight: bold;
        }

        .table tr:hover {
            background: #ecf0f1;
        }

        .table th ion-icon {
            margin-right: 5px;
        }

        tr[onclick] {
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .control-bar {
                flex-direction: column;
                gap: 15px;
            }

            .search-container {
                flex-direction: column;
                gap: 10px;
            }

            .table th, .table td {
                padding: 10px;
                font-size: 12px;
            }
        }

        .dropdown-menu li {
            padding: 8px 10px;
        }
    </style>
</head>
<body>
    <div class="main">
        <h1>View Review</h1>
        
        <div class="search-container">
    <ion-icon name="search-outline"></ion-icon>
    <input type="text" id="search-input" placeholder="Search by name" oninput="searchTable()">

    <div class="btn-group">
    <button type="button" class="btn-btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        Export:
    </button>
    <ul class="dropdown-menu">
        <li><button type="button" class="dropdown-item" onclick="exportPDF()">PDF</button></li>
        <li><button type="button" class="dropdown-item" onclick="exportExcel()">Excel</button></li>
    </ul>
</div>
</div>

        <div class="control-bar">
            <div class="filter-group">
                <label>Filter by:</label>
                <select id="filter-status"onchange="filterTable()">
                    <option value="" selected>- General -</option>
                    <?php if ($categoryresult->num_rows > 0): ?>

                        <optgroup label="Category">
                           <?php while ($row=$categoryresult->fetch_assoc()):?>
                                <option value="<?=htmlspecialchars($row['category_name'])?>">
                                    <?= htmlspecialchars($row['category_name']) ?>


                                </option>
                            
                           <?php endwhile; ?> 
                        </optgroup>
                    <?php else: ?>
                        <optgroup label="Category">
                            <option value="">No categories available</option>
                        </optgroup>
                    <?php endif;?>    
                </select>
                <label>Sort by:</label>
                <select id="sort-order"onchange="sortTable()">
                    <option value="" selected>- General -</option>
                    <option value="newest">Newest</option>
                    <option value="oldest">Oldest</option>
                    <option value="highest">Highest Rating</option>
                    <option value="lowest">Lowest Rating</option>
                </select>
            </div>
            <div class="date-range">
                <label for="start-date">Latest From:</label>
                <input type="text" id="start-date" placeholder="Start Date">
                <label for="end-date">To:</label>
                <input type="text" id="end-date" placeholder="End Date">
            </div>
        </div>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                    <th><ion-icon name="image-outline"></ion-icon> Product Image</th>
                        <th><ion-icon name="pricetag-outline"></ion-icon> Product Name</th>
                        <th><ion-icon name="albums-outline"></ion-icon>Category</th>
                        <th><ion-icon name="chatbubble-outline"></ion-icon> Total Reviews</th>
                        <th><ion-icon name="star-half-outline"></ion-icon> Average Rating</th>
                        <th><ion-icon name="time-outline"></ion-icon> Latest Review</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                <?php    
                          if ($reviewresult->num_rows > 0) {
                            while ($row = $reviewresult->fetch_assoc()) {
                                echo "<tr onclick=\"viewReviewDetails('{$row['item_id']}')\">";
        echo "<td><img src='../User/images/{$row['item_image']}' alt='{$row['item_name']}' style='width: 50px; height: auto;'></td>";
        echo "<td>{$row['item_name']}</td>";
        echo "<td>{$row['category_name']}</td>";
        echo "<td>{$row['total_reviews']}</td>";
        echo "<td>{$row['avg_rating']}</td>";
        echo "<td>{$row['latest_review']}</td>";
        echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No reviewed products found</td></tr>";
                        }
                ?>         
                </tbody>
            </table>

            

        </div>
    </div>
    <div class="pagination" id="pagination"></div>

    <script>



        $(function () {
            $("#start-date, #end-date").datepicker({
                dateFormat: "yy-mm-dd",
                onSelect: filterByDate
            });
        });

        document.getElementById("export-pdf").addEventListener("click", exportPDF);
        document.getElementById("export-excel").addEventListener("click", exportExcel);

        function exportPDF() {
            window.location.href = "generate_review.php";

        }

 
        function exportExcel() {
    const wb = XLSX.utils.book_new();
    wb.Props = {
        Title: "Product Review List",
        Author: "YLS Atelier",
    };

    // Prepare data for the table
    const table = document.querySelector(".table");
    const rows = Array.from(table.querySelectorAll("tbody tr")).map(row => {
        const cells = Array.from(row.querySelectorAll("td"));

        // Exclude the Product Image column (index 0)
        return cells.slice(1).map(cell => cell.textContent.trim());
    });

    // Add headers excluding the Product Image column
    const headers = Array.from(table.querySelectorAll("thead th"))
        .slice(1) // Skip the Product Image header
        .map(header => header.textContent.trim());
    rows.unshift(headers);

    // Create worksheet from updated data
    const ws = XLSX.utils.aoa_to_sheet(rows);

    // Set column widths (adjust according to your needs)
    ws['!cols'] = [
        { wch: 20 }, // Product Name
        { wch: 15 }, // Category
        { wch: 15 }, // Total Reviews
        { wch: 15 }, // Average Rating
        { wch: 25 }, // Latest Review
    ];

    // Append the sheet to the workbook
    XLSX.utils.book_append_sheet(wb, ws, "Product Reviews");

    // Save the workbook
    XLSX.writeFile(wb, "Product_Review_List.xlsx");
}


        

function filterByDate() { 
    const startDate = $("#start-date").val();
    const endDate = $("#end-date").val();
    const rows = document.querySelectorAll("#table-body tr");

    rows.forEach(row => {
        const latestReviewDate = row.cells[5].textContent.trim();
        const reviewDate = latestReviewDate.split(" ")[0]; 

        const start = startDate ? new Date(startDate) : null;
        const end = endDate ? new Date(endDate) : null;
        const currentReviewDate = new Date(reviewDate);

        if ((!start || currentReviewDate >= start) && (!end || currentReviewDate <= end)) {
            row.style.display = ""; 
        } else {
            row.style.display = "none"; 
        }
    });
}

        function filterTable() { 
    const selectedCategory = document.getElementById("filter-status").value;
    const rows = document.querySelectorAll("#table-body tr");

    rows.forEach(row => {
        const productCategory = row.cells[2].textContent.trim(); // 获取产品类别
        row.style.display = (productCategory === selectedCategory || selectedCategory === "") ? "" : "none";
    });
}

function sortTable() {
    const rows = Array.from(document.querySelectorAll("#table-body tr"));
    const sortOrder = document.getElementById("sort-order").value;

    rows.sort((a, b) => {
        if (sortOrder === "newest" || sortOrder === "oldest") {

            const dateA = new Date(a.cells[5].textContent.trim());
            const dateB = new Date(b.cells[5].textContent.trim());
            return sortOrder === "newest" ? dateB - dateA : dateA - dateB;

        } else if (sortOrder === "highest" || sortOrder === "lowest") {

            const ratingA = parseFloat(a.cells[4].textContent.trim()) || 0;
            const ratingB = parseFloat(b.cells[4].textContent.trim()) || 0;
            return sortOrder === "highest" ? ratingB - ratingA : ratingA - ratingB;
        }
        return 0;
    });

    const tbody = document.getElementById("table-body");
    rows.forEach(row => tbody.appendChild(row));
}

function searchTable() { 
    const query = document.getElementById("search-input").value.toLowerCase().trim();
    const rows = document.querySelectorAll("#table-body tr");

    rows.forEach(row => {
        const name = row.cells[1].textContent.toLowerCase().trim();
        row.style.display = name.includes(query) ? "" : "none";
    });
}

        function viewReviewDetails(productId) {
            window.location.href = `adminreviewdetails.php?product_id=${productId}`;
        }


        document.addEventListener("DOMContentLoaded", function () {
    const tableBody = document.getElementById("table-body");
    const pagination = document.getElementById("pagination");

    const rowsPerPage = 10; 
    let currentPage = 1;

    const rows = Array.from(tableBody.rows); 
    const totalRows = rows.length; 


    function initPagination() {
    const totalPages = Math.ceil(totalRows / rowsPerPage);
    pagination.innerHTML = "";

    const prevButton = document.createElement("button");
    prevButton.textContent = "Previous";
    prevButton.disabled = currentPage === 1;
    prevButton.classList.add("page-btn"); 
    prevButton.addEventListener("click", () => goToPage(currentPage - 1));
    pagination.appendChild(prevButton);


    const maxPageButtons = 5;
    const halfRange = Math.floor(maxPageButtons / 2);
    const startPage = Math.max(1, currentPage - halfRange);
    const endPage = Math.min(totalPages, currentPage + halfRange);

    for (let i = startPage; i <= endPage; i++) {
        const pageButton = document.createElement("button");
        pageButton.textContent = i;
        pageButton.classList.add("page-btn");
        if (i === currentPage) pageButton.classList.add("active");
        pageButton.addEventListener("click", () => goToPage(i));
        pagination.appendChild(pageButton);
    }


    const nextButton = document.createElement("button");
    nextButton.textContent = "Next";
    nextButton.disabled = currentPage === totalPages;
    nextButton.classList.add("page-btn"); 
    nextButton.addEventListener("click", () => goToPage(currentPage + 1));
    pagination.appendChild(nextButton);
}


    function goToPage(pageNumber) {
        currentPage = Math.max(1, Math.min(pageNumber, Math.ceil(totalRows / rowsPerPage)));
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;

  
        rows.forEach((row, index) => {
            row.style.display = index >= start && index < end ? "" : "none";
        });

   
        initPagination();
    }


    goToPage(1);
});
    </script>
</body>
</html>
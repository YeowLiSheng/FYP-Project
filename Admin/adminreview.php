<?php
include 'dataconnection.php';
include 'admin_sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Review Table</title>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
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
        }

        .table th, .table td {
            padding: 15px;
            text-align: center;
            border: 1px solid #dcdde1;
        }

        .table th {
            background: #3498db;
            color: white;
            font-weight: bold;
        }

        .table tr:hover {
            background: #ecf0f1;
        }

        .product-img {
            height: 50px;
            width: 50px;
            object-fit: cover;
            border-radius: 5px;
        }

        tr[onclick] {
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .table th, .table td {
                padding: 10px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="main">
        <h1><ion-icon name="star-outline"></ion-icon> Admin Review Table</h1>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th><ion-icon name="image-outline"></ion-icon> Product Image</th>
                        <th><ion-icon name="pricetag-outline"></ion-icon> Product Name</th>
                        <th><ion-icon name="chatbubble-outline"></ion-icon> Total Reviews</th>
                        <th><ion-icon name="star-half-outline"></ion-icon> Average Rating</th>
                        <th><ion-icon name="information-circle-outline"></ion-icon> Review Status</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php
                    $query = "SELECT products.product_image, products.product_name, COUNT(reviews.review_id) AS total_reviews, 
                              AVG(reviews.rating) AS avg_rating, products.product_id
                              FROM products
                              LEFT JOIN reviews ON products.product_id = reviews.product_id
                              GROUP BY products.product_id;";

                    $result = mysqli_query($connect, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $avg_rating = number_format($row['avg_rating'], 1) ?: 'N/A';
                            $status = $row['total_reviews'] > 0 ? 'Active' : 'No Reviews';
                    ?>
                        <tr onclick="viewProductReviews('<?php echo $row['product_id']; ?>')">
                            <td><img src="<?php echo $row['product_image']; ?>" alt="Product Image" class="product-img"></td>
                            <td><?php echo $row['product_name']; ?></td>
                            <td><?php echo $row['total_reviews']; ?></td>
                            <td><?php echo $avg_rating; ?></td>
                            <td><?php echo $status; ?></td>
                        </tr>
                    <?php }
                    } else { ?>
                        <tr>
                            <td colspan="5">No products found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function viewProductReviews(productId) {
            window.location.href = `review.php?product_id=${productId}`;
        }
    </script>
</body>
</html>

<?php
session_start(); // Start the session
// Include the database connection file
include("dataconnection.php"); 

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

$product_query = "SELECT * FROM product ORDER BY product_id ASC LIMIT $limit OFFSET $offset";

$product_result = $connect->query($product_query);

if (isset($_GET['limit']) && isset($_GET['offset'])) {
      ob_start();
    // Run your existing product fetching and rendering code here
    if ($product_result->num_rows > 0) {
        while ($product = $product_result->fetch_assoc()) {
            $product_id = $product['product_id'];

            // Get total stock for the product from product_variant table
            $variant_query = "SELECT * FROM product_variant WHERE product_id = $product_id";
            $variant_result = $connect->query($variant_query);

            $total_stock = 0;
            $isOutOfStock = true;
            $colors = []; // Store available colors and their corresponding images
            
            while ($variant = $variant_result->fetch_assoc()) {
                $total_stock += intval($variant['stock']);
                if (intval($variant['stock']) > 0) {
                    $isOutOfStock = false;
                }
                $colors[] = [
                    'color' => $variant['color'],
                    'image' => $variant['Quick_View1'], // Assuming there's a column 'variant_image' for each color
                ];
            }

            $isUnavailable = $product['product_status'] == 2;
            $productStyle = $isUnavailable || $isOutOfStock ? 'unavailable-product' : '';

            $message = '';
            if ($isUnavailable) {
                $message = '<p style="color: red; font-weight: bold;">Product is unavailable</p>';
            } elseif ($isOutOfStock) {
                $message = '<p style="color: red; font-weight: bold;">Product is out of stock</p>';
            }

            echo '<div class="col-sm-6 col-md-4 col-lg-3 p-b-35 isotope-item category-' . $product['category_id'] . '" style="margin-right: -10px;">
                    <div class="block2 ' . $productStyle . '">
                        <div class="block2-pic hov-img0" >
                            <img src="images/' . $product['product_image'] . '" alt="IMG-PRODUCT" id="product-image-' . $product_id . '">
                            <a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1" 
                                data-id="' . $product['product_id'] . '"' . ($isUnavailable || $isOutOfStock ? 'style="pointer-events: none; opacity: 0.5;"' : '') . '>Quick View</a>
                        </div>
                        <div class="block2-txt flex-w flex-t p-t-14">
                            <div class="block2-txt-child1 flex-col-l ">
                                <a href="product-detail.php?id=' . $product['product_id'] . '&type=product" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6"' . ($isUnavailable || $isOutOfStock ? 'style="pointer-events: none; opacity: 0.5;"' : '') . '>'
                                . $product['product_name'] . 
                                '</a>
                                <span class="stext-105 cl3">$' . $product['product_price'] . '</span>
                                ' . $message . '
                            </div>
                            <div class="block2-txt-child2 flex-r p-t-3">
                                <a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2"' . ($isUnavailable || $isOutOfStock ? 'style="pointer-events: none; opacity: 0.5;"' : '') . '>
                                    <img class="icon-heart1 dis-block trans-04" src="images/icons/icon-heart-01.png" alt="ICON">
                                    <img class="icon-heart2 dis-block trans-04 ab-t-l" src="images/icons/icon-heart-02.png" alt="ICON">
                                </a>
                            </div>
                        </div>
                        <div class="block2-txt-child2 flex-r p-t-3">';
                    
        // Display color circles
        foreach ($colors as $index => $color) {
            $iconClass = strtolower($color['color']) === 'white' ? 'zmdi-circle-o' : 'zmdi-circle';
            $styleColor = strtolower($color['color']) === 'white' ? '#aaa' : $color['color'];
            echo '<span class="fs-15 lh-12 m-r-6 color-circle" style="color: ' . $styleColor . '; cursor: pointer;" 
                    data-image="images/' . $color['image'] . '" data-product-id="' . $product_id . '">
                    <i class="zmdi ' . $iconClass . '"></i>
                </span>';
        }

        echo '      </div>
                    </div>
                  </div>';
        }
        echo ob_get_clean();
        exit;
    } else {
        echo ""; // Return empty if no products
    }
}


?>
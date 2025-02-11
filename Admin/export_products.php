<?php
include 'dataconnection.php';

$query = "SELECT 
            p.product_name, 
            p.tags, 
            v.color, 
            c.category_name, 
            p.product_price_usd, 
            p.stock_quantity, 
            s.product_status 
          FROM product p
          JOIN category c ON p.category_id = c.category_id
          JOIN product_variant v ON p.product_id = v.product_id
          JOIN product_status s ON p.product_status = s.p_status_id";

$result = $connect->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>

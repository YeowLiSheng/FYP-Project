<?php
require('fpdf.php');

function Receipt($order_id, $user_id, $user_name, $address, $order_items, $total_payment, $discount_amount, $delivery_charge) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    
    $pdf->Cell(0, 10, 'E-COMMERCE RECEIPT', 0, 1, 'C');
    $pdf->Ln(10);

    // Order details
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, "Order ID: #$order_id", 0, 1);
    $pdf->Cell(0, 10, "Customer: $user_name", 0, 1);
    $pdf->Cell(0, 10, "Shipping Address: $address", 0, 1);
    $pdf->Ln(10);

    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(70, 10, 'Product', 1);
    $pdf->Cell(30, 10, 'Quantity', 1);
    $pdf->Cell(40, 10, 'Price', 1);
    $pdf->Cell(40, 10, 'Total', 1);
    $pdf->Ln();

   
    $pdf->SetFont('Arial', '', 12);
    foreach ($order_items as $item) {
        $pdf->Cell(70, 10, $item['product_name'], 1);
        $pdf->Cell(30, 10, $item['quantity'], 1);
        $pdf->Cell(40, 10, 'RM' . number_format($item['unit_price'], 2), 1);
        $pdf->Cell(40, 10, 'RM' . number_format($item['total_price'], 2), 1);
        $pdf->Ln();
    }

  
    $pdf->Ln(10);
    $pdf->Cell(0, 10, "Sub-Total: RM" . number_format($total_payment + $discount_amount - $delivery_charge, 2), 0, 1);
    $pdf->Cell(0, 10, "Discount: -RM" . number_format($discount_amount, 2), 0, 1);
    $pdf->Cell(0, 10, "Delivery Charge: RM" . number_format($delivery_charge, 2), 0, 1);
    $pdf->Cell(0, 10, "Total Payment: RM" . number_format($total_payment, 2), 0, 1);

    // 输出文件
    $file_name = "receipt_$order_id.pdf";
    $pdf->Output('F', $file_name); // 将文件保存到服务器

    return $file_name;
}


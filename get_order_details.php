<?php
session_start();
include 'config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $response = ['success' => false, 'data' => ''];

    if ($order_id > 0) {
        $sql = "SELECT products.name, order_items.quantity, products.price, products.image, discount.discount_amount
                FROM order_items 
                JOIN products ON order_items.product_id = products.product_id 
                LEFT JOIN discount ON products.discount_id = discount.discount_id
                WHERE order_items.order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = '<table class="order-details-table">';
        $data .= '<thead><tr><th>Product Name</th><th>Quantity</th><th>Price Before Discount</th><th>Price After Discount</th><th>Image</th></tr></thead><tbody>';

        while ($row = $result->fetch_assoc()) {
            $price_before_discount = $row['price'];
            $discount = isset($row['discount_amount']) ? $row['discount_amount'] : 0;
            $price_after_discount = $price_before_discount - ($price_before_discount * ($discount / 100));

            $data .= '<tr>';
            $data .= '<td>' . htmlspecialchars($row['name']) . '</td>';
            $data .= '<td>' . htmlspecialchars($row['quantity']) . '</td>';
            $data .= '<td>$' . number_format($price_before_discount, 2) . '</td>';
            $data .= '<td>$' . number_format($price_after_discount, 2) . '</td>';
            $data .= '<td><img src="images/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '" width="50"></td>';
            $data .= '</tr>';
        }
        $data .= '</tbody></table>';

        $response['success'] = true;
        $response['data'] = $data;
    }

    echo json_encode($response);
}
?>

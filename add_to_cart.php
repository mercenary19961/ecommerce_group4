<?php
include 'config/db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$response = ['cartCount' => 0];

if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = 1; // Default quantity to 1

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    $response['cartCount'] = array_sum($_SESSION['cart']);
}

echo json_encode($response);
?>

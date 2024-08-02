<?php
session_start();
include 'config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    
    if (isset($_POST['update_quantity'])) {
        $quantity = (int)$_POST['quantity'];
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    } elseif (isset($_POST['remove_item'])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

header('Location: cart.php');
exit();
?>

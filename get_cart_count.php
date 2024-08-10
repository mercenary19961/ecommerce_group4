<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$total_quantity = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $quantity) {
        $total_quantity += $quantity;
    }
}

echo json_encode(['cartCount' => $total_quantity]);
?>

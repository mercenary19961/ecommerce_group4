<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['cart_count'])) {
    $_SESSION['cart_count'] = $_POST['cart_count'];
}
?>

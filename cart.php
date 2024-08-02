<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'config/db_connect.php';

// Fetch cart items from the session
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Fetch product details for items in the cart
$product_details = [];
if (!empty($cart_items)) {
    $product_ids = implode(',', array_keys($cart_items));
    $sql = "SELECT product_id, name, price FROM products WHERE product_id IN ($product_ids)";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $product_details[$row['product_id']] = $row;
    }
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <title>Cart</title>
</head>
<body>
    <main class="container">
        <h1>Cart</h1>
        <?php if (empty($cart_items)): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $product_id => $quantity): ?>
                        <?php if (isset($product_details[$product_id])): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product_details[$product_id]['name']); ?></td>
                                <td><?php echo $quantity; ?></td>
                                <td>$<?php echo number_format($product_details[$product_id]['price'], 2); ?></td>
                                <td>$<?php echo number_format($quantity * $product_details[$product_id]['price'], 2); ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="text-right">
                <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>

<?php include 'includes/footer.php'; ?>

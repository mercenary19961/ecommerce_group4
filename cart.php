<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'config/db_connect.php';

// Handle add, remove, and delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($product_id > 0) {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    if (isset($_SESSION['cart'][$product_id])) {
                        $_SESSION['cart'][$product_id]++;
                    } else {
                        $_SESSION['cart'][$product_id] = 1;
                    }
                    break;
                case 'remove':
                    if (isset($_SESSION['cart'][$product_id])) {
                        $_SESSION['cart'][$product_id]--;
                        if ($_SESSION['cart'][$product_id] <= 0) {
                            unset($_SESSION['cart'][$product_id]);
                        }
                    }
                    break;
                case 'delete':
                    if (isset($_SESSION['cart'][$product_id])) {
                        unset($_SESSION['cart'][$product_id]);
                    }
                    break;
            }
        }
    }
}

// Fetch cart items from the session
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Fetch product details for items in the cart
$product_details = [];
if (!empty($cart_items)) {
    $product_ids = implode(',', array_keys($cart_items));
    $sql = "SELECT products.product_id, products.name, products.price, discount.discount_amount 
            FROM products 
            LEFT JOIN discount ON products.discount_id = discount.discount_id 
            WHERE products.product_id IN ($product_ids)";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $product_details[$row['product_id']] = $row;
    }
}

// Calculate total cost
$total_cost = 0;
foreach ($cart_items as $product_id => $quantity) {
    if (isset($product_details[$product_id])) {
        $price = $product_details[$product_id]['price'];
        if ($product_details[$product_id]['discount_amount']) {
            $discounted_price = $price - ($price * ($product_details[$product_id]['discount_amount'] / 100));
        } else {
            $discounted_price = $price;
        }
        $total_cost += $quantity * $discounted_price;
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $product_id => $quantity): ?>
                        <?php if (isset($product_details[$product_id])): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product_details[$product_id]['name']); ?></td>
                                <td><?php echo $quantity; ?></td>
                                <td>
                                    $<?php 
                                    $price = $product_details[$product_id]['price'];
                                    if ($product_details[$product_id]['discount_amount']) {
                                        $discounted_price = $price - ($price * ($product_details[$product_id]['discount_amount'] / 100));
                                        echo number_format($discounted_price, 2);
                                    } else {
                                        echo number_format($price, 2);
                                    }
                                    ?>
                                </td>
                                <td>
                                    $<?php 
                                    if ($product_details[$product_id]['discount_amount']) {
                                        $total_price = $quantity * $discounted_price;
                                    } else {
                                        $total_price = $quantity * $price;
                                    }
                                    echo number_format($total_price, 2); 
                                    ?>
                                </td>
                                <td>
                                    <form method="post" action="cart.php" style="display:inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <input type="hidden" name="action" value="add">
                                        <button type="submit" class="btn btn-success btn-sm">+</button>
                                    </form>
                                    <form method="post" action="cart.php" style="display:inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="btn btn-warning btn-sm">-</button>
                                    </form>
                                    <form method="post" action="cart.php" style="display:inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Total Cost:</strong></td>
                        <td><strong>$<?php echo number_format($total_cost, 2); ?></strong></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <div class="text-right">
                <a href="checkout.php?method=cash" class="btn btn-primary">Pay in Cash</a>
                <a href="credit_card_payment.php" class="btn btn-secondary">Pay with Credit</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>

<?php include 'includes/footer.php'; ?>

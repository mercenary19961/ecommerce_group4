<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['previous_page']) && !isset($_POST['action'])) {
    $_SESSION['previous_page'] = $_SERVER['HTTP_REFERER'] ?? 'products.php';
}
include 'config/db_connect.php';

// Handle add, remove, delete actions, and coupon application
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($product_id > 0) {
        if (isset($_POST['action'])) {
            $sql = "SELECT stock FROM products WHERE product_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $product_id);
            $stmt->execute();
            $stmt->bind_result($stock);
            $stmt->fetch();
            $stmt->close();

            switch ($_POST['action']) {
                case 'add':
                    if (isset($_SESSION['cart'][$product_id])) {
                        if ($_SESSION['cart'][$product_id] < $stock) {
                            $_SESSION['cart'][$product_id]++;
                        } else {
                            $_SESSION['error'] = "Not enough stock for this product.";
                        }
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

    if (isset($_POST['coupon_code'])) {
        $coupon_code = htmlspecialchars(trim($_POST['coupon_code']));

        $stmt = $conn->prepare("SELECT discount.discount_amount FROM coupons 
                                JOIN discount ON coupons.discount_id = discount.discount_id 
                                WHERE coupons.code = ? AND coupons.expiry_date >= CURDATE()");
        $stmt->bind_param('s', $coupon_code);
        $stmt->execute();
        $stmt->bind_result($coupon_discount);
        if ($stmt->fetch()) {
            $_SESSION['coupon_discount'] = $coupon_discount;
            $_SESSION['coupon_code'] = $coupon_code;
        } else {
            $_SESSION['coupon_error'] = "Invalid or expired coupon code.";
        }
        $stmt->close();
    }

    if (isset($_POST['remove_coupon'])) {
        unset($_SESSION['coupon_discount']);
        unset($_SESSION['coupon_code']);
    }

    // Redirect to the cart page to prevent form resubmission
    header('Location: cart.php');
    exit();
}

// Fetch cart items from the session
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Fetch product details for items in the cart
$product_details = [];
if (!empty($cart_items)) {
    $product_ids = implode(',', array_keys($cart_items));
    $sql = "SELECT products.product_id, products.name, products.description, products.price, products.stock, discount.discount_amount 
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

// Apply coupon discount
$coupon_discount = isset($_SESSION['coupon_discount']) ? $_SESSION['coupon_discount'] : 0;
$total_cost_after_coupon = $total_cost - ($total_cost * ($coupon_discount / 100));

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Cart</title>
    <style>
        .btn-custom {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
        }
        .btn-custom:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .button-group .right-align {
            margin-left: auto;
        }
        .actions-column {
            text-align: right;
        }
        .coupon_form {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .coupon-input {
            width: 20%;
            margin-bottom: 10px;
        }
        .discount-amount {
            color: #ef4444 !important;
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Cart</h1>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (empty($cart_items)): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Price Before Discount</th>
                        <th>Discounted Price</th>
                        <th>Total</th>
                        <th class="actions-column">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $product_id => $quantity): ?>
                        <?php if (isset($product_details[$product_id])): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product_details[$product_id]['name']); ?></td>
                                <td><?php echo htmlspecialchars($product_details[$product_id]['description']); ?></td>
                                <td><?php echo $quantity; ?></td>
                                <td>$<?php echo number_format($product_details[$product_id]['price'], 2); ?></td>
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
                                <td class="actions-column">
                                    <form method="post" action="cart.php" style="display:inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <input type="hidden" name="action" value="add">
                                        <button type="submit" class="btn btn-custom btn-sm"><i class="fa-solid fa-plus" style="color: #ffffff;"></i></button>
                                    </form>
                                    <form method="post" action="cart.php" style="display:inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="btn btn-custom btn-sm"><i class="fa-solid fa-minus" style="color: #ffffff;"></i></button>
                                    </form>
                                    <form method="post" action="cart.php" style="display:inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-custom btn-sm"><i class="fa-solid fa-trash-can" style="color: #ffffff;"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="5" class="text-right"><strong>Total Cost:</strong></td>
                        <td class="total-cost"><strong>$<?php echo number_format($total_cost, 2); ?></strong></td>
                        <td></td>
                    </tr>
                    <?php if ($coupon_discount > 0): ?>
                        <tr>
                            <td colspan="5" class="text-right"><strong>Discount (<?php echo $coupon_discount; ?>%):</strong></td>
                            <td class="discount-amount"><strong>-$<?php echo number_format($total_cost * ($coupon_discount / 100), 2); ?></strong></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-right"><strong>Total After Discount:</strong></td>
                            <td class="total-after-discount"><strong>$<?php echo number_format($total_cost_after_coupon, 2); ?></strong></td>
                            <td></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="button-group">
                <div>
                    <button onclick="checkLogin('cash')" class="btn btn-primary">Pay in Cash</button>
                    <button onclick="checkLogin('credit')" class="btn btn-secondary">Pay with Credit</button>
                </div>
                <div class="right-align">
                    <a href="products.php" class="btn btn-secondary">Back</a>
                </div>
            </div>
            <form method="post" action="cart.php" class="coupon_form">
                <div class="form-group">
                    <label for="coupon_code">Apply Coupon:</label>
                    <input type="text" name="coupon_code" id="coupon_code" class="form-control coupon-input" placeholder="Enter coupon code">
                </div>
                <button type="submit" class="btn btn-primary">Apply Coupon</button>
                <button type="submit" name="remove_coupon" class="btn btn-secondary">Remove Coupon</button>
                <?php if (isset($_SESSION['coupon_error'])): ?>
                    <p style="color: red;"><?php echo $_SESSION['coupon_error']; ?></p>
                    <?php unset($_SESSION['coupon_error']); ?>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </main>
    
    <script>
        function checkLogin(paymentMethod) {
            <?php if (!$isLoggedIn): ?>
                Swal.fire({
                    icon: 'warning',
                    title: 'You need to login first!',
                    text: 'Please login to proceed with the payment.',
                    showCancelButton: true,
                    confirmButtonText: 'Login',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'swal-custom-button'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'login.php';
                    }
                });
            <?php else: ?>
                if (paymentMethod === 'cash') {
                    window.location.href = 'checkout.php?method=cash';
                } else if (paymentMethod === 'credit') {
                    window.location.href = 'credit_card_payment.php';
                }
            <?php endif; ?>
        }
    </script>
</body>
</html>

<?php include 'includes/footer.php'; ?>

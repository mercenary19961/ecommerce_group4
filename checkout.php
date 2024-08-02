<?php
session_start();
include 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    // Save current cart to session
    $_SESSION['redirect_to'] = 'checkout.php?method=' . $_GET['method'];
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$method = isset($_GET['method']) ? $_GET['method'] : '';

if (!empty($_SESSION['cart'])) {
    // Save cart to database
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $sql_check = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param('ii', $user_id, $product_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Update existing cart item
            $sql_update = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param('iii', $quantity, $user_id, $product_id);
            $stmt_update->execute();
        } else {
            // Insert new cart item
            $sql_insert = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param('iii', $user_id, $product_id, $quantity);
            $stmt_insert->execute();
        }
    }

    // Clear session cart
    unset($_SESSION['cart']);
}

if ($method === 'cash') {
    // Handle cash on delivery logic
    // Insert order details into database
    // Clear cart from database

    // Redirect to a success page or display a success message
    echo "Order placed successfully! You chose cash on delivery.";
} elseif ($method === 'credit') {
    // Redirect to a credit card payment page
    header("Location: credit_card_payment.php");
    exit();
} else {
    header("Location: cart.php");
    exit();
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
    <title>Checkout</title>
</head>
<body>
    <div class="container">
        <h1>Checkout</h1>
        <p>Processing your order...</p>
    </div>
</body>
</html>

<?php include 'includes/footer.php'; ?>

<?php
session_start();
include 'config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $card_number = $_POST['card_number'];
    $card_name = $_POST['card_name'];
    $expiry_date = $_POST['expiry_date'];
    $cvv = $_POST['cvv'];
    $response = ['success' => false, 'message' => ''];

    // Assuming you have validated the card details and it's successful
    $conn->begin_transaction();

    try {
        // Insert into orders table
        $sql_order = "INSERT INTO orders (user_id, order_date, total, payment_method) VALUES (?, NOW(), 0, 'credit')";
        $stmt_order = $conn->prepare($sql_order);
        $stmt_order->bind_param('i', $user_id);
        $stmt_order->execute();

        // Get the last inserted order id
        $order_id = $stmt_order->insert_id;

        // Insert each cart item into order_items table
        $total_amount = 0;
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $sql_product = "SELECT price FROM products WHERE product_id = ?";
            $stmt_product = $conn->prepare($sql_product);
            $stmt_product->bind_param('i', $product_id);
            $stmt_product->execute();
            $result_product = $stmt_product->get_result();
            $product = $result_product->fetch_assoc();

            $price = $product['price'];
            $total_price = $price * $quantity;
            $total_amount += $total_price;

            $sql_order_item = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt_order_item = $conn->prepare($sql_order_item);
            $stmt_order_item->bind_param('iiid', $order_id, $product_id, $quantity, $price);
            $stmt_order_item->execute();
        }

        // Update the total amount in the orders table
        $sql_update_total = "UPDATE orders SET total = ? WHERE order_id = ?";
        $stmt_update_total = $conn->prepare($sql_update_total);
        $stmt_update_total->bind_param('di', $total_amount, $order_id);
        $stmt_update_total->execute();

        $conn->commit();

        // Clear session cart
        unset($_SESSION['cart']);

        $response['success'] = true;
        $response['message'] = 'Payment processed successfully!';
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = "Failed to process payment. Please try again. Error: " . $e->getMessage();
    }

    echo json_encode($response);
}
?>

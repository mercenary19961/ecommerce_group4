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
        // Calculate total amount and apply coupon discount
        $total_amount = 0;
        $product_details = [];
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $sql_product = "SELECT price, discount_id, stock FROM products WHERE product_id = ?";
            $stmt_product = $conn->prepare($sql_product);
            $stmt_product->bind_param('i', $product_id);
            $stmt_product->execute();
            $result_product = $stmt_product->get_result();
            $product = $result_product->fetch_assoc();

            if ($product['stock'] < $quantity) {
                throw new Exception("Not enough stock for product ID: $product_id");
            }

            $price = $product['price'];
            if ($product['discount_id']) {
                $sql_discount = "SELECT discount_amount FROM discount WHERE discount_id = ?";
                $stmt_discount = $conn->prepare($sql_discount);
                $stmt_discount->bind_param('i', $product['discount_id']);
                $stmt_discount->execute();
                $result_discount = $stmt_discount->get_result();
                $discount = $result_discount->fetch_assoc();
                $price -= ($price * ($discount['discount_amount'] / 100));
            }
            $total_amount += $price * $quantity;
            $product_details[$product_id] = $price;
        }

        // Apply coupon discount
        $coupon_discount = isset($_SESSION['coupon_discount']) ? $_SESSION['coupon_discount'] : 0;
        $total_amount_after_coupon = $total_amount - ($total_amount * ($coupon_discount / 100));

        // Insert into orders table
        $sql_order = "INSERT INTO orders (user_id, order_date, total, payment_method) VALUES (?, NOW(), ?, 'credit')";
        $stmt_order = $conn->prepare($sql_order);
        $stmt_order->bind_param('id', $user_id, $total_amount_after_coupon);
        $stmt_order->execute();

        // Get the last inserted order id
        $order_id = $stmt_order->insert_id;

        // Insert each cart item into order_items table and update product stock
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $price = $product_details[$product_id];
            $sql_order_item = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt_order_item = $conn->prepare($sql_order_item);
            $stmt_order_item->bind_param('iiid', $order_id, $product_id, $quantity, $price);
            $stmt_order_item->execute();

            // Update product stock
            $sql_update_stock = "UPDATE products SET stock = stock - ? WHERE product_id = ?";
            $stmt_update_stock = $conn->prepare($sql_update_stock);
            $stmt_update_stock->bind_param('ii', $quantity, $product_id);
            $stmt_update_stock->execute();
        }

        $conn->commit();

        // Clear session cart and coupon
        unset($_SESSION['cart']);
        unset($_SESSION['coupon_discount']);
        unset($_SESSION['coupon_code']);

        $response['success'] = true;
        $response['message'] = 'Payment processed successfully!';
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = "Failed to process payment. Please try again. Error: " . $e->getMessage();
    }

    echo json_encode($response);
}
?>

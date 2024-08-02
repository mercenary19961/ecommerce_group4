<?php
session_start();
include 'config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $method = $input['method'];
    $user_id = $_SESSION['user_id'];
    $order_placed = false;
    $response = ['success' => false, 'message' => ''];

    if (!empty($_SESSION['cart'])) {
        if ($method === 'cash') {
            // Handle cash on delivery logic
            $conn->begin_transaction();

            try {
                // Insert into orders table
                $sql_order = "INSERT INTO orders (user_id, payment_method, total) VALUES (?, ?, ?)";
                $stmt_order = $conn->prepare($sql_order);
                $total_amount = 0;

                // Calculate total amount
                foreach ($_SESSION['cart'] as $product_id => $quantity) {
                    $sql_product = "SELECT price FROM products WHERE product_id = ?";
                    $stmt_product = $conn->prepare($sql_product);
                    $stmt_product->bind_param('i', $product_id);
                    $stmt_product->execute();
                    $result_product = $stmt_product->get_result();
                    $product = $result_product->fetch_assoc();
                    $total_amount += $product['price'] * $quantity;
                }

                $stmt_order->bind_param('iss', $user_id, $method, $total_amount);
                $stmt_order->execute();

                // Get the last inserted order id
                $order_id = $stmt_order->insert_id;

                // Insert each cart item into order_items table
                foreach ($_SESSION['cart'] as $product_id => $quantity) {
                    $sql_order_item = "INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)";
                    $stmt_order_item = $conn->prepare($sql_order_item);
                    $stmt_order_item->bind_param('iii', $order_id, $product_id, $quantity);
                    $stmt_order_item->execute();
                }

                $conn->commit();

                // Clear session cart
                unset($_SESSION['cart']);

                $response['success'] = true;
                $response['message'] = 'Order placed successfully! Your order will be delivered to you soon.';
            } catch (Exception $e) {
                $conn->rollback();
                $response['message'] = "Failed to place order. Please try again. Error: " . $e->getMessage();
            }
        }
    } else {
        $response['message'] = "Your cart is empty.";
    }

    echo json_encode($response);
}
?>

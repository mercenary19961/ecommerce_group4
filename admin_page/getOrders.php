<?php
include 'config/connection.php';

$purchaseOrders = [];
$salesOrders = [];
$categories = [];

// Fetch orders without discounts (considered as purchases)
$sql = "SELECT DATE(order_date) as date, COUNT(*) AS count 
        FROM orders 
        LEFT JOIN order_items ON orders.order_id = order_items.order_id 
        LEFT JOIN products ON order_items.product_id = products.product_id 
        WHERE products.discount_id IS NULL 
        GROUP BY DATE(order_date) 
        ORDER BY DATE(order_date);";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $purchaseOrders[] = (int)$row['count'];
    $categories[] = $row['date'];
}

// Fetch orders with discounts (considered as Offers)
$sql = "SELECT DATE(order_date) as date, COUNT(*) AS count 
        FROM orders 
        LEFT JOIN order_items ON orders.order_id = order_items.order_id 
        LEFT JOIN products ON order_items.product_id = products.product_id 
        WHERE products.discount_id IS NOT NULL 
        GROUP BY DATE(order_date) 
        ORDER BY DATE(order_date);";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $salesOrders[] = (int)$row['count'];
}

// Combine data
$data = [
    'categories' => $categories,
    'purchaseOrders' => $purchaseOrders,
    'salesOrders' => $salesOrders,
];

echo json_encode($data);


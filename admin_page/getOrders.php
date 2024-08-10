<?php
include 'config/connection.php';

$purchaseOrders = [];
$salesOrders = [];
$categories = [];

// Fetch all dates
$sql = "SELECT DISTINCT DATE(order_date) as date 
        FROM orders 
        ORDER BY DATE(order_date);";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $categories[] = $row['date'];
    $purchaseOrders[$row['date']] = 0;  // Initialize with 0
    $salesOrders[$row['date']] = 0;     // Initialize with 0
}

// Fetch orders without discounts (considered as purchases)
$sql = "SELECT DATE(order_date) as date, COUNT(*) AS count 
        FROM orders 
        LEFT JOIN order_items ON orders.order_id = order_items.order_id 
        LEFT JOIN products ON order_items.product_id = products.product_id 
        WHERE products.discount_id IS NULL 
        GROUP BY DATE(order_date);";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $purchaseOrders[$row['date']] = (int)$row['count'];
}

// Fetch orders with discounts (considered as sales)
$sql = "SELECT DATE(order_date) as date, COUNT(*) AS count 
        FROM orders 
        LEFT JOIN order_items ON orders.order_id = order_items.order_id 
        LEFT JOIN products ON order_items.product_id = products.product_id 
        WHERE products.discount_id IS NOT NULL 
        GROUP BY DATE(order_date);";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $salesOrders[$row['date']] = (int)$row['count'];
}

// Convert arrays to match the categories
$purchaseOrders = array_values($purchaseOrders);
$salesOrders = array_values($salesOrders);

// Combine data
$data = [
    'categories' => $categories,
    'purchaseOrders' => $purchaseOrders,
    'salesOrders' => $salesOrders,
];

echo json_encode($data);

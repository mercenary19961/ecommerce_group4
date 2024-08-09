<?php
include 'config/connection.php';

$purchaseOrders = [];
$salesOrders = [];
$categories = [];

// Fetch CASH orders
$sql = "SELECT DATE(order_date) as date, COUNT(*) AS count FROM orders WHERE payment_method = 'cash' GROUP BY DATE(order_date) ORDER BY DATE(order_date);";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $purchaseOrders[] = (int)$row['count'];
    $categories[] = $row['date'];
}

// credit
$sql = "SELECT DATE(order_date) as date, COUNT(*) AS count FROM orders WHERE payment_method = 'credit' GROUP BY DATE(order_date) ORDER BY DATE(order_date);";
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
?>
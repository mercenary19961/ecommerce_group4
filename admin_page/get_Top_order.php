<?php
include 'config/connection.php';

$sql = "SELECT p.name, COUNT(oi.product_id) AS request_count
FROM order_items oi
JOIN products p ON oi.product_id = p.product_id
GROUP BY p.name
ORDER BY request_count DESC
LIMIT 5;";
$result = $conn->query($sql);

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode($products);

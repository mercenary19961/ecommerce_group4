<?php
include 'config/connection.php';

$sql = "SELECT name, COUNT(*) AS count FROM products GROUP BY name ORDER BY count DESC LIMIT 5;";
$result = $conn->query($sql);

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode($products);
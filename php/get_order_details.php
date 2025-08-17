<?php
header("Content-Type: application/json");
require "db.php";

if (!isset($_GET["id"])) {
    echo json_encode(["success" => false, "message" => "Missing order ID"]);
    exit;
}

$order_id = intval($_GET["id"]);

// Fetch order info
$order_sql = "SELECT * FROM orders WHERE id=$order_id";
$order_result = $conn->query($order_sql);

if ($order_result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Order not found"]);
    exit;
}

$order = $order_result->fetch_assoc();

// Fetch order items
$item_sql = "SELECT oi.*, p.name 
             FROM order_items oi 
             JOIN products p ON oi.product_id = p.id 
             WHERE oi.order_id=$order_id";
$item_result = $conn->query($item_sql);

$items = [];
$total = 0;
while ($row = $item_result->fetch_assoc()) {
    $row["subtotal"] = $row["price"] * $row["quantity"];
    $total += $row["subtotal"];
    $items[] = $row;
}

echo json_encode([
    "success" => true,
    "order" => $order,
    "items" => $items,
    "total" => $total
]);

$conn->close();
?>
<?php
include 'db.php';

$stats = [];

// Total items
$res = $conn->query("SELECT COUNT(*) AS total_items FROM inventory");
$stats['total_items'] = $res->fetch_assoc()['total_items'];

// Total stock
$res = $conn->query("SELECT SUM(stock) AS total_stock FROM inventory");
$stats['total_stock'] = $res->fetch_assoc()['total_stock'];

// Out of stock items
$res = $conn->query("SELECT COUNT(*) AS out_of_stock FROM inventory WHERE stock=0");
$stats['out_of_stock'] = $res->fetch_assoc()['out_of_stock'];

header('Content-Type: application/json');
echo json_encode($stats);
?>
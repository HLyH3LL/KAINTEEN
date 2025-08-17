<?php
include 'db.php';

$result = $conn->query("SELECT * FROM inventory ORDER BY created_at DESC");

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

header('Content-Type: application/json');
echo json_encode($items);
?>
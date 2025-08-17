<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $stock = $_POST['stock'];
    $price = $_POST['price'];
    $category = $_POST['category'];

    $stmt = $conn->prepare("INSERT INTO inventory (name, stock, price, category) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sids", $name, $stock, $price, $category);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Item added successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
    }
}
?>
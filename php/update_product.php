<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $stock = $_POST['stock'];
    $price = $_POST['price'];
    $category = $_POST['category'];

    $stmt = $conn->prepare("UPDATE inventory SET name=?, stock=?, price=?, category=? WHERE id=?");
    $stmt->bind_param("sidsi", $name, $stock, $price, $category, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Item updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
    }
}
?>
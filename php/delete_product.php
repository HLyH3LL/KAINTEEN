<?php
header("Content-Type: application/json");
require "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data["id"])) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

$id = intval($data["id"]);

$sql = "DELETE FROM products WHERE id=$id";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => $conn->error]);
}

$conn->close();
?>
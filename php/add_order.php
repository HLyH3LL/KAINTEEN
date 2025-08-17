<?php
include 'db.php'; // your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_no = $_POST['student_no'];
    $status = $_POST['status'] ?? 'UNPAID';

    // Insert first (id auto increments)
    $stmt = $conn->prepare("INSERT INTO orders (order_code, student_no, status) VALUES ('TEMP', ?, ?)");
    $stmt->bind_param("ss", $student_no, $status);
    $stmt->execute();

    // Get last inserted id
    $lastId = $stmt->insert_id;

    // Generate proper order_code (ORD-001, ORD-002...)
    $order_code = "ORD-" . str_pad($lastId, 3, "0", STR_PAD_LEFT);

    // Update the temp record with correct order_code
    $update = $conn->prepare("UPDATE orders SET order_code=? WHERE id=?");
    $update->bind_param("si", $order_code, $lastId);
    $update->execute();

    echo json_encode([
        "success" => true,
        "order_code" => $order_code,
        "student_no" => $student_no,
        "status" => $status
    ]);
}
?>
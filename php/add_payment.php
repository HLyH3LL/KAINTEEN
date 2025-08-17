<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $student_no = $_POST['student_no'];
    $status = $_POST['status'] ?? 'UNPAID';
    $payment_date = $_POST['payment_date'];
    $mop = $_POST['mop']; // GCASH or Over The Counter

    $stmt = $conn->prepare("INSERT INTO payments (order_id, student_no, status, payment_date, mop) 
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $order_id, $student_no, $status, $payment_date, $mop);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Payment added successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
    }
}
?>
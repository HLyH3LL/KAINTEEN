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
<?php
session_start();
$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = $quantity;
}

header('Location: student_dashboard.php');
exit;

<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_number = $_POST['student_number'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM customers WHERE student_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $student_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            echo "Customer login successful!";
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "Student number not found.";
    }

    $stmt->close();
    $conn->close();
}
?>
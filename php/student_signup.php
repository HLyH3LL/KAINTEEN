<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_number = $_POST['student_number'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // ✅ Check if student number or email already exists
    $check = $conn->prepare("SELECT * FROM customers WHERE student_number = ? OR email = ?");
    $check->bind_param("ss", $student_number, $email);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows > 0) {
        echo "Student number or email already exists.";
        exit;
    }

    $sql = "INSERT INTO customers (student_number, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $student_number, $email, $password);

    if ($stmt->execute()) {
        // ✅ Set session right after sign up
        $_SESSION['student_number'] = $student_number;
        $_SESSION['student_name'] = $email; // or use a separate name column
        $_SESSION['loggedin'] = true;

        echo "success|dashboard.php";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

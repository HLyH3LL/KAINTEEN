<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_number = $_POST['student_number'];
    $password = $_POST['password'];
    $ip_address = $_SERVER['REMOTE_ADDR']; // capture user's IP

    // Get user by student number
    $stmt = $conn->prepare("SELECT * FROM customers WHERE student_number = ?");
    $stmt->bind_param("s", $student_number);
    $stmt->execute();
    $result = $stmt->get_result();

    $success = false; // Track success of login

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check password
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['student_number'] = $user['student_number'];
            $_SESSION['student_name'] = $user['email']; // Replace with name if you add it later
            $_SESSION['loggedin'] = true;

            $success = true;
            echo "success|studentDashboard.html"; // Adjust if your dashboard file name differs
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "Student number not found.";
    }

    // Log login attempt to login_history table
    $log_stmt = $conn->prepare("INSERT INTO login_history (student_number, success, ip_address) VALUES (?, ?, ?)");
    $log_stmt->bind_param("sis", $student_number, $success, $ip_address);
    $log_stmt->execute();
    $log_stmt->close();

    $stmt->close();
    $conn->close();
}
?>

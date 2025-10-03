<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_number = $_POST['student_number'];
    $password = $_POST['password'];
    $ip_address = $_SERVER['REMOTE_ADDR']; 

    $stmt = $conn->prepare("SELECT * FROM customers WHERE student_number = ?");
    $stmt->bind_param("s", $student_number);
    $stmt->execute();
    $result = $stmt->get_result();

    $success = false; 

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            
            $_SESSION['student_number'] = $user['student_number'];
            $_SESSION['student_name'] = $user['email']; 
            $_SESSION['loggedin'] = true;

            $success = true;
            echo "success|studentDashboard.html";
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "Student number not found.";
    }

    
    $log_stmt = $conn->prepare("INSERT INTO login_history (student_number, success, ip_address) VALUES (?, ?, ?)");
    $log_stmt->bind_param("sis", $student_number, $success, $ip_address);
    $log_stmt->execute();
    $log_stmt->close();

    $stmt->close();
    $conn->close();
}
?>

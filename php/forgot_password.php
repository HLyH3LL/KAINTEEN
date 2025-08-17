<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_no = $_POST['student-number'];
    $email = $_POST['email'];

    // Check if student exists
    $stmt = $conn->prepare("SELECT id FROM customers WHERE student_no = ? AND email = ?");
    $stmt->bind_param("ss", $student_no, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Generate token
        $token = bin2hex(random_bytes(16));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Save in DB
        $stmt = $conn->prepare("UPDATE customers SET reset_token = ?, reset_expires = ? WHERE student_no = ?");
        $stmt->bind_param("sss", $token, $expires, $student_no);
        $stmt->execute();

        // Create reset link (adjust localhost/path to your project)
        $reset_link = "http://localhost/project/php/reset_password.php?token=" . $token;

        // Send email
        $subject = "Password Reset Request";
        $message = "Click this link to reset your password: " . $reset_link;
        $headers = "From: no-reply@yourapp.com";

        if (mail($email, $subject, $message, $headers)) {
            echo "Reset link sent to your email.";
        } else {
            echo "Failed to send email.";
        }
    } else {
        echo "No account found with that student number and email.";
    }
}
?>
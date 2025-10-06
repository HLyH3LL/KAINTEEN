<?php
require '../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_number = $_POST['student-number']; // fixed variable name here
    $email = $_POST['email'];

    // Check if student exists
    $stmt = $conn->prepare("SELECT id FROM customers WHERE student_number = ? AND email = ?");
    $stmt->bind_param("ss", $student_number, $email); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Generate token and expiration
        $token = bin2hex(random_bytes(16));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Store token in DB
        $stmt = $conn->prepare("UPDATE customers SET reset_token = ?, reset_expires = ? WHERE student_number = ?");
        $stmt->bind_param("sss", $token, $expires, $student_number); // use correct variable here too
        $stmt->execute();

        // Build reset link
        $reset_link = "http://localhost/KAINTEEN/php/reset_password.php?token=" . $token;

        // Send Email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // SMTP config
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'mjsolano@tip.edu.ph'; // ✅ Replace with your Gmail
            $mail->Password   = 'myyl hahq kdeh wbau';  // ✅ App password only
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            // Email content
            $mail->setFrom('mjsolano@tip.edu.ph', 'T.I.P KainTeen');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "Hi,<br><br>Click the link below to reset your password:<br><a href='$reset_link'>$reset_link</a><br><br>This link will expire in 1 hour.";

            $mail->send();
            echo "<script>alert('Password reset link has been sent to your email.'); window.location.href = '../html/signInStudent.html';</script>";
        } catch (Exception $e) {
            echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "No account found with that student number and email.";
    }
}
?>

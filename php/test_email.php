<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php'; // adjust path if needed

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'mjsolano@tip.edu.ph'; // your email
    $mail->Password = 'vgzc jlou senz jnfu';   // your app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('mjsolano@tip.edu.ph', 'Test Sender');
    $mail->addAddress('recipient@example.com', 'Test Recipient');

    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body    = 'This is a test email from PHPMailer';

    $mail->send();
    echo "Message sent";
} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
}

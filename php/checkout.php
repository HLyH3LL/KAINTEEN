<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if student is logged in using student_number and student_name
if (!isset($_SESSION['student_number']) || !isset($_SESSION['student_name'])) {
    header('Location: ../auth/signInStudent.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../php/dashboard.php');
    exit;
}

// Validate cart data from POST (expects cart[product_id] = quantity)
if (empty($_POST['cart']) || !is_array($_POST['cart'])) {
    die("No cart data received.");
}

$student_number = $_SESSION['student_number'];
$student_name = $_SESSION['student_name']; // Assuming this holds the student's name (or email, as per your login)

// Use student_number as display name fallback if student_name empty
$student_display = !empty($student_name) ? $student_name : $student_number;

$cart = $_POST['cart'];
$total_price = 0;

$conn->begin_transaction();

try {
    // Insert new order with total_price = 0 initially
    $stmt = $conn->prepare("INSERT INTO orders (student_number, student_name, total_price) VALUES (?, ?, 0)");
    $stmt->bind_param("ss", $student_number, $student_display);
    $stmt->execute();
    $order_id = $conn->insert_id;

    foreach ($cart as $product_id => $qty) {
        $product_id = intval($product_id);
        $qty = intval($qty);
        if ($qty < 1) continue;

        // Fetch product details (name, price, stock)
        $stmtProd = $conn->prepare("SELECT name, price, stock FROM products WHERE id = ?");
        $stmtProd->bind_param("i", $product_id);
        $stmtProd->execute();
        $resProd = $stmtProd->get_result();
        $product = $resProd->fetch_assoc();

        if (!$product) {
            throw new Exception("Product not found: ID {$product_id}");
        }
        if ($product['stock'] < $qty) {
            throw new Exception("Not enough stock for product {$product['name']}");
        }

        $subtotal = $product['price'] * $qty;
        $total_price += $subtotal;

        // Insert order item
        $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmtItem->bind_param("iiid", $order_id, $product_id, $qty, $product['price']);
        $stmtItem->execute();

        // Update product stock
        $stmtStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmtStock->bind_param("ii", $qty, $product_id);
        $stmtStock->execute();
    }

    // Update total price on order
    $stmtUpdate = $conn->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
    $stmtUpdate->bind_param("di", $total_price, $order_id);
    $stmtUpdate->execute();

    $conn->commit();
} catch (Exception $e) {
    error_log("Order processing failed: " . $e->getMessage());
    $conn->rollback();
    die("Order processing failed: " . $e->getMessage());
}

// Prepare receipt HTML
$html = "
<h2 style='text-align:center;'>TIP KainTeen Receipt</h2>
<p><strong>Student Number:</strong> " . htmlspecialchars($student_number) . "</p>
<p><strong>Student Name:</strong> " . htmlspecialchars($student_display) . "</p>
<p><strong>Order ID:</strong> {$order_id}</p>
<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>
<table border='1' cellpadding='6' cellspacing='0' width='100%'>
<thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead><tbody>";

foreach ($cart as $product_id => $qty) {
    $product_id = intval($product_id);
    $qty = intval($qty);

    $stmtProd = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
    $stmtProd->bind_param("i", $product_id);
    $stmtProd->execute();
    $res = $stmtProd->get_result();
    $p = $res->fetch_assoc();

    $subtotal = $p['price'] * $qty;

    $html .= "<tr>
                <td>" . htmlspecialchars($p['name']) . "</td>
                <td>{$qty}</td>
                <td>₱" . number_format($p['price'], 2) . "</td>
                <td>₱" . number_format($subtotal, 2) . "</td>
              </tr>";
}

$html .= "</tbody></table>
          <h3 style='text-align:right;'>Total: ₱" . number_format($total_price, 2) . "</h3>
          <p style='text-align:center;'>Thank you for ordering from TIP KainTeen!</p>";

// Generate PDF receipt
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$pdfOutput = $dompdf->output();
$pdfDir = __DIR__ . '/../receipts/';
if (!is_dir($pdfDir)) {
    mkdir($pdfDir, 0755, true);
}
$pdfPath = $pdfDir . "receipt_{$order_id}.pdf";

if (file_put_contents($pdfPath, $pdfOutput) === false) {
    error_log("Failed to save PDF receipt to {$pdfPath}");
}

// Send email with receipt if student_name looks like an email, else skip sending
// Assuming student_name contains the student's email (since your login sets student_name to email)
$student_email = filter_var($student_display, FILTER_VALIDATE_EMAIL) ? $student_display : '';

if (!empty($student_email)) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mjsolano@tip.edu.ph'; // SMTP username
        $mail->Password = 'your_app_password';    // SMTP password or app password (replace with your real app password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('mjsolano@tip.edu.ph', 'TIP KainTeen');
        $mail->addAddress($student_email, $student_display);

        $mail->isHTML(true);
        $mail->Subject = "Your TIP KainTeen Receipt - Order #{$order_id}";
        $mail->Body = "
            <p>Hi " . htmlspecialchars($student_display) . ",</p>
            <p>Thank you for ordering! Attached is your official receipt.</p>
            <p><strong>Order Total:</strong> ₱" . number_format($total_price, 2) . "</p>
            <p>Enjoy your day!</p>
        ";
        $mail->addAttachment($pdfPath, "receipt_{$order_id}.pdf");
        $mail->send();
        error_log("Email sent successfully to {$student_email}");
    } catch (Exception $e) {
        error_log("Mailer Error: " . $e->getMessage());
    }
} else {
    error_log("No valid student email found for sending receipt.");
}

// Clear cart session data
unset($_SESSION['cart']);

// Send response (JSON if requested, else plain text)
if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => "Order placed successfully! Your receipt has been sent to your email.",
        'order_id' => $order_id
    ]);
} else {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Order placed successfully! Your receipt has been sent to your email.";
}

exit;

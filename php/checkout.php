<?php
ini_set('error_log', __DIR__ . '/checkout_error.log');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
error_log("Checkout script started");

if (!isset($_SESSION['student_number'])) {
    error_log("Session student_number not set");
} else {
    error_log("Session student_number: " . $_SESSION['student_number']);
}

if (!isset($_POST['cart'])) {
    error_log("POST cart data missing or empty");
} else {
    error_log("POST cart data received");
}


require_once 'db.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * ======================================================
 *  SESSION VALIDATION
 * ======================================================
 */
if (!isset($_SESSION['student_number']) || !isset($_SESSION['student_name'])) {
    header('Location: ../auth/signInStudent.html');
    exit;
}

/**
 * ======================================================
 *  POST & CART VALIDATION
 * ======================================================
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../php/dashboard.php');
    exit;
}

if (empty($_POST['cart']) || !is_array($_POST['cart'])) {
    die("No cart data received.");
}

// from session
$student_number = $_SESSION['student_number'];
$email          = $_SESSION['student_name']; // holds the email
$cart           = $_POST['cart'];
$total_price    = 0;

// start transaction
$conn->begin_transaction();

try {
    // ✅ insert order (your 'orders' table only has student_id, student_number, total_price)
    $stmt = $conn->prepare("SELECT id FROM customers WHERE student_number = ? AND email = ?");
    $stmt->bind_param("ss", $student_number, $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $customer = $res->fetch_assoc();

    if (!$customer) {
        throw new Exception("Customer not found in database.");
    }

    $student_id = $customer['id'];

    $stmt = $conn->prepare("INSERT INTO orders (student_id, student_number, total_price) VALUES (?, ?, 0)");
    $stmt->bind_param("is", $student_id, $student_number);
    $stmt->execute();
    $order_id = $conn->insert_id;

    /**
     * ========================================
     * PROCESS CART ITEMS
     * ========================================
     */
    foreach ($cart as $product_id => $qty) {
        $product_id = intval($product_id);
        $qty = intval($qty);
        if ($qty < 1) continue;

        $stmtProd = $conn->prepare("SELECT name, price, stock FROM products WHERE id = ?");
        $stmtProd->bind_param("i", $product_id);
        $stmtProd->execute();
        $resProd = $stmtProd->get_result();
        $product = $resProd->fetch_assoc();

        if (!$product) throw new Exception("Product not found: ID {$product_id}");
        if ($product['stock'] < $qty) throw new Exception("Not enough stock for {$product['name']}");

        $subtotal = $product['price'] * $qty;
        $total_price += $subtotal;

        // insert order item
        $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmtItem->bind_param("iiid", $order_id, $product_id, $qty, $product['price']);
        $stmtItem->execute();

        // update stock
        $stmtStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmtStock->bind_param("ii", $qty, $product_id);
        $stmtStock->execute();
    }

    // update total price
    $stmtUpdate = $conn->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
    $stmtUpdate->bind_param("di", $total_price, $order_id);
    $stmtUpdate->execute();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    error_log("Order processing failed: " . $e->getMessage());
    die("Order failed: " . htmlspecialchars($e->getMessage()));
}

/**
 * ======================================================
 *  GENERATE RECEIPT PDF
 * ======================================================
 */
$html = "
<h2 style='text-align:center;'>TIP KainTeen Receipt</h2>
<p><strong>Student Number:</strong> " . htmlspecialchars($student_number) . "</p>
<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
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

// generate pdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$pdfOutput = $dompdf->output();
$pdfDir = __DIR__ . '/../receipts/';
if (!is_dir($pdfDir)) mkdir($pdfDir, 0755, true);
$pdfPath = $pdfDir . "receipt_{$order_id}.pdf";
file_put_contents($pdfPath, $pdfOutput);

/**
 * ======================================================
 *  SEND EMAIL RECEIPT
 * ======================================================
 */
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mjsolano@tip.edu.ph';
        $mail->Password = 'vgzc jlou senz jnfu'; // your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('mjsolano@tip.edu.ph', 'TIP KainTeen');
        $mail->addAddress($email, $student_number);
        $mail->isHTML(true);
        $mail->Subject = "Your TIP KainTeen Receipt - Order #{$order_id}";
        $mail->Body = "
            <p>Hi " . htmlspecialchars($student_number) . ",</p>
            <p>Thank you for ordering! Attached is your official receipt.</p>
            <p><strong>Order Total:</strong> ₱" . number_format($total_price, 2) . "</p>
            <p>Enjoy your day!</p>
        ";
        $mail->addAttachment($pdfPath, "receipt_{$order_id}.pdf");
        $mail->send();
        error_log("✅ Email sent successfully to {$email}");
    } catch (Exception $e) {
        error_log("❌ Mailer Error: " . $mail->ErrorInfo);
    }
} else {
    error_log("❌ Invalid email format for student_number {$student_number}");
}

/**
 * ======================================================
 *  CLEAN UP & RESPONSE
 * ======================================================
 */
unset($_SESSION['cart']);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'message' => "Order placed successfully! Your receipt has been sent to your email.",
    'order_id' => $order_id
]);
exit;
?>

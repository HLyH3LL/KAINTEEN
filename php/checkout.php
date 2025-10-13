<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ======================================================
// SESSION VALIDATION
// ======================================================
if (!isset($_SESSION['student_number']) || !isset($_SESSION['student_name'])) {
    echo "<h3>⚠️ Session expired. Please log in again.</h3>";
    exit;
}

// ======================================================
// READ CART FROM POST
// ======================================================
if (!isset($_POST['cart'])) {
    echo "<h3>⚠️ Cart data missing.</h3>";
    exit;
}

$cart = json_decode($_POST['cart'], true);
if (!$cart || !is_array($cart)) {
    echo "<h3>⚠️ Invalid cart data.</h3>";
    exit;
}

// ======================================================
// DATABASE + LIBRARIES
// ======================================================
require_once 'db.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$student_number = $_SESSION['student_number'];
$student_email = $_SESSION['student_name']; // contains email
$total_price = 0;
$receipt_rows = '';

$conn->begin_transaction();

try {
    // ------------------------------
    // CREATE ORDER
    // ------------------------------
    $stmt = $conn->prepare("INSERT INTO orders (student_number, student_email, total_price) VALUES (?, ?, 0)");
    $stmt->bind_param("ss", $student_number, $student_email);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // ------------------------------
    // PROCESS ITEMS
    // ------------------------------
    foreach ($cart as $pid => $qty) {
        $qty = intval($qty); // ensure numeric
        $stmtP = $conn->prepare("SELECT name, price, stock FROM products WHERE id = ?");
        $stmtP->bind_param("i", $pid);
        $stmtP->execute();
        $prod = $stmtP->get_result()->fetch_assoc();
        if (!$prod) continue;

        if ($prod['stock'] < $qty) {
            throw new Exception("Not enough stock for {$prod['name']}");
        }

        $subtotal = floatval($prod['price']) * $qty;
        $total_price += $subtotal;

        // ------------------------------
        // Add row for receipt
        // ------------------------------
        $receipt_rows .= "<tr>
            <td>" . htmlspecialchars($prod['name']) . "</td>
            <td>{$qty}</td>
            <td>₱" . number_format($prod['price'], 2) . "</td>
            <td>₱" . number_format($subtotal, 2) . "</td>
        </tr>";

        // ------------------------------
        // INSERT ORDER ITEM
        // ------------------------------
        $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmtItem->bind_param("iiid", $order_id, $pid, $qty, $prod['price']);
        $stmtItem->execute();

        // ------------------------------
        // REDUCE STOCK
        // ------------------------------
        $stmtStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmtStock->bind_param("ii", $qty, $pid);
        $stmtStock->execute();
    }

    // ------------------------------
    // UPDATE TOTAL PRICE
    // ------------------------------
    $stmtTotal = $conn->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
    $stmtTotal->bind_param("di", $total_price, $order_id);
    $stmtTotal->execute();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    echo "<h3>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
    exit;
}

// ======================================================
// GENERATE PDF RECEIPT
// ======================================================
$html = "
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<title>TIP KainTeen Receipt</title>
<style>
body { font-family: DejaVu Sans, sans-serif; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #000; padding: 6px; text-align: left; }
</style>
</head>
<body>
<h2 style='text-align:center;'>TIP KainTeen Receipt</h2>
<p><strong>Student Number:</strong> " . htmlspecialchars($student_number) . "</p>
<p><strong>Email:</strong> " . htmlspecialchars($student_email) . "</p>
<p><strong>Order ID:</strong> {$order_id}</p>
<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>
<table>
<thead>
<tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>
</thead>
<tbody>
{$receipt_rows}
</tbody>
</table>
<h3 style='text-align:right;'>Total: ₱" . number_format($total_price, 2) . "</h3>
<p style='text-align:center;'>Thank you for ordering from TIP KainTeen!</p>
</body>
</html>
";

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans'); // ensures ₱ works
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$pdfDir = __DIR__ . '/../receipts/';
if (!is_dir($pdfDir)) mkdir($pdfDir, 0755, true);
$pdfPath = $pdfDir . "receipt_{$order_id}.pdf";
file_put_contents($pdfPath, $dompdf->output());

// ======================================================
// SEND EMAIL RECEIPT
// ======================================================
if (filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mjsolano@tip.edu.ph';
        $mail->Password = 'vgzc jlou senz jnfu'; // app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('mjsolano@tip.edu.ph', 'TIP KainTeen');
        $mail->addAddress($student_email, $student_number);
        $mail->isHTML(true);
        $mail->Subject = "Your TIP KainTeen Receipt - Order #{$order_id}";
        $mail->Body = "<p>Hi " . htmlspecialchars($student_number) . ",</p>
                       <p>Thank you for ordering! Attached is your receipt.</p>
                       <p><strong>Order Total:</strong> ₱" . number_format($total_price, 2) . "</p>";
        $mail->addAttachment($pdfPath, "receipt_{$order_id}.pdf");
        $mail->send();
    } catch (Exception $e) {
        error_log("Email error: " . $mail->ErrorInfo);
    }
}

// ======================================================
// OUTPUT RECEIPT PAGE
// ======================================================
unset($_SESSION['cart']); // clear cart

echo "
<html><head><title>Checkout Complete</title>
<style>
body { font-family: Arial; background:#f9f9f9; text-align:center; padding:40px; }
.box { background:white; padding:30px; border-radius:10px; display:inline-block; box-shadow:0 2px 6px rgba(0,0,0,0.2); }
</style></head><body>
<div class='box'>
<h2>✅ Order #{$order_id} Confirmed!</h2>
<p>Total: <strong>₱" . number_format($total_price, 2) . "</strong></p>
<p>A receipt has been emailed to <strong>" . htmlspecialchars($student_email) . "</strong>.</p>
<p><a href='../receipts/receipt_{$order_id}.pdf' target='_blank'>Download PDF Receipt</a></p>
</div>
</body></html>";
exit;
?>

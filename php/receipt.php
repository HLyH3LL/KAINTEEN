<?php
require '../vendor/autoload.php';
include 'db.php';

use Dompdf\Dompdf;

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/signInStudent.html');
    exit;
}

$order_id = intval($_GET['order_id'] ?? 0);
if (!$order_id) {
    echo "Invalid order ID.";
    exit;
}

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "Order not found.";
    exit;
}

// Fetch order items
$stmt = $conn->prepare("
    SELECT oi.quantity, oi.price, p.name 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();

$html = "<h2>Order Receipt</h2>";
$html .= "<p>Order #{$order['id']}</p>";
$html .= "<ul>";
while ($item = $items_result->fetch_assoc()) {
    $subtotal = $item['price'] * $item['quantity'];
    $html .= "<li>" . htmlspecialchars($item['name']) . " x {$item['quantity']} = ₱" . number_format($subtotal, 2) . "</li>";
}
$html .= "</ul>";
$html .= "<p><strong>Total: ₱" . number_format($order['total_price'], 2) . "</strong></p>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("receipt_order_{$order['id']}.pdf", ["Attachment" => false]);
exit;

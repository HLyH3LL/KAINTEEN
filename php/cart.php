<?php
session_start();
include 'db.php';

$cart = $_SESSION['cart'] ?? [];
$total = 0;
$items = [];

if ($cart) {
    $ids = implode(',', array_keys($cart));
    $sql = "SELECT * FROM products WHERE id IN ($ids)";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $row['quantity'] = $cart[$row['id']];
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $total += $row['subtotal'];
        $items[] = $row;
    }
}
?>

<h2>Your Cart</h2>
<?php foreach($items as $item): ?>
  <p><?= $item['name']; ?> x <?= $item['quantity']; ?> = ₱<?= number_format($item['subtotal'], 2); ?></p>
<?php endforeach; ?>
<p><strong>Total: ₱<?= number_format($total, 2); ?></strong></p>

<form action="checkout.php" method="post">
  <button type="submit">Confirm Order</button>
</form>
<a href="student_dashboard.php">← Back</a>

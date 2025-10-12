<?php
session_start();
include 'db.php';

$cart = $_SESSION['cart'] ?? [];
$total = 0;
$items = [];

if ($cart) {
    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $types = str_repeat('i', count($cart));
    $stmt->bind_param($types, ...array_keys($cart));
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $row['quantity'] = $cart[$row['id']];
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $total += $row['subtotal'];
        $items[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Your Cart</title>
</head>
<body>
  <h2>Your Cart</h2>

  <?php if (empty($items)): ?>
    <p>Your cart is empty.</p>
  <?php else: ?>
    <?php foreach($items as $item): ?>
      <p><?= htmlspecialchars($item['name']); ?> x <?= (int)$item['quantity']; ?> = ₱<?= number_format($item['subtotal'], 2); ?></p>
    <?php endforeach; ?>
    <p><strong>Total: ₱<?= number_format($total, 2); ?></strong></p>

    <form action="checkout.php" method="post">
      <?php foreach ($items as $item): ?>
        <input type="hidden" name="cart[<?= (int)$item['id']; ?>]" value="<?= (int)$item['quantity']; ?>" />
      <?php endforeach; ?>
      <button type="submit">Place Order & Download Receipt</button>
    </form>
  <?php endif; ?>

  <a href="dashboard.php">← Back</a>
</body>
</html>

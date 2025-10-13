<?php
session_start();
require_once 'db.php';

// If cart submitted from dashboard (popup)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart'])) {
    $_SESSION['cart'] = $_POST['cart'];
}

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
        $row['quantity'] = $cart[$row['id']] ?? 1;
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $total += $row['subtotal'];
        $items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your Cart</title>
<style>
body{font-family:Arial,sans-serif;background:#fafafa;margin:20px;}
h2{text-align:center;}
.cart-item{border-bottom:1px solid #ddd;padding:10px 0;display:flex;justify-content:space-between;}
.total{font-weight:bold;text-align:right;margin-top:15px;}
.btn{display:inline-block;background:#007bff;color:white;padding:10px 20px;border:none;border-radius:5px;text-decoration:none;cursor:pointer;}
.btn:hover{background:#0056b3;}
</style>
</head>
<body>

<h2>Your Cart</h2>

<?php if(empty($items)): ?>
    <p>Your cart is empty.</p>
    <a class="btn" href="dashboard.php">← Back to Menu</a>
<?php else: ?>
    <?php foreach($items as $item): ?>
        <div class="cart-item">
            <span><?= htmlspecialchars($item['name']); ?> (x<?= (int)$item['quantity']; ?>)</span>
            <span>₱<?= number_format($item['subtotal'],2); ?></span>
        </div>
    <?php endforeach; ?>

    <p class="total">Total: ₱<?= number_format($total,2); ?></p>

    <form action="checkout.php" method="post">
        <?php foreach($items as $item): ?>
            <input type="hidden" name="cart[<?= (int)$item['id']; ?>]" value="<?= (int)$item['quantity']; ?>">
        <?php endforeach; ?>
        <button type="submit" class="btn">Place Order & Download Receipt</button>
    </form>

    <a class="btn" href="dashboard.php">← Back to Menu</a>
<?php endif; ?>

</body>
</html>

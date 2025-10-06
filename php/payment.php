<?php
session_start();
include 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/signInStudent.html');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
    echo "Your cart is empty!";
    exit;
}

$student_id = $_SESSION['student_id'] ?? null;
$student_number = $_SESSION['student_number'] ?? null;

if (!$student_id || !$student_number) {
    echo "User info missing.";
    exit;
}

// Fetch products info
$ids = implode(',', array_keys($cart));
$sql = "SELECT * FROM products WHERE id IN ($ids)";
$result = $conn->query($sql);

$items = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $qty = $cart[$row['id']];
    $subtotal = $row['price'] * $qty;
    $items[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'price' => $row['price'],
        'quantity' => $qty,
        'subtotal' => $subtotal
    ];
    $total += $subtotal;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulate payment success

    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (student_number, total_price) VALUES (?, ?)");
    $stmt->bind_param("sd", $student_number, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Insert order items and update stock
    foreach ($items as $item) {
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
        $stmt->execute();

        // Decrement stock
        $stmt2 = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt2->bind_param("ii", $item['quantity'], $item['id']);
        $stmt2->execute();
    }

    // Clear cart
    unset($_SESSION['cart']);

    // Redirect to receipt
    header("Location: receipt.php?order_id=$order_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Payment</title>
</head>
<body>
  <h2>Confirm Payment</h2>
  <h3>Order Summary</h3>
  <ul>
    <?php foreach ($items as $item): ?>
      <li><?= htmlspecialchars($item['name']); ?> x <?= $item['quantity']; ?> = ₱<?= number_format($item['subtotal'], 2); ?></li>
    <?php endforeach; ?>
  </ul>
  <p><strong>Total: ₱<?= number_format($total, 2); ?></strong></p>

  <form method="POST" action="">
    <button type="submit">Pay Now</button>
  </form>

  <a href="studentDashboard.php">Back to Menu</a>
</body>
</html>

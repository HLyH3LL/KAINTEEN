<?php
session_start();
require_once 'db.php';

// Make sure logged in
if (!isset($_SESSION['student_number'])) {
    header('Location: ../auth/signInStudent.html');
    exit;
}

$student_number = $_SESSION['student_number'];
$student_email  = $_SESSION['student_name']; // email

// Fetch categories
$catStmt = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $catStmt->fetch_all(MYSQLI_ASSOC);

// Fetch products
$stmt = $conn->prepare("
    SELECT p.id, p.name, p.category_id, p.price, p.stock, p.image, c.name AS category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.stock > 0
    ORDER BY p.name ASC
");
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Initialize cart in session if not already
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Student Dashboard — TIP KainTeen</title>
<link rel="shortcut icon" href="../res/logo.png" type="image/x-icon">
<link rel="stylesheet" href="../css/studentDashboard.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
.checkout-modal {
  position: fixed; top:0; left:0; width:100%; height:100%;
  background: rgba(0,0,0,0.6); display:flex;
  align-items:center; justify-content:center; z-index:999;
}
.checkout-content {
  background:white; padding:20px; border-radius:12px; width:300px; text-align:center;
}
.checkout-content button { margin:8px; padding:8px 16px; }
</style>
</head>
<body>
<video class="bg-video" autoplay muted loop>
  <source src="../res/bg.mp4" type="video/mp4" />
</video>

<nav class="topbar">
  <div class="brand">TIP KainTeen</div>
  <div class="user">
    <span>Welcome, <strong><?= htmlspecialchars($student_email) ?></strong></span>
    <a class="btn-ghost" href="../php/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</nav>

<main class="layout">
  <aside class="sidebar">
    <div class="card">
      <h3>Categories</h3>
      <ul id="categories">
        <li data-cat="all" class="cat active">All</li>
        <?php foreach ($categories as $cat): ?>
          <li data-cat="<?= $cat['id'] ?>" class="cat"><?= htmlspecialchars($cat['name']) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="card cart-card">
      <h3>Your Cart</h3>
      <div id="cart-items"><p>Your cart is empty.</p></div>
      <div class="cart-footer">
        <strong>Total: ₱<span id="cart-total">0.00</span></strong>
        <button class="btn-primary" id="checkoutBtn">Checkout</button>
      </div>
    </div>
  </aside>

  <section class="content">
    <h2>Menu</h2>
    <div class="grid" id="menu-grid">
      <?php foreach ($products as $p): ?>
        <article class="menu-card" data-category="<?= htmlspecialchars($p['category_id']) ?>">
          <img src="<?= htmlspecialchars($p['image'] ?: '../res/noimage.png') ?>" alt="<?= htmlspecialchars($p['name']) ?>" />
          <div class="menu-body">
            <h4><?= htmlspecialchars($p['name']) ?></h4>
            <p class="muted"><?= htmlspecialchars($p['category_name']) ?></p>
            <div class="price">₱<?= number_format($p['price'], 2) ?></div>
            <div class="actions">
              <input type="number" min="1" max="<?= (int)$p['stock'] ?>" value="1" class="qty" />
              <button class="btn-add" data-id="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>" data-price="<?= $p['price'] ?>">Add</button>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<!-- Checkout Popup -->
<div id="checkoutPopup" class="checkout-modal" style="display:none;">
  <div class="checkout-content">
    <h2>Confirm Checkout</h2>
    <p>Are you sure you want to proceed?</p>
    <form id="checkoutForm" method="POST" action="checkout.php" target="_blank">
      <input type="hidden" name="cart" id="checkoutCart">
      <button type="submit" class="btn-primary">Yes, Checkout</button>
      <button type="button" class="btn-secondary" onclick="closeCheckout()">Cancel</button>
    </form>
  </div>
</div>

<script>
let cart = <?= json_encode(array_map('intval', $_SESSION['cart'])) ?>;

// Add to cart buttons
document.querySelectorAll('.btn-add').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    const qtyInput = btn.parentElement.querySelector('.qty');
    const qty = parseInt(qtyInput.value) || 1;

    cart[id] = (cart[id] || 0) + qty;
    updateCartUI();
  });
});

function updateCartUI() {
  const cartItems = document.getElementById('cart-items');
  const cartTotal = document.getElementById('cart-total');
  cartItems.innerHTML = '';
  let total = 0;

  if (Object.keys(cart).length === 0) {
    cartItems.innerHTML = '<p>Your cart is empty.</p>';
    cartTotal.textContent = '0.00';
    return;
  }

  for (const id in cart) {
    const item = document.querySelector(`.btn-add[data-id='${id}']`);
    const name = item.dataset.name;
    const price = parseFloat(item.dataset.price);
    const qty = cart[id];
    const subtotal = price * qty;
    total += subtotal;

    const div = document.createElement('div');
    div.textContent = `${name} x ${qty} = ₱${subtotal.toFixed(2)}`;
    cartItems.appendChild(div);
  }

  cartTotal.textContent = total.toFixed(2);
}

// Checkout popup
const checkoutBtn = document.getElementById('checkoutBtn');
const checkoutPopup = document.getElementById('checkoutPopup');
const checkoutCart = document.getElementById('checkoutCart');

checkoutBtn.addEventListener('click', () => {
  if (Object.keys(cart).length === 0) {
    alert('Your cart is empty!');
    return;
  }
  checkoutCart.value = JSON.stringify(cart);
  checkoutPopup.style.display = 'flex';
});

function closeCheckout() {
  checkoutPopup.style.display = 'none';
}

updateCartUI();
</script>

</body>
</html>

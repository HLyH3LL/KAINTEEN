<?php
session_start();
require_once 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['student_number'])) {
    header('Location: ../auth/signInStudent.html');
    exit;
}

// Fix session variables based on your login script
$student_number = $_SESSION['student_number'];
$student_email = $_SESSION['student_name'];  // Actually contains the email as per login

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
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Student Dashboard — TIP KainTeen</title>
<link rel="shortcut icon" href="../res/logo.png" type="image/x-icon" />
<link rel="stylesheet" href="../css/studentDashboard.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
</head>
<body>
<!-- Background Video -->
<video class="bg-video" autoplay muted loop>
  <source src="../res/bg.mp4" type="video/mp4" />
</video>

<!-- Top Navbar -->
<nav class="topbar">
  <div class="brand">TIP KainTeen</div>
  <div class="user">
    <span>Welcome, <strong id="student-email"><?= htmlspecialchars($student_email) ?></strong></span>
    <a class="btn-ghost" href="../php/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</nav>

<!-- Main Layout -->
<main class="layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <!-- Categories -->
    <div class="card">
      <h3>Categories</h3>
      <ul id="categories">
        <li data-cat="all" class="cat active">All</li>
        <?php foreach ($categories as $cat): ?>
          <li data-cat="<?= $cat['id'] ?>" class="cat"><?= htmlspecialchars($cat['name']) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <!-- Cart -->
    <div class="card cart-card">
      <h3>Your Cart</h3>
      <div id="cart-items"><p>Your cart is empty.</p></div>
      <div class="cart-footer">
        <strong>Total: ₱<span id="cart-total">0.00</span></strong>
        <button id="checkoutBtn" class="btn-primary">Checkout</button>
      </div>
    </div>

    <!-- Order History -->
    <div class="card">
      <h3>Order History</h3>
      <div id="order-history">(Your previous orders will appear here)</div>
    </div>
  </aside>

  <!-- Content Section -->
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

<script src="../js/studentDashboard.js"></script>
</body>
</html>

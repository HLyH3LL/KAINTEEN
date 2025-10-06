<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: ../auth/signInStudent.html");
    exit;
}
include '../php/db.php';
$student_name = $_SESSION['student_name'];
$student_number = $_SESSION['student_number'];

$sql = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.stock > 0";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard</title>
  <link rel="stylesheet" href="../css/studentDashboard.css">
</head>
<body>
  <h2>Welcome, <?= htmlspecialchars($student_name); ?> (<?= htmlspecialchars($student_number); ?>)</h2>
  <div id="menu">
    <?php while($row = $result->fetch_assoc()): ?>
      <div class="product-card">
        <img src="<?= $row['image_path']; ?>" alt="<?= $row['name']; ?>">
        <h3><?= $row['name']; ?></h3>
        <p>Category: <?= $row['category_name']; ?></p>
        <p>Price: ₱<?= number_format($row['price'], 2); ?></p>
        <form method="POST" action="add_to_cart.php">
          <input type="hidden" name="product_id" value="<?= $row['id']; ?>">
          <input type="number" name="quantity" min="1" max="<?= $row['stock']; ?>" value="1">
          <button type="submit">Add to Cart</button>
        </form>
      </div>
    <?php endwhile; ?>
  </div>
  <a href="cart.php">View Cart</a> |
  <a href="logout.php">Logout</a>
</body>
</html>

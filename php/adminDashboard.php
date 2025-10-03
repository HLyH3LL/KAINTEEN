<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../html/adminLogin.html"); // ✅ fixed path
    exit();
}

include 'db.php';

// Get total items
$result_items = mysqli_query($conn, "SELECT COUNT(*) AS total_items FROM inventory");
$total_items = mysqli_fetch_assoc($result_items)['total_items'];

// Get total stock
$result_stock = mysqli_query($conn, "SELECT SUM(stock) AS total_stock FROM inventory");
$total_stock = mysqli_fetch_assoc($result_stock)['total_stock'];

// Get low stock items (stock less than 5)
$low_stock_query = mysqli_query($conn, "SELECT * FROM inventory WHERE stock < 5 LIMIT 5");

// Get latest 5 items
$recent_items = mysqli_query($conn, "SELECT * FROM inventory ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../css/adminDashboard.css">
  <link rel="stylesheet" href="../css/adminLogout.css">
  <link rel="stylesheet" href="../css/adminOrders.css">
  <link rel="stylesheet" href="../css/adminInventory.css">
</head>
<body>
  <div class="sidebar">
    <img src="../res/logo.png" alt="Logo">
    <h2>ADMIN</h2>
    <a href="adminDashboard.php" class="active">Overview</a>
    <a href="adminOrders.php">Orders</a>
    <a href="adminInventory.php">Inventory</a>
    <a href="adminPayments.php">Payments</a>
    <a href="adminEarnings.php">Earnings</a>
    <div class="logout" onclick="openModal()">Log out</div>
  </div>

  <div class="main">
    <h2>DASHBOARD OVERVIEW</h2>
    
    <!-- Summary cards -->
    <div class="cards">
      <div class="card">
        <h3>Total Items</h3>
        <p><?php echo $total_items; ?></p>
      </div>
      <div class="card">
        <h3>Total Stock</h3>
        <p><?php echo $total_stock ? $total_stock : 0; ?></p>
      </div>
      <div class="card">
        <h3>Low Stock Alerts</h3>
        <p>
          <?php 
            $low_count = mysqli_num_rows($low_stock_query);
            echo $low_count > 0 ? $low_count : "None";
          ?>
        </p>
      </div>
    </div>

    <!-- Recent items -->
    <div class="section">
      <h3>Recently Added Items</h3>
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Category</th>
            <th>Stock</th>
            <th>Price</th>
            <th>Added</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($recent_items) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($recent_items)): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td><?php echo $row['stock']; ?></td>
                <td>₱<?php echo number_format($row['price'], 2); ?></td>
                <td><?php echo $row['created_at']; ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="5">No recent items</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Low stock items list -->
    <div class="section">
      <h3>Low Stock Items</h3>
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Stock</th>
            <th>Category</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($low_stock_query) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($low_stock_query)): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td style="color:red; font-weight:bold;"><?php echo $row['stock']; ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="3">No low stock items</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Logout Modal -->
 <button onclick="openModal()">Log Out</button>

<!-- Logout Modal -->
<div id="logoutModal" class="modal">
  <div class="modal-content">
    <h3>Are you sure you want to log out?</h3>
    <div class="modal-buttons">
      <button class="btn btn-yes" onclick="logout()">Yes</button>
      <button class="btn btn-cancel" onclick="closeModal()">Cancel</button>
    </div>
  </div>
</div>

  <script src="../js/adminLogout.js"></script>
</body>
</html>
<?php
mysqli_close($conn); // ✅ moved to bottom
?>

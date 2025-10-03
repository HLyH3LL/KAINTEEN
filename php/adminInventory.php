<?php
include 'db.php';

// Handle Add Item
if (isset($_POST['addItem'])) {
    $name = $_POST['addName'];
    $price = $_POST['addPrice'];
    $stock = $_POST['addStock'];
    $category = $_POST['addCategory'];
    $description = $_POST['addDescription'];

    $sql = "INSERT INTO inventory (name, description, category, price, stock) 
            VALUES ('$name', '$description', '$category', '$price', '$stock')";
    mysqli_query($conn, $sql);
    header("Location: adminInventory.php"); 
    exit;
}

// Handle Edit Item
if (isset($_POST['editItem'])) {
    $id = $_POST['editID'];
    $name = $_POST['editName'];
    $price = $_POST['editPrice'];
    $stock = $_POST['editStock'];
    $category = $_POST['editCategory'];
    $description = $_POST['editDescription'];

    $sql = "UPDATE inventory 
            SET name='$name', description='$description', category='$category', price='$price', stock='$stock' 
            WHERE id=$id";
    mysqli_query($conn, $sql);
    header("Location: adminInventory.php");
    exit;
}

// Fetch all inventory items
$result = mysqli_query($conn, "SELECT * FROM inventory ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Inventory</title>
  <link rel="stylesheet" href="../css/adminDashboard.css">
  <link rel="stylesheet" href="../css/adminLogout.css">
  <link rel="stylesheet" href="../css/adminOrders.css">
  <link rel="stylesheet" href="../css/adminInventory.css">
</head>
<body>
  <div class="sidebar">
    <img src="../res/logo.png" alt="Logo">
    <h2>ADMIN</h2>
    <a href="adminDashboard.php">Overview</a>
    <a href="adminOrders.php">Orders</a>
    <a href="adminInventory.php" class="active">Inventory</a>
    <a href="adminPayments.php">Payments</a>
    <a href="adminEarnings.php">Earnings</a>
    <div class="logout" onclick="openModal()">Log out</div>
  </div>

  <div class="main">
    <div class="orders">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2>INVENTORY</h2>
        <button class="btn-add" onclick="openAddModal()">+ Add Item</button>
      </div>

      <!-- Inventory Table -->
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Item Name</th>
            <th>Description</th>
            <th>Category</th>
            <th>Stock</th>
            <th>Price</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td><?php echo $row['stock']; ?></td>
                <td>₱<?php echo number_format($row['price'], 2); ?></td>
                <td>
                  <button class="btn-view" 
                          onclick="openInventoryModal('<?php echo $row['id']; ?>',
                                                      '<?php echo htmlspecialchars($row['name']); ?>',
                                                      '<?php echo htmlspecialchars($row['description']); ?>',
                                                      '<?php echo $row['category']; ?>',
                                                      '<?php echo $row['stock']; ?>',
                                                      '<?php echo $row['price']; ?>')">
                    Edit
                  </button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="7">No items found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Edit Modal -->
      <div id="inventoryModal" class="modal">
        <div class="modal-content add-form">
          <span class="close" onclick="closeInventoryModal()">&times;</span>
          <h2 class="modal-title">EDIT ITEM</h2>
          <form method="POST">
            <input type="hidden" id="editID" name="editID">

            <label for="editName">ITEM NAME</label>
            <input type="text" id="editName" name="editName" required>

            <label for="editDescription">DESCRIPTION</label>
            <textarea id="editDescription" name="editDescription" required></textarea>

            <label for="editCategory">CATEGORY</label>
            <select id="editCategory" name="editCategory" required>
              <option value="Food">Meals</option>
              <option value="Snacks">Snacks</option>
              <option value="Drinks">Drinks</option>
              <option value="School Supplies">School Supplies</option>
            </select>

            <label for="editStock">STOCK</label>
            <input type="number" id="editStock" name="editStock" required>

            <label for="editPrice">PRICE</label>
            <input type="number" id="editPrice" name="editPrice" required>

            <button type="submit" name="editItem" class="btn-submit">
              ✔ SAVE ITEM
            </button>
          </form>
        </div>
      </div>

      <!-- Add Modal -->
      <div id="addModal" class="modal">
        <div class="modal-content add-form">
          <span class="close" onclick="closeAddModal()">&times;</span>
          <h2 class="modal-title">ADD ITEM</h2>
          <form method="POST">
            <label for="addName">Item Name</label>
            <input type="text" id="addName" name="addName" required>

            <label for="addDescription">Description</label>
            <textarea id="addDescription" name="addDescription" required></textarea>

            <label for="addCategory">Category</label>
            <select id="addCategory" name="addCategory" required>
              <option value="Food">Meals</option>
              <option value="Snacks">Snacks</option>
              <option value="Drinks">Drinks</option>
              <option value="School Supplies">School Supplies</option>
            </select>

            <label for="addStock">Stock</label>
            <input type="number" id="addStock" name="addStock" required>

            <label for="addPrice">Price</label>
            <input type="number" id="addPrice" name="addPrice" required>

            <button type="submit" name="addItem" class="btn-submit">
              + ADD ITEM
            </button>
          </form>
        </div>
      </div>

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
    </div>
  </div>

  <script src="../js/adminLogout.js"></script>
  <script>
    function openInventoryModal(id, name, description, category, stock, price) {
      document.getElementById("editID").value = id;
      document.getElementById("editName").value = name;
      document.getElementById("editDescription").value = description;
      document.getElementById("editCategory").value = category;
      document.getElementById("editStock").value = stock;
      document.getElementById("editPrice").value = price;
      document.getElementById("inventoryModal").style.display = "block";
    }
    function closeInventoryModal() {
      document.getElementById("inventoryModal").style.display = "none";
    }

    function openAddModal() {
      document.getElementById("addModal").style.display = "block";
    }
    function closeAddModal() {
      document.getElementById("addModal").style.display = "none";
    }
  </script>
</body>
</html>

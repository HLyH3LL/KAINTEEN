<?php
session_start();
include 'db.php';

// Admin Authentication Check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Fetch categories for dropdowns
$categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name");
$categories = [];
if ($categories_result) {
    while ($cat = $categories_result->fetch_assoc()) {
        $categories[$cat['id']] = $cat['name'];
    }
}

$error = '';

function uploadImage($fileInputName) {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] === UPLOAD_ERR_NO_FILE) {
        // No file uploaded
        return null;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = $_FILES[$fileInputName]['type'];
    if (!in_array($fileType, $allowedTypes)) {
        return false; // Invalid file type
    }

    $uploadsDir = __DIR__ . '/uploads/'; // Absolute path for uploads directory
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }

    $tmpName = $_FILES[$fileInputName]['tmp_name'];
    $ext = pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;

    $destination = $uploadsDir . $filename;

    if (move_uploaded_file($tmpName, $destination)) {
        // Return relative path for use in <img src="">
        return 'uploads/' . $filename;
    }

    return false;
}

// Handle Add Item
if (isset($_POST['addItem'])) {
    $name = trim($_POST['addName']);
    $price = floatval($_POST['addPrice']);
    $stock = intval($_POST['addStock']);
    $category_id = intval($_POST['addCategory']);
    $description = trim($_POST['addDescription']);

    $imagePath = uploadImage('addImage');
    if ($imagePath === false) {
        $error = "Invalid image file.";
    }

    if ($name && $category_id && $price >= 0 && $stock >= 0 && $description && !$error) {
        $stmt = $conn->prepare("INSERT INTO products (name, description, category_id, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssidis", $name, $description, $category_id, $price, $stock, $imagePath);

        if (!$stmt->execute()) {
            $error = "Error adding item: " . $stmt->error;
        }
        $stmt->close();

        if (!$error) {
            header("Location: adminInventory.php");
            exit;
        }
    } else if (!$error) {
        $error = "Please fill out all fields correctly.";
    }
}

// Handle Edit Item
if (isset($_POST['editItem'])) {
    $id = intval($_POST['editID']);
    $name = trim($_POST['editName']);
    $price = floatval($_POST['editPrice']);
    $stock = intval($_POST['editStock']);
    $category_id = intval($_POST['editCategory']);
    $description = trim($_POST['editDescription']);

    // Fetch current image path
    $currentImage = null;
    $stmtImg = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmtImg->bind_param("i", $id);
    $stmtImg->execute();
    $stmtImg->bind_result($currentImage);
    $stmtImg->fetch();
    $stmtImg->close();

    $imagePath = uploadImage('editImage');
    if ($imagePath === false) {
        $error = "Invalid image file.";
    }

    if ($id && $name && $category_id && $price >= 0 && $stock >= 0 && $description && !$error) {
        if ($imagePath === null) {
            // keep old image if no new image uploaded
            $imagePath = $currentImage;
        }

        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, category_id=?, price=?, stock=?, image=? WHERE id=?");
        $stmt->bind_param("ssisisi", $name, $description, $category_id, $price, $stock, $imagePath, $id);

        if (!$stmt->execute()) {
            $error = "Error updating item: " . $stmt->error;
        }
        $stmt->close();

        if (!$error) {
            header("Location: adminInventory.php");
            exit;
        }
    } else if (!$error) {
        $error = "Please fill out all fields correctly.";
    }
}

// Handle Delete Item
if (isset($_POST['deleteItem'])) {
    $id = intval($_POST['deleteID']);
    if ($id) {
        // Delete image file if exists
        $stmtImg = $conn->prepare("SELECT image FROM products WHERE id = ?");
        $stmtImg->bind_param("i", $id);
        $stmtImg->execute();
        $stmtImg->bind_result($imagePath);
        $stmtImg->fetch();
        $stmtImg->close();

        if ($imagePath && file_exists(__DIR__ . '/' . $imagePath)) {
            unlink(__DIR__ . '/' . $imagePath);
        }

        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            $error = "Error deleting item: " . $stmt->error;
        }
        $stmt->close();

        if (!$error) {
            header("Location: adminInventory.php");
            exit;
        }
    } else {
        $error = "Invalid item ID for deletion.";
    }
}

// Fetch all products with their category names
$sql = "SELECT p.id, p.name, p.description, p.price, p.stock, p.category_id, p.image, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.id DESC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Inventory</title>
  <link rel="stylesheet" href="../css/adminDashboard.css" />
  <link rel="stylesheet" href="../css/adminLogout.css" />
  <link rel="stylesheet" href="../css/adminOrders.css" />
  <link rel="stylesheet" href="../css/adminInventory.css" />
  <style>
    /* Additional styling for delete button */
    .btn-delete {
      background-color: #d9534f;
      color: white;
      border: none;
      padding: 5px 10px;
      margin-left: 5px;
      cursor: pointer;
      border-radius: 3px;
    }
    .btn-delete:hover {
      background-color: #c9302c;
    }
    table img {
      max-width: 50px;
      max-height: 50px;
      object-fit: cover;
      border-radius: 4px;
    }
    /* Modal styling - basic */
    .modal {
      position: fixed;
      z-index: 1000;
      left: 0; top: 0;
      width: 100%; height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.5);
    }
    .modal-content {
      background-color: #fefefe;
      margin: 5% auto;
      padding: 20px;
      border-radius: 8px;
      width: 400px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      position: relative;
    }
    .close {
      position: absolute;
      right: 15px;
      top: 10px;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
      color: #aaa;
    }
    .close:hover {
      color: black;
    }
    .btn-submit, .btn-add {
      background-color: #28a745;
      color: white;
      border: none;
      padding: 10px 15px;
      cursor: pointer;
      border-radius: 5px;
      font-size: 16px;
    }
    .btn-submit:hover, .btn-add:hover {
      background-color: #218838;
    }
    label {
      display: block;
      margin-top: 10px;
      font-weight: bold;
    }
    input[type=text], input[type=number], textarea, select {
      width: 100%;
      padding: 8px;
      margin-top: 4px;
      box-sizing: border-box;
      border: 1px solid #ccc;
      border-radius: 4px;
      resize: vertical;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <img src="../res/logo.png" alt="Logo" />
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

      <?php if (!empty($error)): ?>
        <div style="color: red; margin-bottom: 1rem;"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <!-- Inventory Table -->
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Item Name</th>
            <th>Description</th>
            <th>Category</th>
            <th>Stock</th>
            <th>Price</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= (int)$row['id'] ?></td>
                <td>
                  <?php if (!empty($row['image']) && file_exists(__DIR__ . '/' . $row['image'])): ?>
                    <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" />
                  <?php else: ?>
                    <img src="../res/placeholder.png" alt="No image" />
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?></td>
                <td><?= (int)$row['stock'] ?></td>
                <td>₱<?= number_format($row['price'], 2) ?></td>
                <td>
                  <button class="btn-view"
                          onclick="openInventoryModal(
                            '<?= (int)$row['id'] ?>',
                            '<?= htmlspecialchars(addslashes($row['name'])) ?>',
                            '<?= htmlspecialchars(addslashes($row['description'])) ?>',
                            '<?= (int)$row['category_id'] ?>',
                            '<?= (int)$row['stock'] ?>',
                            '<?= number_format($row['price'], 2) ?>'
                          )">Edit</button>

                  <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this item?');">
                    <input type="hidden" name="deleteID" value="<?= (int)$row['id'] ?>">
                    <button type="submit" name="deleteItem" class="btn-delete">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="8" style="text-align:center;">No items found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Logout Modal -->
  <div id="logoutModal" class="modal" style="display:none;">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <h3>Logout</h3>
      <p>Are you sure you want to log out?</p>
      <button onclick="location.href='logout.php'" class="btn-submit">Logout</button>
      <button onclick="closeModal()" class="btn-submit" style="background-color: #6c757d; margin-left: 10px;">Cancel</button>
    </div>
  </div>

  <!-- Add Item Modal -->
  <div id="addItemModal" class="modal" style="display:none;">
    <div class="modal-content">
      <span class="close" onclick="closeAddModal()">&times;</span>
      <h3>Add Item</h3>
      <form method="POST" enctype="multipart/form-data" onsubmit="return validateAddForm()">
        <label for="addName">Item Name:</label>
        <input type="text" id="addName" name="addName" required />

        <label for="addDescription">Description:</label>
        <textarea id="addDescription" name="addDescription" rows="3" required></textarea>

        <label for="addCategory">Category:</label>
        <select id="addCategory" name="addCategory" required>
          <option value="">Select category</option>
          <?php foreach ($categories as $catId => $catName): ?>
            <option value="<?= $catId ?>"><?= htmlspecialchars($catName) ?></option>
          <?php endforeach; ?>
        </select>

        <label for="addStock">Stock:</label>
        <input type="number" id="addStock" name="addStock" min="0" required />

        <label for="addPrice">Price:</label>
        <input type="number" id="addPrice" name="addPrice" min="0" step="0.01" required />

        <label for="addImage">Image (jpg, png, gif):</label>
        <input type="file" id="addImage" name="addImage" accept="image/*" />

        <button type="submit" name="addItem" class="btn-submit" style="margin-top:15px;">Add Item</button>
      </form>
    </div>
  </div>

  <!-- Edit Item Modal -->
  <div id="editItemModal" class="modal" style="display:none;">
    <div class="modal-content">
      <span class="close" onclick="closeEditModal()">&times;</span>
      <h3>Edit Item</h3>
      <form method="POST" enctype="multipart/form-data" onsubmit="return validateEditForm()">
        <input type="hidden" id="editID" name="editID" />

        <label for="editName">Item Name:</label>
        <input type="text" id="editName" name="editName" required />

        <label for="editDescription">Description:</label>
        <textarea id="editDescription" name="editDescription" rows="3" required></textarea>

        <label for="editCategory">Category:</label>
        <select id="editCategory" name="editCategory" required>
          <option value="">Select category</option>
          <?php foreach ($categories as $catId => $catName): ?>
            <option value="<?= $catId ?>"><?= htmlspecialchars($catName) ?></option>
          <?php endforeach; ?>
        </select>

        <label for="editStock">Stock:</label>
        <input type="number" id="editStock" name="editStock" min="0" required />

        <label for="editPrice">Price:</label>
        <input type="number" id="editPrice" name="editPrice" min="0" step="0.01" required />

        <label for="editImage">Replace Image (optional):</label>
        <input type="file" id="editImage" name="editImage" accept="image/*" />

        <button type="submit" name="editItem" class="btn-submit" style="margin-top:15px;">Update Item</button>
      </form>
    </div>
  </div>

  <script>
    // Logout Modal
    function openModal() {
      document.getElementById("logoutModal").style.display = "block";
    }
    function closeModal() {
      document.getElementById("logoutModal").style.display = "none";
    }

    // Add Item Modal
    function openAddModal() {
      document.getElementById("addItemModal").style.display = "block";
      // Reset form
      document.querySelector("#addItemModal form").reset();
    }
    function closeAddModal() {
      document.getElementById("addItemModal").style.display = "none";
    }

    // Edit Item Modal
    function openInventoryModal(id, name, description, categoryId, stock, price) {
      document.getElementById("editID").value = id;
      document.getElementById("editName").value = name;
      document.getElementById("editDescription").value = description;
      document.getElementById("editCategory").value = categoryId;
      document.getElementById("editStock").value = stock;
      document.getElementById("editPrice").value = price;
      document.getElementById("editImage").value = null;
      document.getElementById("editItemModal").style.display = "block";
    }
    function closeEditModal() {
      document.getElementById("editItemModal").style.display = "none";
    }

    // Simple form validation (optional enhancement)
    function validateAddForm() {
      const form = document.forms[0];
      if (form.addName.value.trim() === '') {
        alert('Please enter a name.');
        return false;
      }
      if (form.addPrice.value < 0) {
        alert('Price cannot be negative.');
        return false;
      }
      if (form.addStock.value < 0) {
        alert('Stock cannot be negative.');
        return false;
      }
      if (form.addCategory.value === '') {
        alert('Please select a category.');
        return false;
      }
      return true;
    }

    function validateEditForm() {
      const form = document.forms[1];
      if (form.editName.value.trim() === '') {
        alert('Please enter a name.');
        return false;
      }
      if (form.editPrice.value < 0) {
        alert('Price cannot be negative.');
        return false;
      }
      if (form.editStock.value < 0) {
        alert('Stock cannot be negative.');
        return false;
      }
      if (form.editCategory.value === '') {
        alert('Please select a category.');
        return false;
      }
      return true;
    }

    // Close modals when clicking outside content
    window.onclick = function(event) {
      const modals = ['logoutModal', 'addItemModal', 'editItemModal'];
      modals.forEach(id => {
        const modal = document.getElementById(id);
        if (event.target == modal) {
          modal.style.display = "none";
        }
      });
    }
  </script>
</body>
</html>

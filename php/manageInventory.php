<?php
session_start();

// Check if admin is logged in (you'll need to implement admin authentication)
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminLogin.php');
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'kainteen_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("
                    INSERT INTO products (name, description, price, category, stock, image_path) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['category'],
                    $_POST['stock'],
                    $_POST['image_path'] ?? null
                ]);
                $message = "Product added successfully!";
                break;
                
            case 'update':
                $stmt = $pdo->prepare("
                    UPDATE products 
                    SET name = ?, description = ?, price = ?, category = ?, stock = ?, image_path = ? 
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['category'],
                    $_POST['stock'],
                    $_POST['image_path'] ?? null,
                    $_POST['id']
                ]);
                $message = "Product updated successfully!";
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = "Product deleted successfully!";
                break;
        }
    }
}

// Fetch all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY category, name");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Inventory</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        button:hover {
            background: #0056b3;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .edit-form {
            display: none;
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-boxes"></i> Manage Inventory</h1>
        
        <?php if (isset($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Add Product Form -->
        <div class="form-section">
            <h3><i class="fas fa-plus"></i> Add New Product</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="name">Product Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="price">Price (₱):</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="meals">Meals</option>
                        <option value="snacks">Snacks</option>
                        <option value="drinks">Drinks</option>
                        <option value="school-supplies">School Supplies</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="stock">Stock Quantity:</label>
                    <input type="number" id="stock" name="stock" min="0" required>
                </div>
                <div class="form-group">
                    <label for="image_path">Image Path (optional):</label>
                    <input type="text" id="image_path" name="image_path" placeholder="filename.jpg">
                </div>
                <button type="submit" class="btn-success">Add Product</button>
            </form>
        </div>
        
        <!-- Products Table -->
        <h3><i class="fas fa-list"></i> Current Inventory</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td>₱<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo ucfirst($product['category']); ?></td>
                        <td><?php echo $product['stock']; ?></td>
                        <td><?php echo $product['image_path'] ?: 'No image'; ?></td>
                        <td class="action-buttons">
                            <button onclick="showEditForm(<?php echo $product['id']; ?>)" class="btn-success">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <tr class="edit-form" id="edit-form-<?php echo $product['id']; ?>">
                        <td colspan="8">
                            <form method="POST">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                                    <div class="form-group">
                                        <label>Name:</label>
                                        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Description:</label>
                                        <textarea name="description" rows="2" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Price:</label>
                                        <input type="number" name="price" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Category:</label>
                                        <select name="category" required>
                                            <option value="meals" <?php echo $product['category'] === 'meals' ? 'selected' : ''; ?>>Meals</option>
                                            <option value="snacks" <?php echo $product['category'] === 'snacks' ? 'selected' : ''; ?>>Snacks</option>
                                            <option value="drinks" <?php echo $product['category'] === 'drinks' ? 'selected' : ''; ?>>Drinks</option>
                                            <option value="school-supplies" <?php echo $product['category'] === 'school-supplies' ? 'selected' : ''; ?>>School Supplies</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Stock:</label>
                                        <input type="number" name="stock" min="0" value="<?php echo $product['stock']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Image Path:</label>
                                        <input type="text" name="image_path" value="<?php echo $product['image_path'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div style="margin-top: 15px;">
                                    <button type="submit" class="btn-success">Update Product</button>
                                    <button type="button" onclick="hideEditForm(<?php echo $product['id']; ?>)" class="btn-danger">Cancel</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <script>
        function showEditForm(id) {
            document.getElementById(`edit-form-${id}`).style.display = 'table-row';
        }
        
        function hideEditForm(id) {
            document.getElementById(`edit-form-${id}`).style.display = 'none';
        }
    </script>
</body>
</html>
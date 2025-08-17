<?php
// Enable CORS for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
$host = 'localhost';
$dbname = 'kainteen_db';
$username = 'root';
$password = '';

try {
    // Create database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch all products from the admin inventory
    $stmt = $pdo->prepare("
        SELECT 
            id,
            name,
            description,
            price,
            category,
            stock,
            image_path,
            created_at,
            updated_at
        FROM products 
        WHERE stock > 0 
        ORDER BY category, name
    ");
    
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organize products by category
    $organizedProducts = [];
    foreach ($products as $product) {
        $category = $product['category'];
        if (!isset($organizedProducts[$category])) {
            $organizedProducts[$category] = [];
        }
        
        // Format the product data
        $organizedProducts[$category][] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'description' => $product['description'],
            'price' => floatval($product['price']),
            'category' => $product['category'],
            'stock' => intval($product['stock']),
            'image' => $product['image_path'] ? '../uploads/' . $product['image_path'] : '../res/placeholder-food.jpg'
        ];
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'products' => $organizedProducts,
        'message' => 'Products loaded successfully'
    ]);
    
} catch (PDOException $e) {
    // Database error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    
} catch (Exception $e) {
    // General error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
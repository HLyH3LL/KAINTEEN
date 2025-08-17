-- Create products table for admin inventory
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category ENUM('meals', 'snacks', 'drinks', 'school-supplies') NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO products (name, description, price, category, stock, image_path) VALUES
-- Meals
('Menudo', 'Traditional Filipino pork stew with vegetables', 65.00, 'meals', 50, 'menudo.jpg'),
('Adobo', 'Classic Filipino adobo with rice', 70.00, 'meals', 45, 'adobo.jpg'),
('Sinigang', 'Sour tamarind soup with pork and vegetables', 75.00, 'meals', 30, 'sinigang.jpg'),
('Kare-kare', 'Peanut stew with beef and vegetables', 80.00, 'meals', 25, 'kare-kare.jpg'),
('Pancit Canton', 'Stir-fried noodles with vegetables and meat', 60.00, 'meals', 40, 'pancit.jpg'),

-- Snacks
('Cupcake', 'Delicious vanilla cupcake with frosting', 17.00, 'snacks', 100, 'cupcake.jpg'),
('Cookies', 'Freshly baked chocolate chip cookies', 15.00, 'snacks', 80, 'cookies.jpg'),
('Brownies', 'Rich chocolate brownies', 20.00, 'snacks', 60, 'brownies.jpg'),
('Pancake', 'Fluffy pancakes with syrup', 25.00, 'snacks', 70, 'pancake.jpg'),
('Donut', 'Glazed donut with sprinkles', 22.00, 'snacks', 55, 'donut.jpg'),

-- Drinks
('Water', '500ml purified drinking water', 10.00, 'drinks', 200, 'water.jpg'),
('Juice', 'Fresh orange juice', 25.00, 'drinks', 75, 'juice.jpg'),
('Coffee', 'Hot brewed coffee', 30.00, 'drinks', 90, 'coffee.jpg'),
('Milk Tea', 'Sweet milk tea with pearls', 35.00, 'drinks', 65, 'milk-tea.jpg'),
('Soda', 'Carbonated soft drink', 20.00, 'drinks', 120, 'soda.jpg'),

-- School Supplies
('Notebook', 'A4 size notebook with 100 pages', 45.00, 'school-supplies', 150, 'notebook.jpg'),
('Pen', 'Blue ballpoint pen', 12.00, 'school-supplies', 300, 'pen.jpg'),
('Pencil', 'HB graphite pencil', 8.00, 'school-supplies', 250, 'pencil.jpg'),
('Eraser', 'White eraser', 5.00, 'school-supplies', 200, 'eraser.jpg'),
('Ruler', '30cm plastic ruler', 15.00, 'school-supplies', 100, 'ruler.jpg');
// Admin Inventory Management System
class InventoryManager {
  constructor() {
    this.products = this.loadProducts();
    this.currentEditId = null;
    this.init();
  }

  init() {
    this.displayProducts();
    this.updateDashboardStats();
    this.setupEventListeners();
  }

  // Load products from localStorage
  loadProducts() {
    const stored = localStorage.getItem('kainteen_products');
    if (stored) {
      return JSON.parse(stored);
    }
    
    // Return sample data if no products exist
    return {
      meals: [
        {
          id: 'meal1',
          name: 'Menudo',
          description: 'Traditional Filipino pork stew with vegetables',
          price: 65.00,
          category: 'meals',
          stock: 50,
          image: '../res/placeholder-food.jpg'
        },
        {
          id: 'meal2',
          name: 'Adobo',
          description: 'Classic Filipino adobo with rice',
          price: 70.00,
          category: 'meals',
          stock: 45,
          image: '../res/placeholder-food.jpg'
        }
      ],
      snacks: [
        {
          id: 'snack1',
          name: 'Cupcake',
          description: 'Delicious vanilla cupcake with frosting',
          price: 17.00,
          category: 'snacks',
          stock: 100,
          image: '../res/placeholder-food.jpg'
        }
      ],
      drinks: [
        {
          id: 'drink1',
          name: 'Water',
          description: '500ml purified drinking water',
          price: 10.00,
          category: 'drinks',
          stock: 200,
          image: '../res/placeholder-food.jpg'
        }
      ],
      'school-supplies': [
        {
          id: 'supply1',
          name: 'Notebook',
          description: 'A4 size notebook with 100 pages',
          price: 45.00,
          category: 'school-supplies',
          stock: 150,
          image: '../res/placeholder-food.jpg'
        }
      ]
    };
  }

  // Save products to localStorage
  saveProducts() {
    localStorage.setItem('kainteen_products', JSON.stringify(this.products));
    // Also save to a separate key for student dashboard access
    localStorage.setItem('student_dashboard_products', JSON.stringify(this.products));
  }

  // Add new product
  addProduct(productData) {
    const newId = 'prod_' + Date.now();
    const newProduct = Object.assign({}, productData, {
      id: newId,
      image: productData.image || '../res/placeholder-food.jpg'
    });

    if (!this.products[productData.category]) {
      this.products[productData.category] = [];
    }

    this.products[productData.category].push(newProduct);
    this.saveProducts();
    this.displayProducts();
    this.updateDashboardStats();
    this.showMessage('Product added successfully!', 'success');
  }

  // Update existing product
  updateProduct(productId, productData) {
    // Find and remove the old product
    let found = false;
    for (const category in this.products) {
      const index = this.products[category].findIndex(function(p) { 
        return p.id === productId; 
      });
      if (index !== -1) {
        this.products[category].splice(index, 1);
        found = true;
        break;
      }
    }

    if (found) {
      // Add the updated product
      const updatedProduct = Object.assign({}, productData, {
        id: productId,
        image: productData.image || '../res/placeholder-food.jpg'
      });

      if (!this.products[productData.category]) {
        this.products[productData.category] = [];
      }

      this.products[productData.category].push(updatedProduct);
      this.saveProducts();
      this.displayProducts();
      this.updateDashboardStats();
      this.showMessage('Product updated successfully!', 'success');
    }
  }

  // Delete product
  deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
      for (const category in this.products) {
        const index = this.products[category].findIndex(function(p) { 
          return p.id === productId; 
        });
        if (index !== -1) {
          this.products[category].splice(index, 1);
          break;
        }
      }
      this.saveProducts();
      this.displayProducts();
      this.updateDashboardStats();
      this.showMessage('Product deleted successfully!', 'success');
    }
  }

  // Display products in table
  displayProducts() {
    const tableBody = document.getElementById('products-table-body');
    const searchTerm = document.getElementById('search-products').value.toLowerCase();
    const categoryFilter = document.getElementById('category-filter').value;

    let allProducts = [];
    for (const category in this.products) {
      if (!categoryFilter || category === categoryFilter) {
        allProducts.push(...this.products[category]);
      }
    }

    // Filter by search term
    allProducts = allProducts.filter(product => 
      product.name.toLowerCase().includes(searchTerm) ||
      product.description.toLowerCase().includes(searchTerm)
    );

    if (allProducts.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="7" class="no-products"><i class="fas fa-box-open"></i><p>No products found</p></td></tr>';
      return;
    }

    tableBody.innerHTML = allProducts.map(function(product) {
      return '<tr>' +
        '<td><img src="' + product.image + '" alt="' + product.name + '" class="product-image" onerror="this.src=\'../res/placeholder-food.jpg\'"></td>' +
        '<td><strong>' + product.name + '</strong></td>' +
        '<td>' + product.description + '</td>' +
        '<td><span class="badge">' + product.category.replace('-', ' ').toUpperCase() + '</span></td>' +
        '<td><strong>₱' + parseFloat(product.price).toFixed(2) + '</strong></td>' +
        '<td><span class="stock-status ' + this.getStockStatusClass(product.stock) + '">' + this.getStockStatusText(product.stock) + '</span></td>' +
        '<td>' +
          '<button class="btn-edit" onclick="inventoryManager.editProduct(\'' + product.id + '\')"><i class="fas fa-edit"></i> Edit</button>' +
          '<button class="btn-delete" onclick="inventoryManager.deleteProduct(\'' + product.id + '\')"><i class="fas fa-trash"></i> Delete</button>' +
        '</td>' +
      '</tr>';
    }.bind(this)).join('');
  }

  // Get stock status class
  getStockStatusClass(stock) {
    if (stock === 0) return 'stock-out';
    if (stock <= 10) return 'stock-low';
    if (stock <= 30) return 'stock-medium';
    return 'stock-high';
  }

  // Get stock status text
  getStockStatusText(stock) {
    if (stock === 0) return 'OUT OF STOCK';
    if (stock <= 10) return 'LOW STOCK';
    if (stock <= 30) return 'MEDIUM STOCK';
    return 'IN STOCK';
  }

  // Edit product
  editProduct(productId) {
    // Find the product
    let product = null;
    for (const category in this.products) {
      product = this.products[category].find(p => p.id === productId);
      if (product) break;
    }

    if (product) {
      this.currentEditId = productId;
      document.getElementById('form-title').textContent = 'Edit Product';
      document.getElementById('edit-product-id').value = productId;
      document.getElementById('product-name').value = product.name;
      document.getElementById('product-category').value = product.category;
      document.getElementById('product-price').value = product.price;
      document.getElementById('product-stock').value = product.stock;
      document.getElementById('product-description').value = product.description;
      
      // Show image preview if exists
      if (product.image && product.image !== '../res/placeholder-food.jpg') {
        document.getElementById('image-preview').src = product.image;
        document.getElementById('image-preview').style.display = 'block';
      } else {
        document.getElementById('image-preview').style.display = 'none';
      }

      document.getElementById('product-form').classList.add('active');
      document.querySelector('.add-product-btn').textContent = 'Cancel Edit';
    }
  }

  // Cancel edit mode
  cancelEdit() {
    this.currentEditId = null;
    this.resetForm();
    document.getElementById('product-form').classList.remove('active');
    document.querySelector('.add-product-btn').textContent = 'Add New Product';
  }

  // Reset form
  resetForm() {
    document.getElementById('product-form-element').reset();
    document.getElementById('edit-product-id').value = '';
    document.getElementById('image-preview').style.display = 'none';
    document.getElementById('form-title').textContent = 'Add New Product';
  }

  // Update dashboard statistics
  updateDashboardStats() {
    let totalProducts = 0;
    let lowStockCount = 0;

    for (const category in this.products) {
      totalProducts += this.products[category].length;
      lowStockCount += this.products[category].filter(p => p.stock <= 10).length;
    }

    document.getElementById('total-products').textContent = totalProducts;
    document.getElementById('low-stock-count').textContent = lowStockCount;
  }

  // Show message
  showMessage(message, type) {
    const successMsg = document.getElementById('success-message');
    const errorMsg = document.getElementById('error-message');

    if (type === 'success') {
      successMsg.textContent = message;
      successMsg.style.display = 'block';
      errorMsg.style.display = 'none';
      setTimeout(() => successMsg.style.display = 'none', 3000);
    } else {
      errorMsg.textContent = message;
      errorMsg.style.display = 'block';
      successMsg.style.display = 'none';
      setTimeout(() => errorMsg.style.display = 'none', 3000);
    }
  }

  // Setup event listeners
  setupEventListeners() {
    // Search functionality
    document.getElementById('search-products').addEventListener('input', () => {
      this.displayProducts();
    });

    // Category filter
    document.getElementById('category-filter').addEventListener('change', () => {
      this.displayProducts();
    });
  }
}

// Global functions for HTML onclick events
function toggleProductForm() {
  const form = document.getElementById('product-form');
  const btn = document.querySelector('.add-product-btn');
  
  if (form.classList.contains('active')) {
    // Cancel edit mode
    inventoryManager.cancelEdit();
  } else {
    // Show add form
    form.classList.add('active');
    btn.textContent = 'Cancel';
  }
}

function cancelEdit() {
  inventoryManager.cancelEdit();
}

function saveProduct(event) {
  event.preventDefault();
  
  const formData = {
    name: document.getElementById('product-name').value.trim(),
    description: document.getElementById('product-description').value.trim(),
    price: parseFloat(document.getElementById('product-price').value),
    category: document.getElementById('product-category').value,
    stock: parseInt(document.getElementById('product-stock').value),
    image: document.getElementById('image-preview').src || '../res/placeholder-food.jpg'
  };

  // Validation
  if (!formData.name || !formData.description || !formData.price || !formData.category || formData.stock < 0) {
    inventoryManager.showMessage('Please fill in all required fields correctly.', 'error');
    return;
  }

  if (inventoryManager.currentEditId) {
    // Update existing product
    inventoryManager.updateProduct(inventoryManager.currentEditId, formData);
  } else {
    // Add new product
    inventoryManager.addProduct(formData);
  }

  // Reset form and hide
  inventoryManager.resetForm();
  document.getElementById('product-form').classList.remove('active');
  document.querySelector('.add-product-btn').textContent = 'Add New Product';
}

function previewImage(input) {
  const preview = document.getElementById('image-preview');
  const file = input.files[0];

  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
  } else {
    preview.style.display = 'none';
  }
}

// Initialize inventory manager when page loads
let inventoryManager;
document.addEventListener('DOMContentLoaded', () => {
  inventoryManager = new InventoryManager();
});

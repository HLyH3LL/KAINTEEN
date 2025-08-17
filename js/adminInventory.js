// Admin Inventory Management System (PHP + MySQL Version)
class InventoryManager {
  constructor() {
    this.products = {};
    this.currentEditId = null;
    this.init();
  }

  async init() {
    await this.loadProducts();
    await this.updateDashboardStats();
    this.setupEventListeners();
  }

  // Load products from PHP backend
  async loadProducts() {
    try {
      const response = await fetch("php/get_products.php");
      const data = await response.json();
      this.products = data || {};
      this.displayProducts();
    } catch (error) {
      console.error("Error loading products:", error);
      this.showMessage("Failed to load products.", "error");
    }
  }

  // Add new product (POST)
  async addProduct(productData) {
    try {
      const response = await fetch("php/add_product.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(productData)
      });

      const result = await response.json();
      if (result.success) {
        await this.loadProducts();
        await this.updateDashboardStats();
        this.showMessage("Product added successfully!", "success");
      } else {
        this.showMessage(result.message || "Failed to add product.", "error");
      }
    } catch (err) {
      console.error(err);
      this.showMessage("Server error.", "error");
    }
  }

  // Update product (POST)
  async updateProduct(productId, productData) {
    try {
      const response = await fetch("php/update_product.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: productId, ...productData })
      });

      const result = await response.json();
      if (result.success) {
        await this.loadProducts();
        await this.updateDashboardStats();
        this.showMessage("Product updated successfully!", "success");
      } else {
        this.showMessage(result.message || "Failed to update product.", "error");
      }
    } catch (err) {
      console.error(err);
      this.showMessage("Server error.", "error");
    }
  }

  // Delete product (POST)
  async deleteProduct(productId) {
    if (!confirm("Are you sure you want to delete this product?")) return;

    try {
      const response = await fetch("php/delete_product.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: productId })
      });

      const result = await response.json();
      if (result.success) {
        await this.loadProducts();
        await this.updateDashboardStats();
        this.showMessage("Product deleted successfully!", "success");
      } else {
        this.showMessage(result.message || "Failed to delete product.", "error");
      }
    } catch (err) {
      console.error(err);
      this.showMessage("Server error.", "error");
    }
  }

  // Display products in table
  displayProducts() {
    const tableBody = document.getElementById("products-table-body");
    const searchTerm = document.getElementById("search-products").value.toLowerCase();
    const categoryFilter = document.getElementById("category-filter").value;

    let allProducts = [];
    for (const category in this.products) {
      if (!categoryFilter || category === categoryFilter) {
        allProducts.push(...this.products[category]);
      }
    }

    // Filter by search
    allProducts = allProducts.filter(
      (product) =>
        product.name.toLowerCase().includes(searchTerm) ||
        product.description.toLowerCase().includes(searchTerm)
    );

    if (allProducts.length === 0) {
      tableBody.innerHTML =
        '<tr><td colspan="7" class="no-products"><i class="fas fa-box-open"></i><p>No products found</p></td></tr>';
      return;
    }

    tableBody.innerHTML = allProducts
      .map(
        (product) =>
          `<tr>
            <td><img src="${product.image}" alt="${product.name}" class="product-image" onerror="this.src='../res/placeholder-food.jpg'"></td>
            <td><strong>${product.name}</strong></td>
            <td>${product.description}</td>
            <td><span class="badge">${product.category.replace("-", " ").toUpperCase()}</span></td>
            <td><strong>₱${parseFloat(product.price).toFixed(2)}</strong></td>
            <td><span class="stock-status ${this.getStockStatusClass(product.stock)}">${this.getStockStatusText(product.stock)}</span></td>
            <td>
              <button class="btn-edit" onclick="inventoryManager.editProduct('${product.id}')"><i class="fas fa-edit"></i> Edit</button>
              <button class="btn-delete" onclick="inventoryManager.deleteProduct('${product.id}')"><i class="fas fa-trash"></i> Delete</button>
            </td>
          </tr>`
      )
      .join("");
  }

  // Stock status
  getStockStatusClass(stock) {
    if (stock === 0) return "stock-out";
    if (stock <= 10) return "stock-low";
    if (stock <= 30) return "stock-medium";
    return "stock-high";
  }

  getStockStatusText(stock) {
    if (stock === 0) return "OUT OF STOCK";
    if (stock <= 10) return "LOW STOCK";
    if (stock <= 30) return "MEDIUM STOCK";
    return "IN STOCK";
  }

  // Edit product
  editProduct(productId) {
    let product = null;
    for (const category in this.products) {
      product = this.products[category].find((p) => p.id === productId);
      if (product) break;
    }

    if (product) {
      this.currentEditId = productId;
      document.getElementById("form-title").textContent = "Edit Product";
      document.getElementById("edit-product-id").value = productId;
      document.getElementById("product-name").value = product.name;
      document.getElementById("product-category").value = product.category;
      document.getElementById("product-price").value = product.price;
      document.getElementById("product-stock").value = product.stock;
      document.getElementById("product-description").value = product.description;

      if (product.image) {
        document.getElementById("image-preview").src = product.image;
        document.getElementById("image-preview").style.display = "block";
      } else {
        document.getElementById("image-preview").style.display = "none";
      }

      document.getElementById("product-form").classList.add("active");
      document.querySelector(".add-product-btn").textContent = "Cancel Edit";
    }
  }

  cancelEdit() {
    this.currentEditId = null;
    this.resetForm();
    document.getElementById("product-form").classList.remove("active");
    document.querySelector(".add-product-btn").textContent = "Add New Product";
  }

  resetForm() {
    document.getElementById("product-form-element").reset();
    document.getElementById("edit-product-id").value = "";
    document.getElementById("image-preview").style.display = "none";
    document.getElementById("form-title").textContent = "Add New Product";
  }

  // Dashboard stats
  async updateDashboardStats() {
    try {
      const response = await fetch("php/get_dashboard_stats.php");
      const stats = await response.json();

      document.getElementById("total-products").textContent = stats.total_products || 0;
      document.getElementById("low-stock-count").textContent = stats.low_stock_count || 0;
    } catch (error) {
      console.error("Error loading stats:", error);
    }
  }

  // Messages
  showMessage(message, type) {
    const successMsg = document.getElementById("success-message");
    const errorMsg = document.getElementById("error-message");

    if (type === "success") {
      successMsg.textContent = message;
      successMsg.style.display = "block";
      errorMsg.style.display = "none";
      setTimeout(() => (successMsg.style.display = "none"), 3000);
    } else {
      errorMsg.textContent = message;
      errorMsg.style.display = "block";
      successMsg.style.display = "none";
      setTimeout(() => (errorMsg.style.display = "none"), 3000);
    }
  }

  setupEventListeners() {
    document.getElementById("search-products").addEventListener("input", () => {
      this.displayProducts();
    });

    document.getElementById("category-filter").addEventListener("change", () => {
      this.displayProducts();
    });
  }
}

// Global helper functions
function toggleProductForm() {
  const form = document.getElementById("product-form");
  const btn = document.querySelector(".add-product-btn");

  if (form.classList.contains("active")) {
    inventoryManager.cancelEdit();
  } else {
    form.classList.add("active");
    btn.textContent = "Cancel";
  }
}

function cancelEdit() {
  inventoryManager.cancelEdit();
}

function saveProduct(event) {
  event.preventDefault();

  const formData = {
    name: document.getElementById("product-name").value.trim(),
    description: document.getElementById("product-description").value.trim(),
    price: parseFloat(document.getElementById("product-price").value),
    category: document.getElementById("product-category").value,
    stock: parseInt(document.getElementById("product-stock").value),
    image: document.getElementById("image-preview").src || "../res/placeholder-food.jpg",
  };

  if (!formData.name || !formData.description || !formData.price || !formData.category || formData.stock < 0) {
    inventoryManager.showMessage("Please fill in all required fields correctly.", "error");
    return;
  }

  if (inventoryManager.currentEditId) {
    inventoryManager.updateProduct(inventoryManager.currentEditId, formData);
  } else {
    inventoryManager.addProduct(formData);
  }

  inventoryManager.resetForm();
  document.getElementById("product-form").classList.remove("active");
  document.querySelector(".add-product-btn").textContent = "Add New Product";
}

function previewImage(input) {
  const preview = document.getElementById("image-preview");
  const file = input.files[0];

  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      preview.src = e.target.result;
      preview.style.display = "block";
    };
    reader.readAsDataURL(file);
  } else {
    preview.style.display = "none";
  }
}

// Init on page load
let inventoryManager;
document.addEventListener("DOMContentLoaded", () => {
  inventoryManager = new InventoryManager();
});
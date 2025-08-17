// Student Dashboard JavaScript
class StudentDashboard {
    constructor() {
        this.cart = [];
        this.products = {};
        this.currentCategory = 'meals';
        this.studentInfo = null;
        this.init();
    }

    init() {
        this.loadStudentInfo();
        this.setupEventListeners();
        this.setDefaultDateTime();
        this.loadProducts();
        this.updateCartDisplay();
    }

    // Load student information from session/localStorage
    loadStudentInfo() {
        // In a real application, this would come from the authentication system
        this.studentInfo = {
            id: localStorage.getItem('studentId') || 'STU001',
            name: localStorage.getItem('studentName') || 'Student'
        };
        
        // Note: student-name element was removed from HTML, so we don't need to update it
    }

    // Load products from admin inventory
    loadProducts() {
        const loadingSpinner = document.getElementById('loading-spinner');
        const productGrid = document.getElementById('product-grid');
        
        // Try to load products from admin inventory first
        const adminProducts = localStorage.getItem('kainteen_products');
        if (adminProducts) {
            this.products = JSON.parse(adminProducts);
            this.displayProducts();
            loadingSpinner.style.display = 'none';
            return;
        }
        
        // Fallback to sample data if no admin products exist
        this.products = {
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
        
        this.displayProducts();
        loadingSpinner.style.display = 'none';
    }

    // Display products in the grid
    displayProducts() {
        const productGrid = document.getElementById('product-grid');
        const category = this.currentCategory;
        
        if (!this.products[category] || this.products[category].length === 0) {
            productGrid.innerHTML = `
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <p>No products available in this category</p>
                </div>
            `;
            return;
        }
        
        productGrid.innerHTML = this.products[category]
            .filter(product => product.stock > 0) // Only show products in stock
            .map(product => `
                <div class="product-card" data-product-id="${product.id}">
                    <img src="${product.image}" alt="${product.name}" class="product-image" 
                         onerror="this.src='../res/placeholder-food.jpg'">
                    <div class="product-info">
                        <h3>${product.name}</h3>
                        <p class="product-description">${product.description}</p>
                        <p class="product-price">₱${parseFloat(product.price).toFixed(2)}</p>
                        <p class="product-stock">Stock: ${product.stock}</p>
                        <button class="add-to-cart-btn" onclick="studentDashboard.addToCart('${product.id}')">
                            <i class="fas fa-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            `).join('');
    }

    // Switch between product categories
    switchCategory(category) {
        this.currentCategory = category;
        
        // Update active tab
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-category="${category}"]`).classList.add('active');
        
        // Display products for selected category
        this.displayProducts();
    }

    // Add product to cart
    addToCart(productId) {
        // Find the product
        let product = null;
        for (const category in this.products) {
            product = this.products[category].find(p => p.id === productId);
            if (product) break;
        }
        
        if (!product) return;
        
        // Check if product is already in cart
        const existingItem = this.cart.find(item => item.id === productId);
        
        if (existingItem) {
            // Increase quantity if stock allows
            if (existingItem.quantity < product.stock) {
                existingItem.quantity++;
            } else {
                this.showError('Maximum stock limit reached for this item');
                return;
            }
        } else {
            // Add new item to cart
            this.cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                quantity: 1,
                image: product.image
            });
        }
        
        this.updateCartDisplay();
        this.showSuccess(`${product.name} added to cart`);
    }

    // Remove item from cart
    removeFromCart(productId) {
        this.cart = this.cart.filter(item => item.id !== productId);
        this.updateCartDisplay();
    }

    // Update item quantity in cart
    updateQuantity(productId, change) {
        const item = this.cart.find(item => item.id === productId);
        if (item) {
            const newQuantity = item.quantity + change;
            if (newQuantity <= 0) {
                this.removeFromCart(productId);
            } else {
                // Check stock limit
                const product = this.findProduct(productId);
                if (product && newQuantity <= product.stock) {
                    item.quantity = newQuantity;
                } else {
                    this.showError('Cannot exceed available stock');
                    return;
                }
            }
            this.updateCartDisplay();
        }
    }

    // Find product by ID
    findProduct(productId) {
        for (const category in this.products) {
            const product = this.products[category].find(p => p.id === productId);
            if (product) return product;
        }
        return null;
    }

    // Calculate cart total
    calculateTotal() {
        return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    // Create cart item HTML
    createCartItemHTML(item) {
        return `
            <div class="cart-item" data-product-id="${item.id}">
                <img src="${item.image}" alt="${item.name}" class="cart-item-image" 
                     onerror="this.src='../res/placeholder-food.jpg'">
                <div class="cart-item-details">
                    <h4 class="cart-item-name">${item.name}</h4>
                    <p class="cart-item-price">₱${parseFloat(item.price).toFixed(2)}</p>
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="studentDashboard.updateQuantity('${item.id}', -1)">-</button>
                        <span class="quantity-display">${item.quantity}</span>
                        <button class="quantity-btn" onclick="studentDashboard.updateQuantity('${item.id}', 1)">+</button>
                    </div>
                </div>
                <button class="remove-item-btn" onclick="studentDashboard.removeFromCart('${item.id}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    }

    // Update cart display
    updateCartDisplay() {
        const cartItems = document.getElementById('cart-items');
        const cartTotal = document.getElementById('cart-total');
        const confirmBtn = document.getElementById('confirm-order-btn');
        const cartCount = document.getElementById('cart-count');

        // Update cart count in header
        const totalItems = this.cart.reduce((total, item) => total + item.quantity, 0);
        cartCount.textContent = totalItems;

        if (this.cart.length === 0) {
            cartItems.innerHTML = `
                <div class="cart-empty">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Your cart is empty</p>
                </div>
            `;
            cartTotal.textContent = '0.00';
            confirmBtn.disabled = true;
            return;
        }

        cartItems.innerHTML = this.cart.map(item => this.createCartItemHTML(item)).join('');
        cartTotal.textContent = this.calculateTotal().toFixed(2);
        confirmBtn.disabled = false;

        // Add event listeners to cart item buttons
        this.setupCartItemEventListeners();
    }

    // Setup event listeners for cart items
    setupCartItemEventListeners() {
        // This method can be expanded if needed for additional cart functionality
    }

    // Handle payment method change
    handlePaymentMethodChange(method) {
        const gcashForm = document.getElementById('gcash-form');
        const gcashAmount = document.getElementById('gcash-amount');
        
        if (method === 'gcash') {
            gcashForm.style.display = 'block';
            gcashAmount.value = `₱${this.calculateTotal().toFixed(2)}`;
        } else {
            gcashForm.style.display = 'none';
        }
    }

    // Process Gcash payment
    processGcashPayment() {
        const gcashNumber = document.getElementById('gcash-number').value;
        const gcashName = document.getElementById('gcash-name').value;
        
        if (!gcashNumber || !gcashName) {
            this.showError('Please fill in all Gcash details');
            return;
        }
        
        // Simulate Gcash payment processing
        this.showSuccess('Gcash payment processed successfully!');
        this.showOrderConfirmation();
    }

    // Show order confirmation modal
    showOrderConfirmation() {
        const modal = document.getElementById('order-confirmation-modal');
        const orderDetails = document.getElementById('order-details');
        
        const orderSummary = this.cart.map(item => 
            `${item.name} x${item.quantity} - ₱${(item.price * item.quantity).toFixed(2)}`
        ).join('<br>');
        
        orderDetails.innerHTML = `
            <strong>Order Summary:</strong><br>
            ${orderSummary}<br><br>
            <strong>Total: ₱${this.calculateTotal().toFixed(2)}</strong><br>
            <strong>Payment Method: ${document.getElementById('payment-method').value.toUpperCase()}</strong><br>
            <strong>Pickup Date: ${document.getElementById('pickup-date').value}</strong><br>
            <strong>Pickup Time: ${document.getElementById('pickup-time').value}</strong>
        `;
        
        modal.style.display = 'block';
    }

    // Confirm order
    confirmOrder() {
        // Here you would typically send the order to a backend server
        this.showSuccess('Order confirmed successfully! Your order number is: ORD-' + Date.now());
        
        // Clear cart
        this.cart = [];
        this.updateCartDisplay();
        
        // Reset form
        document.getElementById('payment-method').value = '';
        document.getElementById('gcash-form').style.display = 'none';
        
        // Close modal
        document.getElementById('order-confirmation-modal').style.display = 'none';
    }

    // Handle search
    handleSearch(searchTerm) {
        if (!searchTerm.trim()) {
            this.displayProducts();
            return;
        }
        
        const productGrid = document.getElementById('product-grid');
        const category = this.currentCategory;
        
        if (!this.products[category]) return;
        
        const filteredProducts = this.products[category].filter(product => 
            product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            product.description.toLowerCase().includes(searchTerm.toLowerCase())
        );
        
        if (filteredProducts.length === 0) {
            productGrid.innerHTML = `
                <div class="search-results">
                    <h3>No products found for "${searchTerm}"</h3>
                    <p>Try a different search term or browse other categories</p>
                </div>
            `;
        } else {
            productGrid.innerHTML = filteredProducts
                .filter(product => product.stock > 0)
                .map(product => `
                    <div class="product-card" data-product-id="${product.id}">
                        <img src="${product.image}" alt="${product.name}" class="product-image" 
                             onerror="this.src='../res/placeholder-food.jpg'">
                        <div class="product-info">
                            <h3>${product.name}</h3>
                            <p class="product-description">${product.description}</p>
                            <p class="product-price">₱${parseFloat(product.price).toFixed(2)}</p>
                            <p class="product-stock">Stock: ${product.stock}</p>
                            <button class="add-to-cart-btn" onclick="studentDashboard.addToCart('${product.id}')">
                                <i class="fas fa-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                `).join('');
        }
    }

    // Set default date and time
    setDefaultDateTime() {
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        
        const dateInput = document.getElementById('pickup-date');
        const timeInput = document.getElementById('pickup-time');
        
        if (dateInput) {
            dateInput.value = tomorrow.toISOString().split('T')[0];
        }
        if (timeInput) {
            timeInput.value = '12:00';
        }
    }

    // Setup all event listeners
    setupEventListeners() {
        // Category tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.switchCategory(e.target.dataset.category);
            });
        });

        // Search functionality
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.handleSearch(e.target.value);
            });
        }

        // Payment method selection
        const paymentMethod = document.getElementById('payment-method');
        if (paymentMethod) {
            paymentMethod.addEventListener('change', (e) => {
                this.handlePaymentMethodChange(e.target.value);
            });
        }

        // Gcash payment processing
        const processGcash = document.getElementById('process-gcash');
        if (processGcash) {
            processGcash.addEventListener('click', () => {
                this.processGcashPayment();
            });
        }

        // Confirm order
        const confirmOrderBtn = document.getElementById('confirm-order-btn');
        if (confirmOrderBtn) {
            confirmOrderBtn.addEventListener('click', () => {
                this.showOrderConfirmation();
            });
        }

        // Cart toggle functionality
        const cartToggleBtn = document.getElementById('cart-toggle-btn');
        if (cartToggleBtn) {
            cartToggleBtn.addEventListener('click', () => {
                this.toggleCart();
            });
        }

        // Close cart when clicking overlay
        const cartOverlay = document.getElementById('cart-overlay');
        if (cartOverlay) {
            cartOverlay.addEventListener('click', () => {
                this.closeCart();
            });
        }

        // Close cart button
        const closeCartBtn = document.getElementById('close-cart-btn');
        if (closeCartBtn) {
            closeCartBtn.addEventListener('click', () => {
                this.closeCart();
            });
        }

        // Modal close buttons
        document.querySelectorAll('.close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.target.closest('.modal').style.display = 'none';
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                e.target.style.display = 'none';
            }
        });

        // Logout functionality
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                this.logout();
            });
        }
    }

    // Cart toggle functionality
    toggleCart() {
        const cartSidebar = document.getElementById('cart-sidebar');
        const cartOverlay = document.getElementById('cart-overlay');
        
        if (cartSidebar && cartOverlay) {
            cartSidebar.classList.add('active');
            cartOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    closeCart() {
        const cartSidebar = document.getElementById('cart-sidebar');
        const cartOverlay = document.getElementById('cart-overlay');
        
        if (cartSidebar && cartOverlay) {
            cartSidebar.classList.remove('active');
            cartOverlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    }

    // Logout functionality
    logout() {
        // Clear any stored data
        localStorage.removeItem('studentId');
        localStorage.removeItem('studentName');
        
        // Redirect to login page
        window.location.href = 'signInStudent.html';
    }

    // Utility functions for notifications
    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => notification.classList.add('show'), 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

// Initialize dashboard when page loads
let studentDashboard;
document.addEventListener('DOMContentLoaded', () => {
    studentDashboard = new StudentDashboard();
});
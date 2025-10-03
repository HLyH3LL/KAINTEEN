// KAINTEEN/js/studentDashboard.js

document.addEventListener("DOMContentLoaded", () => {
  const cartItemsContainer = document.getElementById("cart-items");
  const cartTotalEl = document.getElementById("cart-total");
  const checkoutBtn = document.getElementById("checkoutBtn");
  const categoryBtns = document.querySelectorAll(".cat");
  const menuGrid = document.getElementById("menu-grid");

  let cart = [];

  // Category filter
  categoryBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      const cat = btn.getAttribute("data-cat");
      document.querySelectorAll(".cat").forEach(c => c.classList.remove("active"));
      btn.classList.add("active");

      document.querySelectorAll(".menu-card").forEach(card => {
        if (cat === "all" || card.dataset.category === cat) {
          card.style.display = "block";
        } else {
          card.style.display = "none";
        }
      });
    });
  });

  // Handle add button
  menuGrid.addEventListener("click", e => {
    if (e.target.classList.contains("btn-add")) {
      const btn = e.target;
      const id = btn.getAttribute("data-id");
      const name = btn.getAttribute("data-name");
      const price = parseFloat(btn.getAttribute("data-price"));
      const qtyInput = btn.closest(".actions").querySelector(".qty");
      const qty = parseInt(qtyInput.value) || 1;
      addToCart(id, name, price, qty);
    }
  });

  function addToCart(id, name, price, qty) {
    const existing = cart.find(item => item.id === id);
    if (existing) {
      existing.qty += qty;
    } else {
      cart.push({ id, name, price, qty });
    }
    renderCart();
  }

  function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    renderCart();
  }

  function renderCart() {
    cartItemsContainer.innerHTML = "";
    let total = 0;

    if (cart.length === 0) {
      cartItemsContainer.innerHTML = "<p>Your cart is empty.</p>";
    } else {
      cart.forEach(item => {
        const div = document.createElement("div");
        div.className = "cart-item";
        div.innerHTML = `
          <span>${item.name} x ${item.qty}</span>
          <span>₱${(item.price * item.qty).toFixed(2)}</span>
          <button class="remove-btn" data-id="${item.id}">x</button>
        `;
        cartItemsContainer.appendChild(div);
        total += item.price * item.qty;
      });
    }

    cartTotalEl.textContent = total.toFixed(2);
  }

  // Remove from cart
  cartItemsContainer.addEventListener("click", e => {
    if (e.target.classList.contains("remove-btn")) {
      const id = e.target.getAttribute("data-id");
      removeFromCart(id);
    }
  });

  // Checkout
  checkoutBtn.addEventListener("click", () => {
    if (cart.length === 0) {
      alert("Your cart is empty!");
      return;
    }

    // Later: send to PHP backend
    alert("Order placed successfully!");
    cart = [];
    renderCart();
  });

  renderCart();
});

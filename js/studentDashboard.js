document.addEventListener("DOMContentLoaded", () => {
  const cartItemsContainer = document.getElementById("cart-items");
  const cartTotalEl = document.getElementById("cart-total");
  const checkoutBtn = document.getElementById("checkoutBtn");
  const categoryBtns = document.querySelectorAll(".cat");
  const menuGrid = document.getElementById("menu-grid");
  const checkoutPopup = document.getElementById("checkoutPopup");
  const checkoutCartInput = document.getElementById("checkoutCart");

  let cart = JSON.parse(sessionStorage.getItem("cart") || "{}");

  // Category filter
  categoryBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      const cat = btn.getAttribute("data-cat");
      categoryBtns.forEach(c => c.classList.remove("active"));
      btn.classList.add("active");
      document.querySelectorAll(".menu-card").forEach(card => {
        card.style.display = cat === "all" || card.dataset.category === cat ? "block" : "none";
      });
    });
  });

  // Add to cart
  menuGrid.addEventListener("click", e => {
    if (e.target.classList.contains("btn-add")) {
      const btn = e.target;
      const id = btn.getAttribute("data-id");
      const name = btn.getAttribute("data-name");
      const price = parseFloat(btn.getAttribute("data-price"));
      const qtyInput = btn.closest(".actions").querySelector(".qty");
      const qty = parseInt(qtyInput.value) || 1;
      if (cart[id]) cart[id].qty += qty;
      else cart[id] = { name, price, qty };
      saveCart();
      renderCart();
    }
  });

  // Remove from cart
  cartItemsContainer.addEventListener("click", e => {
    if (e.target.classList.contains("remove-btn")) {
      const id = e.target.getAttribute("data-id");
      delete cart[id];
      saveCart();
      renderCart();
    }
  });

  function saveCart() {
    sessionStorage.setItem("cart", JSON.stringify(cart));
  }

  function renderCart() {
    cartItemsContainer.innerHTML = "";
    let total = 0;
    const keys = Object.keys(cart);
    if (keys.length === 0) cartItemsContainer.innerHTML = "<p>Your cart is empty.</p>";
    else keys.forEach(id => {
      const item = cart[id];
      const subtotal = item.price * item.qty;
      const div = document.createElement("div");
      div.className = "cart-item";
      div.innerHTML = `
        <span>${item.name} x ${item.qty}</span>
        <span>₱${subtotal.toFixed(2)}</span>
        <button class="remove-btn" data-id="${id}" title="Remove item">×</button>
      `;
      cartItemsContainer.appendChild(div);
      total += subtotal;
    });
    cartTotalEl.textContent = total.toFixed(2);
  }

  // Checkout popup
  if (checkoutBtn) {
    checkoutBtn.addEventListener("click", () => {
      const keys = Object.keys(cart);
      if (keys.length === 0) {
        alert("Your cart is empty!");
        return;
      }
      checkoutCartInput.value = JSON.stringify(cart);
      checkoutPopup.style.display = "flex";
    });
  }

  window.closeCheckout = () => checkoutPopup.style.display = "none";

  renderCart();
});

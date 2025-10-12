document.addEventListener("DOMContentLoaded", () => {
  const cartItemsContainer = document.getElementById("cart-items");
  const cartTotalEl = document.getElementById("cart-total");
  const checkoutBtn = document.getElementById("checkoutBtn");
  const categoryBtns = document.querySelectorAll(".cat");
  const menuGrid = document.getElementById("menu-grid");

  // Load cart from sessionStorage or empty object
  // Cart structure: { [productId]: { name, price, qty } }
  let cart = JSON.parse(sessionStorage.getItem("cart") || "{}");

  /** ============================
   * CATEGORY FILTER
   * ============================ */
  categoryBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const cat = btn.getAttribute("data-cat");
      categoryBtns.forEach((c) => c.classList.remove("active"));
      btn.classList.add("active");

      document.querySelectorAll(".menu-card").forEach((card) => {
        card.style.display =
          cat === "all" || card.dataset.category === cat ? "block" : "none";
      });
    });
  });

  /** ============================
   * ADD TO CART
   * ============================ */
  menuGrid.addEventListener("click", (e) => {
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
    if (cart[id]) {
      cart[id].qty += qty;
    } else {
      cart[id] = { name, price, qty };
    }
    saveCart();
    renderCart();
  }

  function removeFromCart(id) {
    delete cart[id];
    saveCart();
    renderCart();
  }

  function saveCart() {
    sessionStorage.setItem("cart", JSON.stringify(cart));
  }

  /** ============================
   * RENDER CART
   * ============================ */
  function renderCart() {
    cartItemsContainer.innerHTML = "";
    let total = 0;
    const keys = Object.keys(cart);

    if (keys.length === 0) {
      cartItemsContainer.innerHTML = "<p>Your cart is empty.</p>";
    } else {
      keys.forEach((id) => {
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
    }

    cartTotalEl.textContent = total.toFixed(2);
  }

  // Remove from cart
  cartItemsContainer.addEventListener("click", (e) => {
    if (e.target.classList.contains("remove-btn")) {
      const id = e.target.getAttribute("data-id");
      removeFromCart(id);
    }
  });

  /** ============================
   * CHECKOUT (POST to PHP)
   * ============================ */
  checkoutBtn.addEventListener("click", async () => {
    const keys = Object.keys(cart);
    if (keys.length === 0) {
      alert("Your cart is empty!");
      return;
    }

    // Build POST data - keys as product IDs, values as qty
    const formData = new FormData();
    keys.forEach((id) => {
      formData.append(`cart[${id}]`, cart[id].qty);
    });

    checkoutBtn.disabled = true;
    checkoutBtn.textContent = "Processing...";

    try {
      const res = await fetch("../php/checkout.php", {
        method: "POST",
        body: formData,
        credentials: "include",
      });

      const text = await res.text();

      if (!res.ok) throw new Error(text || "Server error");

      alert("✅ Order placed successfully! Your receipt has been sent via email.");

      // Clear cart
      cart = {};
      saveCart();
      renderCart();

      // Optionally reload or redirect after delay
      setTimeout(() => {
        location.reload();
      }, 1500);
    } catch (err) {
      alert("❌ Order failed: " + err.message);
      console.error(err);
    } finally {
      checkoutBtn.disabled = false;
      checkoutBtn.textContent = "Checkout";
    }
  });

  // Initial cart render
  renderCart();
});

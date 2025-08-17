function openOrderModal() {
  async function openOrderModal(orderId) {
    try {
      // Fetch order details from backend
      let response = await fetch(`../php/get_order_details.php?id=${orderId}`);
      let data = await response.json();

      if (!data.success) {
        alert("Failed to load order details: " + data.message);
        return;
      }

      // Fill modal content dynamically
      document.querySelector("#orderModal h1").textContent = data.order.order_code;

      // Fill order info (student no + status)
      let orderInfoTable = `
        <tr>
          <th>Order ID</th>
          <th>Student No.</th>
          <th>Status</th>
        </tr>
        <tr>
          <td>${data.order.order_code}</td>
          <td>${data.order.student_no}</td>
          <td class="status-${data.order.status.toLowerCase()}">${data.order.status}</td>
        </tr>
      `;
      document.querySelector("#orderModal table").innerHTML = orderInfoTable;

      // Fill order summary
      let summaryTable = "<tr><th>Item</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr>";
      data.items.forEach(item => {
        summaryTable += `
          <tr>
            <td>${item.name}</td>
            <td>₱${item.price.toFixed(2)}</td>
            <td>${item.quantity}</td>
            <td>₱${item.subtotal.toFixed(2)}</td>
          </tr>
        `;
      });
      summaryTable += `
        <tr><td colspan="3"><strong>Total:</strong></td><td><strong>₱${data.total.toFixed(2)}</strong></td></tr>
      `;
      document.getElementById("order-summary").innerHTML = summaryTable;

      // Show modal
      document.getElementById("orderModal").style.display = "block";
    } catch (error) {
      console.error(error);
      alert("Error fetching order details.");
    }
  }

  function closeOrderModal() {
    document.getElementById("orderModal").style.display = "none";
  }

  window.onclick = function(event) {
    let modal = document.getElementById("orderModal");
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
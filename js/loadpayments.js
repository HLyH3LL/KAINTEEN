async function loadPayments() {
  let sort = document.getElementById("sort").value;
  let status = document.getElementById("filterStatus")?.value || "";

  let response = await fetch(`../php/get_payments.php?sort=${sort}&status=${status}`);
  let payments = await response.json();

  let tbody = document.querySelector("tbody");
  tbody.innerHTML = ""; // clear table

  payments.forEach(p => {
    let row = `
      <tr>
        <td>${p.order_code}</td>
        <td>${p.student_no}</td>
        <td class="status-${p.status.toLowerCase()}">${p.status}</td>
        <td>${p.payment_date}</td>
        <td>${p.mop}</td>
      </tr>`;
    tbody.innerHTML += row;
  });
}

// Reload table when dropdown changes
document.getElementById("sort").addEventListener("change", loadPayments);
document.getElementById("filterStatus")?.addEventListener("change", loadPayments);

window.onload = loadPayments;

 function openModal() {
    document.getElementById("logoutModal").style.display = "flex";
  }

  function closeModal() {
    document.getElementById("logoutModal").style.display = "none";
  }

  function logout() {
    window.location.href = "adminLogin.html";
  }

  // Optional: Click outside modal to close
  window.onclick = function(event) {
    const modal = document.getElementById("logoutModal");
    if (event.target === modal) {
      closeModal();
    }
  }
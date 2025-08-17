function openModal() {
      document.getElementById("logoutModal").style.display = "flex";
    }

    function closeModal() {
      document.getElementById("logoutModal").style.display = "none";
    }

    function logout() {
      window.location.href = "adminLogin.html";
    }
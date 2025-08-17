
function togglePassword() {
  const passwordField = document.getElementById("admin-password");
  if (passwordField.type === "password") {
    passwordField.type = "text";
  } else {
    passwordField.type = "password";
  }
}

// Popup Message
function showPopup(message, isError = false) {
  const popup = document.createElement("div");
  popup.classList.add("popup");
  if (isError) popup.classList.add("error");
  popup.innerText = message;
  document.body.appendChild(popup);

  setTimeout(() => { popup.style.opacity = "1"; }, 50);
  setTimeout(() => {
    popup.style.opacity = "0";
    setTimeout(() => popup.remove(), 300);
  }, 2000);
}

// Handle Form Submit
document.getElementById("adminLoginForm").addEventListener("submit", function(e) {
  e.preventDefault();

  const username = document.getElementById("admin-username").value.trim();
  const password = document.getElementById("admin-password").value.trim();

  if (!username || !password) {
    showPopup("Please enter username and password.", true);
    return;
  }
}

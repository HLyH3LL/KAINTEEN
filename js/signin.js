document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    const studentNumber = document.getElementById("student-number").value.trim();
    const password = document.getElementById("password").value.trim();

    if (!/^[0-9]{7}$/.test(studentNumber)) {
      showPopup("Student number must be exactly 7 digits.", true);
      return;
    }
    if (password.length < 6) {
      showPopup("Password must be at least 6 characters.", true);
      return;
    }

    showPopup("Sign in successful! Welcome back.");
    form.reset();
  });

  function showPopup(message, isError = false) {
    const popup = document.createElement("div");
    popup.className = "popup" + (isError ? " error" : "");
    popup.textContent = message;
    document.body.appendChild(popup);

    setTimeout(() => popup.style.opacity = "1", 50);
    setTimeout(() => {
      popup.style.opacity = "0";
      setTimeout(() => popup.remove(), 300);
    }, 2000);
  }
});

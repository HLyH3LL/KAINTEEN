document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    const studentNumber = document.getElementById("student-number").value.trim();
    const password = document.getElementById("password").value.trim();
    const confirmPassword = document.getElementById("confirm-password").value.trim();
    const terms = document.getElementById("terms").checked;

  
    if (!/^[0-9]{7}$/.test(studentNumber)) {
      showPopup("Student number must be exactly 7 digits.", true);
      return;
    }

  
    if (password.length < 6) {
      showPopup("Password must be at least 6 characters.", true);
      return;
    }

    
    if (password !== confirmPassword) {
      showPopup("Passwords do not match.", true);
      return;
    }

   
    if (!terms) {
      showPopup("You must agree to the Terms and Privacy Policy.", true);
      return;
    }

   
    showPopup("Account created successfully! 🎉");
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

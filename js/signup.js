document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    const email = document.getElementById("email").value.trim();
    const studentNumber = document.getElementById("student-number").value.trim();
    const password = document.getElementById("password").value.trim();
    const confirmPassword = document.getElementById("confirm-password").value.trim();
    const terms = document.getElementById("terms").checked;

    console.log("Form inputs:", { email, studentNumber, password, confirmPassword, terms });

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      showPopup("Please enter a valid email address.", true);
      return;
    }
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

    
    fetch("../php/student_signup.php", {  
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        email: email,
        student_number: studentNumber,
        password: password
      })
    })
    .then((response) => response.text())
    .then((data) => {
      console.log("Server Response:", data);

      if (data.startsWith("success|")) {
        const [, redirectUrl] = data.split("|");

        showPopup("Account created successfully! Redirecting...");
        console.log("Redirecting to:", redirectUrl);

        setTimeout(() => {
          window.location.href = redirectUrl;
        }, 2000);
      } else {
        showPopup(data, true);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showPopup("Something went wrong. Try again later.", true);
    });
  });

  function showPopup(message, isError = false) {
    const popup = document.createElement("div");
    popup.className = "popup" + (isError ? " error" : "");
    popup.textContent = message;
    document.body.appendChild(popup);

    setTimeout(() => (popup.style.opacity = "1"), 50);
    setTimeout(() => {
      popup.style.opacity = "0";
      setTimeout(() => popup.remove(), 300);
    }, 2500);
  }
});

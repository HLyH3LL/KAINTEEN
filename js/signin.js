document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");
  const studentNumberInput = document.getElementById("student-number");
  const passwordInput = document.getElementById("password");

  const liveDisplay = document.createElement("p");
  liveDisplay.style.color = "yellow";
  liveDisplay.style.fontSize = "14px";
  studentNumberInput.insertAdjacentElement("afterend", liveDisplay);

  studentNumberInput.addEventListener("input", () => {
    liveDisplay.textContent = `Typing Student No: ${studentNumberInput.value}`;
    console.log("Live input:", studentNumberInput.value);
  });

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    const studentNumber = studentNumberInput.value.trim();
    const password = passwordInput.value.trim();

    console.log("Form submitted with values:", { studentNumber, password });

    if (!/^[0-9]{7}$/.test(studentNumber)) {
      showPopup("Student number must be exactly 7 digits.", true);
      return;
    }
    if (password.length < 6) {
      showPopup("Password must be at least 6 characters.", true);
      return;
    }

    fetch("../php/customer_login.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        student_number: studentNumber,
        password: password
      })
    })
      .then((response) => response.text())
      .then((data) => {
        console.log("Server Response:", data);

        if (data.startsWith("success|")) {
          const [, redirectUrl] = data.split("|");

          showPopup("Sign in successful! Redirecting...");
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
    }, 2000);
  }
});

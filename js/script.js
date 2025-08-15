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
            showPopup(" Password must be at least 6 characters.", true);
            return;
        }

        
        showPopup("Sign in successful! Welcome back.");
        form.reset();
    });

    function showPopup(message, isError = false) {
       
        const popup = document.createElement("div");
        popup.textContent = message;
        popup.style.position = "fixed";
        popup.style.top = "50%";
        popup.style.left = "50%";
        popup.style.transform = "translate(-50%, -50%)";
        popup.style.background = isError ? "#ff4d4d" : "#4CAF50";
        popup.style.color = "white";
        popup.style.padding = "15px 25px";
        popup.style.borderRadius = "8px";
        popup.style.fontSize = "16px";
        popup.style.boxShadow = "0 2px 10px rgba(0,0,0,0.2)";
        popup.style.zIndex = "9999";
        popup.style.opacity = "0";
        popup.style.transition = "opacity 0.3s ease";

        document.body.appendChild(popup);

 
        setTimeout(() => {
            popup.style.opacity = "1";
        }, 50);

        setTimeout(() => {
            popup.style.opacity = "0";
            setTimeout(() => popup.remove(), 300);
        }, 2000);
    }
});
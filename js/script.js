 v
    document.addEventListener('DOMContentLoaded', () => {
        
        const form = document.querySelector('form');
        const studentNumberInput = document.getElementById('student-number');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm-password');
        const termsCheckbox = document.getElementById('terms');
        const submitButton = form.querySelector('button');

  
        form.addEventListener('submit', (event) => {
            event.preventDefault(); 

      
            const studentNumber = studentNumberInput.value.trim();
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

          
            const studentNumberPattern = /^[0-9]{7}$/;
            if (!studentNumberPattern.test(studentNumber)) {
                alert("Please enter a valid 7-digit student number.");
                return;
            }

            if (password !== confirmPassword) {
                alert("Passwords do not match.");
                return;
            }

            if (!termsCheckbox.checked) {
                alert("You must agree to the terms of service and privacy policy.");
                return;
            }

            alert("Account created successfully!");
            form.reset();
        });

       
        form.addEventListener('input', () => {
            const isValid = studentNumberPattern.test(studentNumberInput.value.trim()) &&
                            passwordInput.value === confirmPasswordInput.value &&
                            termsCheckbox.checked;
            submitButton.disabled = !isValid;
        });
    });


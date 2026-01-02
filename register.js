// register.js - Registration page specific functionality

document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');
    
    if (!registerForm) {
        console.error('Register form not found');
        return;
    }

    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate passwords match
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            showNotification('Passwords do not match.', 'error');
            return;
        }

        const formData = new FormData(registerForm);
        const submitBtn = registerForm.querySelector('.btn-register');
        const originalText = submitBtn.textContent;

        // Show loading state
        submitBtn.textContent = 'Processing...';
        submitBtn.disabled = true;

        fetch('/bloodbridge/php/register_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Store user data in localStorage
                localStorage.setItem('user', JSON.stringify(data.user));
                
                showNotification(data.message);
                registerForm.reset();
                
                // Redirect to login page after successful registration
                setTimeout(() => {
                    window.location.href = '/bloodbridge/login.html';
                }, 2000);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        })
        .finally(() => {
            // Reset button state
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
});
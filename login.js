// login.js - Login page specific functionality

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    
    if (!loginForm) {
        console.error('Login form not found');
        return;
    }

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(loginForm);
        const submitBtn = loginForm.querySelector('.btn-login');
        const originalText = submitBtn.textContent;

        // Show loading state
        submitBtn.textContent = 'Processing...';
        submitBtn.disabled = true;

        fetch('/bloodbridge/php/login_user.php', {
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
                
                // Redirect to home page after successful login
                setTimeout(() => {
                    window.location.href = '/bloodbridge/index.html';
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
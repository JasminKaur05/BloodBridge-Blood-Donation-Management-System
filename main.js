// main.js - Home page specific functionality

document.addEventListener('DOMContentLoaded', function() {
    console.log('Main.js loaded');
    
    // Check if user is logged in
    let currentUser = null;
    
    function updateCurrentUser() {
        const userStr = localStorage.getItem('user');
        if (userStr) {
            try {
                currentUser = JSON.parse(userStr);
                console.log('Current user updated:', currentUser);
            } catch (e) {
                console.error('Error parsing user data:', e);
                localStorage.removeItem('user');
                currentUser = null;
            }
        } else {
            console.log('No user found in localStorage');
            currentUser = null;
        }
    }
    
    // Update current user initially
    updateCurrentUser();

    // Get DOM elements
    const donateBtn = document.getElementById('donate-btn');
    const requestBtn = document.getElementById('request-btn');
    const donorModal = document.getElementById('donor-modal');
    const requestModal = document.getElementById('request-modal');
    const closeBtns = document.querySelectorAll('.close');
    
    console.log('DOM elements found:', {
        donateBtn: donateBtn,
        requestBtn: requestBtn,
        donorModal: donorModal,
        requestModal: requestModal
    });

    // Open modals
    if (donateBtn && donorModal) {
        console.log('Adding click event to donate button');
        donateBtn.addEventListener('click', function(e) {
            console.log('Donate button clicked');
            e.preventDefault();
            
            // Update current user before checking
            updateCurrentUser();
            
            if (!currentUser) {
                console.log('User not logged in');
                showNotification('Please login to register as a donor.', 'error');
                setTimeout(() => {
                    window.location.href = '/bloodbridge/login.html';
                }, 2000);
                return;
            }
            
            console.log('Opening donor modal');
            donorModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    } else {
        console.error('Donate button or modal not found');
    }

    if (requestBtn && requestModal) {
        console.log('Adding click event to request button');
        requestBtn.addEventListener('click', function(e) {
            console.log('Request button clicked');
            e.preventDefault();
            
            // Update current user before checking
            updateCurrentUser();
            
            if (!currentUser) {
                console.log('User not logged in');
                showNotification('Please login to request blood.', 'error');
                setTimeout(() => {
                    window.location.href = '/bloodbridge/login.html';
                }, 2000);
                return;
            }
            
            console.log('Opening request modal');
            requestModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    } else {
        console.error('Request button or modal not found');
    }

    // Close modals
    closeBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            console.log('Close button clicked');
            donorModal.style.display = 'none';
            requestModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === donorModal) {
            donorModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        if (event.target === requestModal) {
            requestModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });

    // Form submissions
    const donorForm = document.getElementById('donor-form');
    const requestForm = document.getElementById('request-form');
    const contactForm = document.getElementById('contact-form');

    // Donor form submission
    if (donorForm) {
        donorForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Donor form submitted');
            
            // Update current user before submitting
            updateCurrentUser();
            
            if (!currentUser) {
                showNotification('Please login to register as a donor.', 'error');
                return;
            }

            const formData = new FormData(donorForm);

            const submitBtn = donorForm.querySelector('.btn');
            const originalText = submitBtn.textContent;

            // Show loading state
            submitBtn.textContent = 'Processing...';
            submitBtn.disabled = true;

            fetch('/bloodbridge/php/donor_registration.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response status text:', response.statusText);
                
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Server response:', text);
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    });
                }
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Non-JSON response:', text);
                        throw new Error('Server returned non-JSON response');
                    });
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Donor registration response:', data);
                if (data.success) {
                    showNotification(data.message);
                    donorForm.reset();
                    donorModal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                } else {
                    showNotification(data.message || 'Registration failed. Please try again.', 'error');
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
    } else {
        console.error('Donor form not found');
    }

    // Blood request form submission
    if (requestForm) {
        requestForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Request form submitted');
            
            // Update current user before submitting
            updateCurrentUser();
            
            if (!currentUser) {
                showNotification('Please login to request blood.', 'error');
                return;
            }

            const formData = new FormData(requestForm);

            const submitBtn = requestForm.querySelector('.btn');
            const originalText = submitBtn.textContent;

            // Show loading state
            submitBtn.textContent = 'Processing...';
            submitBtn.disabled = true;

            fetch('/bloodbridge/php/request_blood_advanced.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response status text:', response.statusText);
                
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Server response:', text);
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    });
                }
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Non-JSON response:', text);
                        throw new Error('Server returned non-JSON response');
                    });
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Blood request response:', data);
                if (data.success) {
                    showNotification(data.message);
                    requestForm.reset();
                    requestModal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                } else {
                    showNotification(data.message || 'Request failed. Please try again.', 'error');
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
    } else {
        console.error('Request form not found');
    }

    // Contact form submission
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Contact form submitted');
            
            const formData = new FormData(contactForm);
            const submitBtn = contactForm.querySelector('button');
            const originalText = submitBtn.textContent;

            // Show loading state
            submitBtn.textContent = 'Sending...';
            submitBtn.disabled = true;

            fetch('/bloodbridge/php/contact.php', {
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
                    showNotification(data.message);
                    contactForm.reset();
                } else {
                    showNotification(data.message || 'Message failed to send. Please try again.', 'error');
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
    } else {
        console.error('Contact form not found');
    }

    // Function to show notifications
    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        const notificationMessage = document.getElementById('notification-message');
        const closeNotification = document.querySelector('.close-notification');
        
        notificationMessage.textContent = message;

        if (type === 'error') {
            notification.classList.add('error');
        } else {
            notification.classList.remove('error');
        }

        notification.style.display = 'flex';

        closeNotification.addEventListener('click', function() {
            notification.style.display = 'none';
        });

        // Auto hide after 5 seconds
        setTimeout(function() {
            notification.style.display = 'none';
        }, 5000);
    }
});
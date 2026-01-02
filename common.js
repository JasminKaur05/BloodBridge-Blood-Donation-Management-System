// common.js - Shared functionality across all pages

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
        });
    }
    
    // Close notification when close button is clicked
    const closeNotification = document.querySelector('.close-notification');
    const notification = document.getElementById('notification');
    
    if (closeNotification && notification) {
        closeNotification.addEventListener('click', function() {
            notification.style.display = 'none';
        });
    }
    
    // Auto-hide notifications after 5 seconds
    if (notification) {
        setTimeout(function() {
            notification.style.display = 'none';
        }, 5000);
    }
});

// Function to show notifications
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    const notificationMessage = document.getElementById('notification-message');
    
    if (notification && notificationMessage) {
        notificationMessage.textContent = message;
        
        if (type === 'error') {
            notification.classList.add('error');
        } else {
            notification.classList.remove('error');
        }
        
        notification.style.display = 'flex';
        
        // Auto hide after 5 seconds
        setTimeout(function() {
            notification.style.display = 'none';
        }, 5000);
    }
}

// Function to update navigation based on login status
function updateNavigation() {
    const user = localStorage.getItem('user');
    const registerLink = document.getElementById('register-link');
    const loginLink = document.getElementById('login-link');
    const donorNotificationsLink = document.getElementById('donor-notifications-link');
    const logoutLink = document.getElementById('logout-link');
    
    if (user) {
        // User is logged in
        if (registerLink) registerLink.style.display = 'none';
        if (loginLink) loginLink.style.display = 'none';
        if (donorNotificationsLink) donorNotificationsLink.style.display = 'inline-block';
        if (logoutLink) logoutLink.style.display = 'inline-block';
    } else {
        // User is not logged in
        if (registerLink) registerLink.style.display = 'inline-block';
        if (loginLink) loginLink.style.display = 'inline-block';
        if (donorNotificationsLink) donorNotificationsLink.style.display = 'none';
        if (logoutLink) logoutLink.style.display = 'none';
    }
}

// Function to check if user is logged in
function isLoggedIn() {
    const user = localStorage.getItem('user');
    return user !== null;
}

// Function to get current user
function getCurrentUser() {
    const userStr = localStorage.getItem('user');
    if (userStr) {
        try {
            return JSON.parse(userStr);
        } catch (e) {
            console.error('Error parsing user data:', e);
            localStorage.removeItem('user');
            return null;
        }
    }
    return null;
}
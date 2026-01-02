// notifications.js - Donor notifications page specific functionality

document.addEventListener('DOMContentLoaded', function() {
    // Update navigation based on login status
    updateNavigation();
    
    const checkNotificationsBtn = document.getElementById('check-notifications');
    const notificationsContainer = document.getElementById('notifications-container');
    const responseModal = document.getElementById('response-modal');
    const acceptRequestBtn = document.getElementById('accept-request');
    const declineRequestBtn = document.getElementById('decline-request');
    const closeBtns = document.querySelectorAll('.close');
    
    if (checkNotificationsBtn) {
        checkNotificationsBtn.addEventListener('click', function() {
            const email = document.getElementById('email').value;
            
            if (!email) {
                showNotification('Please enter your email address.', 'error');
                return;
            }
            
            // Show loading state
            checkNotificationsBtn.textContent = 'Checking...';
            checkNotificationsBtn.disabled = true;
            
            fetch('/bloodbridge/php/get_notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'email=' + encodeURIComponent(email)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    displayNotifications(data.notifications);
                } else {
                    showNotification(data.message || 'Failed to retrieve notifications.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            })
            .finally(() => {
                // Reset button state
                checkNotificationsBtn.textContent = 'Check Notifications';
                checkNotificationsBtn.disabled = false;
            });
        });
    }
    
    function displayNotifications(notifications) {
        if (!notificationsContainer) return;
        
        notificationsContainer.innerHTML = '';
        
        if (notifications.length === 0) {
            notificationsContainer.innerHTML = '<div class="no-notifications">No notifications found.</div>';
            return;
        }
        
        notifications.forEach(notification => {
            const notificationElement = document.createElement('div');
            notificationElement.className = 'notification-item';
            notificationElement.innerHTML = `
                <div class="notification-message">
                    <p><strong>Blood Request:</strong> ${notification.blood_group} blood needed in ${notification.location}</p>
                    <p><strong>Patient:</strong> ${notification.patient_name}</p>
                    <p><strong>Status:</strong> ${notification.status}</p>
                    <p><strong>Date:</strong> ${new Date(notification.created_at).toLocaleString()}</p>
                </div>
                <button class="respond-btn" data-id="${notification.notification_id}">Respond</button>
            `;
            
            notificationsContainer.appendChild(notificationElement);
        });
        
        // Add event listeners to respond buttons
        const respondBtns = document.querySelectorAll('.respond-btn');
        respondBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const notificationId = this.getAttribute('data-id');
                showNotificationDetails(notificationId);
            });
        });
    }
    
    function showNotificationDetails(notificationId) {
        // Fetch notification details
        fetch('/bloodbridge/php/get_notification_details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + notificationId
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const notification = data.notification;
                const detailsContainer = document.getElementById('notification-details');
                
                if (detailsContainer) {
                    detailsContainer.innerHTML = `
                        <div class="notification-details-content">
                            <p><strong>Patient Name:</strong> ${notification.patient_name}</p>
                            <p><strong>Blood Group:</strong> ${notification.blood_group}</p>
                            <p><strong>Hospital/Address:</strong> ${notification.address}</p>
                            <p><strong>Location:</strong> ${notification.location}</p>
                            <p><strong>Contact Phone:</strong> ${notification.phone}</p>
                            <p><strong>Date Requested:</strong> ${new Date(notification.created_at).toLocaleString()}</p>
                        </div>
                    `;
                    
                    // Set notification ID in hidden field
                    const notificationIdField = document.getElementById('notification-id');
                    if (notificationIdField) {
                        notificationIdField.value = notification.notification_id;
                    }
                    
                    // Show modal
                    if (responseModal) {
                        responseModal.style.display = 'block';
                        document.body.style.overflow = 'hidden';
                    }
                }
            } else {
                showNotification(data.message || 'Failed to retrieve notification details.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        });
    }
    
    // Accept request button
    if (acceptRequestBtn) {
        acceptRequestBtn.addEventListener('click', function() {
            respondToNotification('accept');
        });
    }
    
    // Decline request button
    if (declineRequestBtn) {
        declineRequestBtn.addEventListener('click', function() {
            respondToNotification('decline');
        });
    }
    
    function respondToNotification(action) {
        const notificationId = document.getElementById('notification-id').value;
        
        if (!notificationId) {
            showNotification('Notification ID not found.', 'error');
            return;
        }
        
        fetch('/bloodbridge/php/respond_to_notification.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + notificationId + '&action=' + action
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
                
                // Close modal
                if (responseModal) {
                    responseModal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
                
                // Refresh notifications
                if (checkNotificationsBtn) {
                    checkNotificationsBtn.click();
                }
            } else {
                showNotification(data.message || 'Failed to respond to notification.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        });
    }
    
    // Close modals
    closeBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (responseModal) {
                responseModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === responseModal) {
            responseModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
});
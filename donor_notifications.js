document.addEventListener('DOMContentLoaded', function() {
    console.log('Donor notifications page loaded');
    
    const checkBtn = document.getElementById('check-notifications');
    const emailInput = document.getElementById('email');
    const notificationsContainer = document.getElementById('notifications-container');
    
    console.log('Elements found:', {
        checkBtn: checkBtn,
        emailInput: emailInput,
        notificationsContainer: notificationsContainer
    });
    
    // Check notifications
    if (checkBtn) {
        checkBtn.addEventListener('click', function() {
            console.log('Check notifications button clicked');
            const email = emailInput.value.trim();
            
            if (!email) {
                showNotification('Please enter your email address', 'error');
                return;
            }
            
            console.log('Checking notifications for email:', email);
            
            // Show loading state
            checkBtn.textContent = 'Checking...';
            checkBtn.disabled = true;
            
            fetch(`php/check_notifications.php?email=${encodeURIComponent(email)}`)
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    // Reset button state
                    checkBtn.textContent = 'Check Notifications';
                    checkBtn.disabled = false;
                    
                    console.log('Notifications data:', data);
                    
                    if (data.success) {
                        displayNotifications(data.notifications);
                    } else {
                        notificationsContainer.innerHTML = `<p class="error-message">${data.message}</p>`;
                    }
                })
                .catch(error => {
                    // Reset button state
                    checkBtn.textContent = 'Check Notifications';
                    checkBtn.disabled = false;
                    
                    showNotification('An error occurred. Please try again.', 'error');
                    console.error('Error:', error);
                });
        });
    } else {
        console.error('Check notifications button not found');
    }
    
    // Display notifications
    function displayNotifications(notifications) {
        console.log('Displaying notifications:', notifications);
        
        if (!notifications || notifications.length === 0) {
            notificationsContainer.innerHTML = '<p class="no-notifications">No pending notifications found.</p>';
            return;
        }
        
        let html = '<h3>Pending Notifications</h3>';
        
        notifications.forEach(notification => {
            console.log('Processing notification:', notification);
            
            html += `
                <div class="notification-item" data-id="${notification.notification_id}">
                    <div class="notification-message">
                        <p><strong>Message:</strong> ${notification.message}</p>
                        <p><strong>Patient:</strong> ${notification.patient_name}</p>
                        <p><strong>Blood Group:</strong> ${notification.blood_group}</p>
                        <p><strong>Location:</strong> ${notification.location}</p>
                        <p><strong>Contact:</strong> ${notification.phone}</p>
                    </div>
                    <button class="btn respond-btn" data-id="${notification.notification_id}">Respond</button>
                </div>
            `;
        });
        
        notificationsContainer.innerHTML = html;
        
        // Add event listeners to respond buttons
        const respondBtns = document.querySelectorAll('.respond-btn');
        respondBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const notificationId = this.getAttribute('data-id');
                const notification = notifications.find(n => n.notification_id == notificationId);
                
                if (notification) {
                    console.log('Showing notification details:', notification);
                    showNotificationDetails(notification);
                }
            });
        });
    }
    
    // Show notification details in modal
    function showNotificationDetails(notification) {
        const responseModal = document.getElementById('response-modal');
        const notificationIdInput = document.getElementById('notification-id');
        const notificationDetails = document.getElementById('notification-details');
        
        if (!responseModal || !notificationIdInput || !notificationDetails) {
            console.error('Modal elements not found');
            return;
        }
        
        notificationIdInput.value = notification.notification_id;
        
        notificationDetails.innerHTML = `
            <div class="notification-details-content">
                <p><strong>Message:</strong> ${notification.message}</p>
                <p><strong>Patient:</strong> ${notification.patient_name}</p>
                <p><strong>Blood Group Needed:</strong> ${notification.blood_group}</p>
                <p><strong>Location:</strong> ${notification.location}</p>
                <p><strong>Contact:</strong> ${notification.phone}</p>
            </div>
        `;
        
        responseModal.style.display = 'block';
        
        // Set up response buttons
        const acceptBtn = document.getElementById('accept-request');
        const declineBtn = document.getElementById('decline-request');
        const closeBtn = responseModal.querySelector('.close');
        
        if (acceptBtn) {
            acceptBtn.onclick = function() {
                respondToRequest('accepted');
            };
        }
        
        if (declineBtn) {
            declineBtn.onclick = function() {
                respondToRequest('declined');
            };
        }
        
        if (closeBtn) {
            closeBtn.onclick = function() {
                responseModal.style.display = 'none';
            };
        }
    }
    
    // Respond to request
    function respondToRequest(response) {
        const notificationIdInput = document.getElementById('notification-id');
        const responseModal = document.getElementById('response-modal');
        const acceptBtn = document.getElementById('accept-request');
        const declineBtn = document.getElementById('decline-request');
        
        if (!notificationIdInput) {
            console.error('Notification ID input not found');
            return;
        }
        
        const notificationId = notificationIdInput.value;
        
        const formData = new FormData();
        formData.append('notification_id', notificationId);
        formData.append('response', response);
        
        // Show loading state
        if (acceptBtn) {
            acceptBtn.textContent = 'Processing...';
            acceptBtn.disabled = true;
        }
        if (declineBtn) {
            declineBtn.textContent = 'Processing...';
            declineBtn.disabled = true;
        }
        
        fetch('php/donor_responses.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Reset button state
            if (acceptBtn) {
                acceptBtn.textContent = 'Available';
                acceptBtn.disabled = false;
            }
            if (declineBtn) {
                declineBtn.textContent = 'Not Available';
                declineBtn.disabled = false;
            }
            
            if (data.success) {
                showNotification(data.message);
                responseModal.style.display = 'none';
                
                // Refresh notifications
                if (checkBtn) {
                    checkBtn.click();
                }
                
                // If accepted, show donor details
                if (response === 'accepted' && data.donor_details) {
                    showDonorDetails(data.donor_details);
                }
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            // Reset button state
            if (acceptBtn) {
                acceptBtn.textContent = 'Available';
                acceptBtn.disabled = false;
            }
            if (declineBtn) {
                declineBtn.textContent = 'Not Available';
                declineBtn.disabled = false;
            }
            
            showNotification('An error occurred. Please try again.', 'error');
            console.error('Error:', error);
        });
    }
    
    // Show donor details
    function showDonorDetails(donorDetails) {
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.style.display = 'block';
        
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Donor Details Shared</h2>
                <div class="donor-details">
                    <p><strong>Your contact information has been shared with:</strong></p>
                    <p><strong>Patient Name:</strong> ${donorDetails.patient_name}</p>
                    <p><strong>Patient Blood Group:</strong> ${donorDetails.patient_blood_group}</p>
                    <p><strong>Patient Contact:</strong> ${donorDetails.patient_phone}</p>
                    <p>The patient will contact you directly if needed.</p>
                </div>
                <button class="btn" id="close-details-modal">Close</button>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Close modal
        const closeBtnModal = modal.querySelector('#close-details-modal');
        const closeBtn = modal.querySelector('.close');
        
        if (closeBtnModal) {
            closeBtnModal.addEventListener('click', function() {
                modal.remove();
            });
        }
        
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                modal.remove();
            });
        }
        
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.remove();
            }
        });
    }
    
    // Show notification
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.style.position = 'fixed';
        notification.style.bottom = '20px';
        notification.style.right = '20px';
        notification.style.backgroundColor = type === 'error' ? '#B71C1C' : '#D62828';
        notification.style.color = 'white';
        notification.style.padding = '15px 25px';
        notification.style.borderRadius = '5px';
        notification.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.2)';
        notification.style.display = 'flex';
        notification.style.alignItems = 'center';
        notification.style.justifyContent = 'space-between';
        notification.style.zIndex = '1001';
        notification.style.animation = 'slideUp 0.3s ease';
        
        notification.innerHTML = `
            <span>${message}</span>
            <span class="close-notification" style="margin-left: 15px; cursor: pointer; font-size: 18px;">&times;</span>
        `;
        
        document.body.appendChild(notification);
        
        // Close notification
        const closeNotification = notification.querySelector('.close-notification');
        closeNotification.addEventListener('click', function() {
            notification.remove();
        });
        
        // Auto hide after 5 seconds
        setTimeout(function() {
            notification.remove();
        }, 5000);
    }
    
    // Mobile menu toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
        });
    }
});
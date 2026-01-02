<?php
// get_notifications.php
// Ensure no whitespace or output before this point
ob_start();

// Start session
session_start();

// Disable error display and log errors instead
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/php/logs/php_error_log');

// Set content type header immediately
header('Content-Type: application/json');

// Database connection
try {
    $conn = new mysqli('localhost', 'root', '', 'bloodbridge_db');
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
} catch (Exception $e) {
    $errorMsg = $e->getMessage();
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

// Check if email is provided
if (!isset($_POST['email']) || empty($_POST['email'])) {
    $errorMsg = 'Email is required';
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

 $email = trim($_POST['email']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errorMsg = 'Invalid email format';
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

// Get donor by email
 $stmt = $conn->prepare("SELECT donor_id, name, email FROM donors WHERE email = ?");
 $stmt->bind_param("s", $email);
 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows === 0) {
    $errorMsg = 'No donor found with this email';
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

 $donor = $result->fetch_assoc();
 $donor_id = $donor['donor_id'];

// Get notifications for this donor
 $stmt = $conn->prepare("
    SELECT n.notification_id, n.request_id, n.message, n.status, n.created_at, 
           r.name as patient_name, r.blood_group, r.location, r.phone
    FROM notifications n
    JOIN requests r ON n.request_id = r.request_id
    WHERE n.donor_id = ?
    ORDER BY n.created_at DESC
");
 $stmt->bind_param("i", $donor_id);
 $stmt->execute();
 $result = $stmt->get_result();

 $notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

 $stmt->close();
 $conn->close();

echo json_encode([
    'success' => true,
    'notifications' => $notifications
]);

ob_end_flush();
?>
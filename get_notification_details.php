<?php
// get_notification_details.php
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

// Check if notification ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    $errorMsg = 'Notification ID is required';
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

 $notification_id = intval($_POST['id']);

// Get notification details
 $stmt = $conn->prepare("
    SELECT n.notification_id, n.request_id, n.message, n.status, n.created_at, 
           r.name as patient_name, r.blood_group, r.address, r.location, r.phone
    FROM notifications n
    JOIN requests r ON n.request_id = r.request_id
    WHERE n.notification_id = ?
");
 $stmt->bind_param("i", $notification_id);
 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows === 0) {
    $errorMsg = 'Notification not found';
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

 $notification = $result->fetch_assoc();

 $stmt->close();
 $conn->close();

echo json_encode([
    'success' => true,
    'notification' => $notification
]);

ob_end_flush();
?>
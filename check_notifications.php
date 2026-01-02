<?php
session_start();
header('Content-Type: application/json');

// Database connection
 $conn = new mysqli('localhost', 'root', '', 'bloodbridge_db');

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get email from query parameter
 $email = trim($_GET['email']);

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Find donor by email
 $stmt = $conn->prepare("SELECT donor_id FROM donors WHERE email = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}
 $stmt->bind_param("s", $email);
 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No donor found with this email']);
    exit;
}

 $donor = $result->fetch_assoc();
 $donor_id = $donor['donor_id'];

// Get pending notifications for this donor
 $stmt = $conn->prepare("
    SELECT n.notification_id, n.message, r.name as patient_name, r.blood_group, r.location, r.phone
    FROM notifications n
    JOIN requests r ON n.request_id = r.request_id
    WHERE n.donor_id = ? AND n.status = 'pending'
");
 $stmt->bind_param("i", $donor_id);
 $stmt->execute();
 $result = $stmt->get_result();

 $notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode([
    'success' => true,
    'notifications' => $notifications
]);

 $stmt->close();
 $conn->close();
?>
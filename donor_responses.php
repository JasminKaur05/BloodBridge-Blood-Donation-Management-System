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

// Get form data
 $notification_id = intval($_POST['notification_id']);
 $response = trim($_POST['response']);

// Validate inputs
if (empty($notification_id) || empty($response)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validate response
if (!in_array($response, ['accepted', 'declined'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid response']);
    exit;
}

// Update notification status
 $stmt = $conn->prepare("UPDATE notifications SET status = ?, responded_at = NOW() WHERE notification_id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}
 $stmt->bind_param("si", $response, $notification_id);

if ($stmt->execute()) {
    // If accepted, get donor and request details
    if ($response === 'accepted') {
        $stmt = $conn->prepare("
            SELECT d.name as donor_name, d.phone as donor_phone, d.email as donor_email,
                   r.name as patient_name, r.blood_group as patient_blood_group, r.phone as patient_phone
            FROM notifications n
            JOIN donors d ON n.donor_id = d.donor_id
            JOIN requests r ON n.request_id = r.request_id
            WHERE n.notification_id = ?
        ");
        $stmt->bind_param("i", $notification_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $details = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'message' => 'Response recorded successfully! Your contact information has been shared with the patient.',
            'donor_details' => $details
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Response recorded successfully.'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to record response']);
}

 $stmt->close();
 $conn->close();
?>
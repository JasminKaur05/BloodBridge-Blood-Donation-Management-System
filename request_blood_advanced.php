<?php
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

// Check if form data is set
 $requiredFields = ['name', 'blood_group', 'address', 'location', 'phone'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field])) {
        $errorMsg = "Missing required field: $field";
        echo json_encode(['success' => false, 'message' => $errorMsg]);
        exit;
    }
}

// Get and sanitize form data
 $name = trim($_POST['name']);
 $blood_group = trim($_POST['blood_group']);
 $address = trim($_POST['address']);
 $location = trim($_POST['location']);
 $phone = trim($_POST['phone']);

// Validate inputs
if (empty($name) || empty($blood_group) || empty($address) || empty($location) || empty($phone)) {
    $errorMsg = 'All fields are required';
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

// Validate blood group
 $valid_blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
if (!in_array($blood_group, $valid_blood_groups)) {
    $errorMsg = 'Invalid blood group';
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

// Insert blood request data
 $stmt = $conn->prepare("INSERT INTO requests (name, blood_group, address, location, phone) VALUES (?, ?, ?, ?, ?)");
 $stmt->bind_param("sssss", $name, $blood_group, $address, $location, $phone);

if ($stmt->execute()) {
    $request_id = $stmt->insert_id;
    
    // Find matching donors based on blood group
    $donor_query = "SELECT donor_id, name, phone, email FROM donors WHERE blood_group = ? AND availability = 1";
    $donor_stmt = $conn->prepare($donor_query);
    $donor_stmt->bind_param("s", $blood_group);
    $donor_stmt->execute();
    $donor_result = $donor_stmt->get_result();
    
    $matching_donors = [];
    while ($donor = $donor_result->fetch_assoc()) {
        $matching_donors[] = $donor;
    }
    
    // Create notifications for matching donors
    foreach ($matching_donors as $donor) {
        $notification_message = "A blood request for $blood_group blood has been made by $name at $location. Please respond if you are available to donate.";
        
        $notification_stmt = $conn->prepare("INSERT INTO notifications (request_id, donor_id, message) VALUES (?, ?, ?)");
        $notification_stmt->bind_param("iis", $request_id, $donor['donor_id'], $notification_message);
        $notification_stmt->execute();
        $notification_stmt->close();
    }
    
    $donor_stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Blood request submitted successfully! ' . count($matching_donors) . ' potential donors have been notified.',
        'request_id' => $request_id,
        'matching_donors_count' => count($matching_donors)
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Blood request failed: ' . $stmt->error]);
}

 $stmt->close();
 $conn->close();

ob_end_flush();
?>
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
 $requiredFields = ['name', 'age', 'gender', 'blood_group', 'phone', 'email', 'address'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field])) {
        $errorMsg = "Missing required field: $field";
        echo json_encode(['success' => false, 'message' => $errorMsg]);
        exit;
    }
}

// Get and sanitize form data
 $name = trim($_POST['name']);
 $age = intval($_POST['age']);
 $gender = trim($_POST['gender']);
 $blood_group = trim($_POST['blood_group']);
 $phone = trim($_POST['phone']);
 $email = trim($_POST['email']);
 $address = trim($_POST['address']);

// Validate inputs
if (empty($name) || empty($age) || empty($gender) || empty($blood_group) || 
    empty($phone) || empty($email) || empty($address)) {
    $errorMsg = 'All fields are required';
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

// Validate age
if ($age < 18 || $age > 65) {
    $errorMsg = 'Age must be between 18 and 65';
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

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errorMsg = 'Invalid email format';
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

// Check if donor already exists with the same email
 $stmt = $conn->prepare("SELECT donor_id FROM donors WHERE email = ?");
 $stmt->bind_param("s", $email);
 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows > 0) {
    $errorMsg = 'A donor with this email already exists';
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

 $stmt->close();

// Insert donor data
 $stmt = $conn->prepare("INSERT INTO donors (name, age, gender, blood_group, phone, email, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
 $stmt->bind_param("sisssss", $name, $age, $gender, $blood_group, $phone, $email, $address);

if ($stmt->execute()) {
    $donor_id = $stmt->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Donor registration successful!',
        'donor_id' => $donor_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Donor registration failed: ' . $stmt->error]);
}

 $stmt->close();
 $conn->close();

ob_end_flush();
?>
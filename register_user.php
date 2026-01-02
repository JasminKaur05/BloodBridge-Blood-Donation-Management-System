<?php
// Start session first (before any output)
session_start();

// Enable error reporting but log to file instead of displaying
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/php/logs/php_error_log');

// Set content type header immediately
header('Content-Type: application/json');

// Database connection
 $conn = new mysqli('localhost', 'root', '', 'bloodbridge_db');

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get form data
 $full_name = trim($_POST['full_name']);
 $email = trim($_POST['email']);
 $password = $_POST['password'];

// Validate inputs
if (empty($full_name) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Check if email already exists
 $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}
 $stmt->bind_param("s", $email);
 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit;
}

// Hash password
 $password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
 $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}
 $stmt->bind_param("sss", $full_name, $email, $password_hash);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    
    // Set session
    $_SESSION['user'] = [
        'user_id' => $user_id,
        'full_name' => $full_name,
        'email' => $email
    ];
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! Redirecting to login...',
        'user' => $_SESSION['user']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $stmt->error]);
}

 $stmt->close();
 $conn->close();
?>
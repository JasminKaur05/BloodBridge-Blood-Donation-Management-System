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
 $username = trim($_POST['username']);
 $password = $_POST['password'];

// Validate inputs
if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit;
}

// Check if user exists
 $stmt = $conn->prepare("SELECT user_id, full_name, email, password FROM users WHERE email = ? OR full_name = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}
 $stmt->bind_param("ss", $username, $username);
 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    exit;
}

 $user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    exit;
}

// Set session
 $_SESSION['user'] = [
    'user_id' => $user['user_id'],
    'full_name' => $user['full_name'],
    'email' => $user['email']
];

echo json_encode([
    'success' => true,
    'message' => 'Login successful! Redirecting...',
    'user' => $_SESSION['user']
]);

 $stmt->close();
 $conn->close();
?>
<?php
// Create a file named check_database.php in your php directory
// File path: C:\xampp\htdocs\bloodbridge\php\check_database.php

header('Content-Type: text/plain');

 $conn = new mysqli('localhost', 'root', '', 'bloodbridge_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== Database Connection: OK ===\n\n";

// Check if tables exist
 $tables = ['users', 'donors', 'requests', 'notifications', 'admin'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "Table '$table': EXISTS\n";
    } else {
        echo "Table '$table': MISSING\n";
    }
}

echo "\n=== Users Table Structure ===\n";
 $result = $conn->query("DESCRIBE users");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . ($row['Key'] == 'PRI' ? 'PRIMARY KEY' : '') . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n=== Donors Table Structure ===\n";
 $result = $conn->query("DESCRIBE donors");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . ($row['Key'] == 'PRI' ? 'PRIMARY KEY' : '') . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n=== Sample Users Data ===\n";
 $result = $conn->query("SELECT * FROM users LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['user_id'] . ", Name: " . $row['full_name'] . ", Email: " . $row['email'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n=== Sample Donors Data ===\n";
 $result = $conn->query("SELECT * FROM donors LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "Donor ID: " . $row['donor_id'] . ", User ID: " . $row['user_id'] . ", Name: " . $row['name'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

 $conn->close();
?>
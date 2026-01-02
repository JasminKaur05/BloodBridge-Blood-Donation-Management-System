<?php
// Create a file named remove_user_id.php in your php directory
// File path: C:\xampp\htdocs\bloodbridge\php\remove_user_id.php

header('Content-Type: text/plain');

 $conn = new mysqli('localhost', 'root', '', 'bloodbridge_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== Database Connection: OK ===\n\n";

// Step 1: Drop the foreign key constraint if it exists
echo "Step 1: Checking for foreign key constraint...\n";
 $result = $conn->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = 'bloodbridge_db' AND TABLE_NAME = 'donors' AND CONSTRAINT_TYPE = 'FOREIGN KEY'");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $constraint_name = $row['CONSTRAINT_NAME'];
    echo "Found foreign key constraint: $constraint_name\n";
    
    $drop_fk_sql = "ALTER TABLE donors DROP FOREIGN KEY $constraint_name";
    if ($conn->query($drop_fk_sql) === TRUE) {
        echo "Foreign key constraint dropped successfully\n";
    } else {
        echo "Error dropping foreign key: " . $conn->error . "\n";
    }
} else {
    echo "No foreign key constraint found\n";
}

// Step 2: Remove the user_id column
echo "\nStep 2: Removing user_id column...\n";
 $sql = "ALTER TABLE donors DROP COLUMN user_id";
if ($conn->query($sql) === TRUE) {
    echo "Column user_id removed successfully\n";
} else {
    echo "Error removing column: " . $conn->error . "\n";
}

// Step 3: Verify the structure
echo "\nStep 3: Verifying updated donors table structure...\n";
 $result = $conn->query("DESCRIBE donors");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . ($row['Key'] == 'PRI' ? 'PRIMARY KEY' : ($row['Key'] == 'MUL' ? 'FOREIGN KEY' : '')) . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

// Step 4: Verify data
echo "\nStep 4: Verifying donors data...\n";
 $result = $conn->query("SELECT donor_id, name, email FROM donors LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "Donor ID: " . $row['donor_id'] . ", Name: " . $row['name'] . ", Email: " . $row['email'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

 $conn->close();
?>
<?php
// Create a file named fix_database.php in your php directory
// File path: C:\xampp\htdocs\bloodbridge\php\fix_database.php

header('Content-Type: text/plain');

 $conn = new mysqli('localhost', 'root', '', 'bloodbridge_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== Database Connection: OK ===\n\n";

// Step 1: Add user_id column to donors table
echo "Step 1: Adding user_id column to donors table...\n";
 $sql = "ALTER TABLE donors ADD COLUMN user_id INT NOT NULL AFTER donor_id";
if ($conn->query($sql) === TRUE) {
    echo "Column user_id added successfully\n";
} else {
    echo "Error adding column: " . $conn->error . "\n";
}

// Step 2: Add foreign key constraint
echo "\nStep 2: Adding foreign key constraint...\n";
 $sql = "ALTER TABLE donors ADD CONSTRAINT fk_donor_user FOREIGN KEY (user_id) REFERENCES users(user_id)";
if ($conn->query($sql) === TRUE) {
    echo "Foreign key constraint added successfully\n";
} else {
    echo "Error adding foreign key: " . $conn->error . "\n";
    // If foreign key fails, we might need to update existing donor records first
    echo "Attempting to update existing donor records...\n";
    
    // Get all donor records
    $result = $conn->query("SELECT donor_id, name, email FROM donors");
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Find matching user by email
            $user_result = $conn->query("SELECT user_id FROM users WHERE email = '" . $row['email'] . "'");
            if ($user_result->num_rows > 0) {
                $user_row = $user_result->fetch_assoc();
                $update_sql = "UPDATE donors SET user_id = " . $user_row['user_id'] . " WHERE donor_id = " . $row['donor_id'];
                if ($conn->query($update_sql) === TRUE) {
                    echo "Updated donor " . $row['name'] . " with user_id " . $user_row['user_id'] . "\n";
                } else {
                    echo "Error updating donor " . $row['name'] . ": " . $conn->error . "\n";
                }
            }
        }
        
        // Try adding the foreign key again
        echo "Retrying to add foreign key constraint...\n";
        $sql = "ALTER TABLE donors ADD CONSTRAINT fk_donor_user FOREIGN KEY (user_id) REFERENCES users(user_id)";
        if ($conn->query($sql) === TRUE) {
            echo "Foreign key constraint added successfully\n";
        } else {
            echo "Error adding foreign key: " . $conn->error . "\n";
        }
    } else {
        echo "No donor records found\n";
    }
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
echo "\nStep 4: Verifying donors data with user_id...\n";
 $result = $conn->query("SELECT donor_id, user_id, name, email FROM donors LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "Donor ID: " . $row['donor_id'] . ", User ID: " . $row['user_id'] . ", Name: " . $row['name'] . ", Email: " . $row['email'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

 $conn->close();
?>
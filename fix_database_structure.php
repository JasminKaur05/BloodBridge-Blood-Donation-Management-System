<?php
header('Content-Type: text/plain');

 $conn = new mysqli('localhost', 'root', '', 'bloodbridge_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== Database Connection: OK ===\n\n";

// Step 1: Remove user_id from requests table if it exists
echo "Step 1: Checking for user_id column in requests table...\n";
 $result = $conn->query("SHOW COLUMNS FROM requests LIKE 'user_id'");
if ($result && $result->num_rows > 0) {
    echo "Found user_id column in requests table\n";
    
    // Drop foreign key constraint if it exists
    $fk_result = $conn->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = 'bloodbridge_db' AND TABLE_NAME = 'requests' AND CONSTRAINT_TYPE = 'FOREIGN KEY'");
    if ($fk_result && $fk_result->num_rows > 0) {
        $fk_row = $fk_result->fetch_assoc();
        $constraint_name = $fk_row['CONSTRAINT_NAME'];
        echo "Found foreign key constraint: $constraint_name\n";
        
        $drop_fk_sql = "ALTER TABLE requests DROP FOREIGN KEY $constraint_name";
        if ($conn->query($drop_fk_sql) === TRUE) {
            echo "Foreign key constraint dropped successfully\n";
        } else {
            echo "Error dropping foreign key: " . $conn->error . "\n";
        }
    }
    
    // Remove the column
    $drop_col_sql = "ALTER TABLE requests DROP COLUMN user_id";
    if ($conn->query($drop_col_sql) === TRUE) {
        echo "Column user_id removed successfully\n";
    } else {
        echo "Error removing column: " . $conn->error . "\n";
    }
} else {
    echo "user_id column not found in requests table\n";
}

// Step 2: Verify the structure
echo "\nStep 2: Verifying updated requests table structure...\n";
 $result = $conn->query("DESCRIBE requests");
if ($result) {
    echo "Requests table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n=== Database Fix Complete ===\n";
 $conn->close();
?>
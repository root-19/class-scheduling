<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Add exam column if it doesn't exist
    $sql = "ALTER TABLE grades ADD COLUMN IF NOT EXISTS exam DECIMAL(5,2) DEFAULT NULL AFTER `final`";
    
    if ($conn->query($sql)) {
        echo "Successfully added exam column to grades table\n";
    } else {
        echo "Error adding exam column: " . $conn->error . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 
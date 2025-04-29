<?php
require_once __DIR__ . '/../Database.php';

use App\Config\Database;

try {
    $database = new Database();
    $conn = $database->connect();

    // Add exam column to grades table
    $sql = "ALTER TABLE grades ADD COLUMN exam DECIMAL(5,2) DEFAULT NULL";
    $conn->exec($sql);

    echo "Successfully added exam column to grades table";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 
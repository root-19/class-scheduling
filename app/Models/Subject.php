<?php

namespace App\Models;

use App\Config\Database;
// use App\Config\Database;
class Subject {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // Add subject
    public function addSubject($subject_names, $descriptions, $units) {
        $stmt = $this->conn->prepare("INSERT INTO subjects (subject_name, unit, description) VALUES (?, ?, ?)");
    
        for ($i = 0; $i < count($subject_names); $i++) {
            $stmt->bindParam(1, $subject_names[$i]);
            $stmt->bindParam(2, $units[$i]);
            $stmt->bindParam(3, $descriptions[$i]);
            $stmt->execute();
        }
    
        return true;
    }
    

    // Get all subjects
    public function getSubjects() {
        $result = $this->conn->query("SELECT * FROM subjects");
        return $result->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Delete subject
    public function deleteSubject($id) {
        $stmt = $this->conn->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }

    // Get total subjects count
    public function getTotalSubjects() {
        $query = "SELECT COUNT(*) as total FROM subjects";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}

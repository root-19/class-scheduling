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
    public function addSubject($subject_name, $description) {
        $stmt = $this->conn->prepare("INSERT INTO subjects (subject_name, description) VALUES (?, ?)");
        $stmt->bindParam(1, $subject_name);
        $stmt->bindParam(2, $description);
        return $stmt->execute();
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
}

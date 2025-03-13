<?php

namespace App\Models;

use App\Config\Database;

class Faculty {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Fetch all faculty
    public function getAllFaculty() {
        $stmt = $this->conn->prepare("SELECT * FROM faculty");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Get single faculty by ID
    public function getFacultyById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM faculty WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Add faculty
    public function addFaculty($facultyId, $name, $email, $contact,$address) {
        $stmt = $this->conn->prepare("INSERT INTO faculty (faculty_id, name, email, contact, address) VALUES (?, ?, ?, ?,?)");
        return $stmt->execute([$facultyId, $name, $email, $contact,$address]);
    }

    // Update faculty
    public function updateFaculty($id, $facultyId, $name, $email, $contact,$address) {
        $stmt = $this->conn->prepare("UPDATE faculty SET faculty_id = ?, name = ?, email = ?, contact = ?, address = ? WHERE id = ?");
        return $stmt->execute([$facultyId, $name, $email, $contact,$address, $id]);
    }

    // Delete faculty
    public function deleteFaculty($id) {
        $stmt = $this->conn->prepare("DELETE FROM faculty WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
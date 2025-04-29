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

// Add faculty with password hashing
public function addFaculty($facultyId, $name, $email, $contact, $address, $password) {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT); // Hash password

    $stmt = $this->conn->prepare("INSERT INTO faculty (faculty_id, name, email, contact, address, password) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$facultyId, $name, $email, $contact, $address, $hashedPassword]); // Store hashed password
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

    // Get total faculty count
    public function getTotalFaculty() {
        $query = "SELECT COUNT(*) as total FROM faculty";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
?>
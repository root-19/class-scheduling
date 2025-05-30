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
    public function addFaculty($facultyId, $name, $email, $contact, $address, $subjects, $password) {
        try {
            // Get subject names from IDs
            $subjectNames = [];
            if (!empty($subjects)) {
                $placeholders = str_repeat('?,', count($subjects) - 1) . '?';
                $stmt = $this->conn->prepare("SELECT subject_name FROM subjects WHERE id IN ($placeholders)");
                $stmt->execute($subjects);
                $subjectNames = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            }
            
            // Convert subjects array to comma-separated string
            $subjectsString = !empty($subjectNames) ? implode(', ', $subjectNames) : '';
            
            // Insert faculty member with subjects
            $stmt = $this->conn->prepare("INSERT INTO faculty (faculty_id, name, email, contact, address, subjects, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            return $stmt->execute([$facultyId, $name, $email, $contact, $address, $subjectsString, $hashedPassword]);
        } catch (\PDOException $e) {
            error_log("Error adding faculty: " . $e->getMessage());
            return false;
        }
    }

    // Update faculty
    public function updateFaculty($id, $facultyId, $name, $email, $contact, $address, $subjects) {
        try {
            // Get subject names from IDs and store both IDs and names
            $subjectData = [];
            if (!empty($subjects)) {
                $placeholders = str_repeat('?,', count($subjects) - 1) . '?';
                $stmt = $this->conn->prepare("SELECT id, subject_name FROM subjects WHERE id IN ($placeholders)");
                $stmt->execute($subjects);
                $subjectData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            
            // Store both IDs and names in JSON format for better data integrity
            $subjectsJson = json_encode($subjectData);
            
            // Update faculty member with subjects
            $stmt = $this->conn->prepare("UPDATE faculty SET faculty_id = ?, name = ?, email = ?, contact = ?, address = ?, subjects = ? WHERE id = ?");
            return $stmt->execute([$facultyId, $name, $email, $contact, $address, $subjectsJson, $id]);
        } catch (\PDOException $e) {
            error_log("Error updating faculty: " . $e->getMessage());
            return false;
        }
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

    public function getFacultySubjects($facultyId) {
        $stmt = $this->conn->prepare("SELECT subjects FROM faculty WHERE id = ?");
        $stmt->execute([$facultyId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($result && !empty($result['subjects'])) {
            try {
                // Decode the JSON data
                $subjectData = json_decode($result['subjects'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($subjectData)) {
                    return $subjectData;
                }
                
                // Fallback for old comma-separated format
                $subjectNames = array_map('trim', explode(',', $result['subjects']));
                $placeholders = str_repeat('?,', count($subjectNames) - 1) . '?';
                $stmt = $this->conn->prepare("SELECT * FROM subjects WHERE subject_name IN ($placeholders)");
                $stmt->execute($subjectNames);
                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                error_log("Error getting faculty subjects: " . $e->getMessage());
                return [];
            }
        }
        return [];
    }
}
?>
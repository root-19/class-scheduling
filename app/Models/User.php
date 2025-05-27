<?php
namespace App\Models;

use PDO;

class User {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function findUserByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function register($firstName, $lastName, $student_id, $contact, $email, $password, $role, $imageName, $subjects, $sections, $prelim, $semester, $faculty, $course) {
        $query = "INSERT INTO {$this->table} 
            (first_name, last_name, student_id, contact, email, password, role, image, subjects, sections, prelim, semester, faculty, course) 
            VALUES 
            (:first_name, :last_name, :student_id, :contact, :email, :password, :role, :image, :subjects, :sections, :prelim, :semester, :faculty, :course)";
        
        $stmt = $this->conn->prepare($query);

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // Convert subjects array to comma-separated string
        $subjectsStr = is_array($subjects) ? implode(', ', $subjects) : $subjects;
        // Remove any quotes from sections
        $sectionsStr = is_string($sections) ? trim($sections, '"') : $sections;

        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':contact', $contact);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':image', $imageName);
        $stmt->bindParam(':subjects', $subjectsStr);
        $stmt->bindParam(':sections', $sectionsStr);
        $stmt->bindParam(':prelim', $prelim);
        $stmt->bindParam(':semester', $semester);
        $stmt->bindParam(':faculty', $faculty);
        $stmt->bindParam(':course', $course);

        return $stmt->execute();
    }
}

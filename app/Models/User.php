<?php
namespace App\Models;

use PDO;

class User {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Find user by email
    public function findUserByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function register($firstName, $lastName, $student_id, $contact, $email, $password, $role, $imageName, $subjects, $sections,$prelim,$semester) {
        $query = "INSERT INTO users (first_name, last_name, student_id, contact, email, password, role, image, subjects, sections, prelim, semester) 
                  VALUES (:first_name, :last_name, :student_id, :contact, :email, :password, :role, :image, :subjects, :sections, :prelim, :semester)";
        
        $stmt = $this->conn->prepare($query);
    
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $encodedSubjects = json_encode($subjects);
        $encodedSections = json_encode($sections);
    
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':contact', $contact);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':image', $imageName);
        $stmt->bindParam(':subjects', $encodedSubjects);
        $stmt->bindParam(':sections', $encodedSections);
        $stmt->bindParam(':prelim', $prelim);
        $stmt->bindParam(':semester', $semester);

    
        return $stmt->execute();
    }
}    
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

    // Register new user
    public function register($firstName, $lastName, $email,  $student_id, $contact, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO " . $this->table . " (first_name, last_name, email,  student_id, contact,password, role) 
                  VALUES (:first_name, :last_name, :email, :student_id, :contact,  :password, 'student')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':contact', $contact);
        $stmt->bindParam(':password', $hashedPassword);

        return $stmt->execute();
    }
}

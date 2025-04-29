<?php
namespace App\Controllers;
require_once __DIR__ . '/../Config/Database.php'; // Ensure this file exists

use App\Config\Database;
use App\Models\User;
use PDO;

class StudentController {
    private $userModel;
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
        $this->userModel = new User($this->conn);
    }

    public function getAllStudents() {
        $query = "SELECT * FROM users WHERE role = 'student'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteStudent($id) {
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getTotalStudents() {
        $query = "SELECT COUNT(*) as total FROM users WHERE role = 'student'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}

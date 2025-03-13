<?php

namespace App\Models;

use App\Config\Database;

class Course {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // Add course
    public function addCourse($course_name, $description) {
        $stmt = $this->conn->prepare("INSERT INTO course (course_name, description) VALUES (?, ?)");
        $stmt->bindParam(1, $course_name);
        $stmt->bindParam(2, $description);
        return $stmt->execute();
    }

    // Get all courses
    public function getCourse() {
        $result = $this->conn->query("SELECT * FROM course");
        return $result->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Get a single course by ID
    public function getCourseById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM course WHERE id = ?");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Update course
    public function updateCourse($id, $course_name, $description) {
        $stmt = $this->conn->prepare("UPDATE course SET course_name = ?, description = ? WHERE id = ?");
        $stmt->bindParam(1, $course_name);
        $stmt->bindParam(2, $description);
        $stmt->bindParam(3, $id);
        return $stmt->execute();
    }

    // Delete course
    public function deleteCourse($id) {
        $stmt = $this->conn->prepare("DELETE FROM course WHERE id = ?");
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
}

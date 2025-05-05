<?php
namespace App\Controllers;

use App\Models\Subject;
use App\Config\Database;
use PDO;

class SubjectController {
    private $subject;
    private $db;

    public function __construct() {
        $this->subject = new Subject();
        $database = new Database();
        $this->db = $database->connect();
    }

    // Handle form submission
    public function handleRequest() {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_subject'])) {
            $subject_names = $_POST['subject_name'];
            $descriptions = $_POST['description'];
            $units = $_POST['unit'];
            $this->subject->addSubject($subject_names, $descriptions, $units);
            header("Location: dashboard.php");
            exit();
        }
    
        if (isset($_GET['delete'])) {
            $id = $_GET['delete'];
            $this->subject->deleteSubject($id);
            header("Location: dashboard.php");
            exit();
        }
    }
    
    public function getSubjects() {
        return $this->subject->getSubjects();
    }

    public function getTotalSubjects() {
        return $this->subject->getTotalSubjects();
    }

    public function getSubjectsByFacultyId($facultyId) {
        try {
            $query = "SELECT * FROM subjects WHERE faculty_id = :faculty_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['faculty_id' => $facultyId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching subjects: " . $e->getMessage());
            return [];
        }
    }

    public function getTodaySchedule($facultyId) {
        try {
            $query = "SELECT s.subject_name, sc.start_time, sc.room 
                     FROM schedules sc 
                     JOIN subjects s ON sc.subject_id = s.id 
                     WHERE s.faculty_id = :faculty_id 
                     AND DATE(sc.schedule_date) = CURDATE() 
                     ORDER BY sc.start_time ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['faculty_id' => $facultyId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching schedule: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalStudents($facultyId) {
        try {
            $query = "SELECT COUNT(DISTINCT se.student_id) as total_students 
                     FROM subject_enrollments se 
                     JOIN subjects s ON se.subject_id = s.id 
                     WHERE s.faculty_id = :faculty_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['faculty_id' => $facultyId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_students'] ?? 0;
        } catch (\PDOException $e) {
            error_log("Error counting students: " . $e->getMessage());
            return 0;
        }
    }
}
?>

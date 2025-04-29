<?php
namespace App\Controllers;

use App\Models\Subject;

class SubjectController {
    private $subject;

    public function __construct() {
        $this->subject = new Subject();
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
}
?>

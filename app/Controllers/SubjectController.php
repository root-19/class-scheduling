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
            $subject_name = $_POST['subject_name'];
            $description = $_POST['description'];
            $this->subject->addSubject($subject_name, $description);
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
}
?>

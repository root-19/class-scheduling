<?php

namespace App\Controllers;

use App\Models\Course;

class CourseController {
    private $course;

    public function __construct() {
        $this->course = new Course();
    }


    public function handleRequest() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST['add_course'])) {
                $course_name = $_POST['course_name'];
                $description = $_POST['description'];
                $this->course->addCourse($course_name, $description);
                header("Location: course.php");
                exit();
            }

            if (isset($_POST['update_course'])) {
                $id = $_POST['course_id'];
                $course_name = $_POST['course_name'];
                $description = $_POST['description'];
                $this->course->updateCourse($id, $course_name, $description);
                header("Location: course.php");
                exit();
            }
        }

        if (isset($_GET['delete'])) {
            $id = $_GET['delete'];
            $this->course->deleteCourse($id);
            header("Location: course.php");
            exit();
        }
    }

    public function getCourse() {
        return $this->course->getCourse();
    }

    public function getCourseById($id) {
        return $this->course->getCourseById($id);
    }
}

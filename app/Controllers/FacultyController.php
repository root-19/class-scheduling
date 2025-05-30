<?php

namespace App\Controllers;

use App\Models\Faculty;

class FacultyController {
    private $facultyModel;

    public function __construct() {
        $this->facultyModel = new Faculty();
    }

    public function listFaculty() {
        return $this->facultyModel->getAllFaculty();
    }

    public function addFaculty($facultyId, $name, $email, $contact, $address, $subjects, $password) {
        return $this->facultyModel->addFaculty($facultyId, $name, $email, $contact, $address, $subjects, $password);
    }
    

    public function getFaculty($id) {
        return $this->facultyModel->getFacultyById($id);
    }

    public function updateFaculty($id, $facultyId, $name, $email, $contact, $address, $subjects) {
        return $this->facultyModel->updateFaculty($id, $facultyId, $name, $email, $contact, $address, $subjects);
    }


    
    public function deleteFaculty($id) {
        return $this->facultyModel->deleteFaculty($id);
    }

    public function getTotalFaculty() {
        return $this->facultyModel->getTotalFaculty();
    }

    public function getFacultySubjects($facultyId) {
        return $this->facultyModel->getFacultySubjects($facultyId);
    }
}


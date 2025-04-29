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

    public function addFaculty($facultyId, $name, $email, $contact, $address, $password) {
        return $this->facultyModel->addFaculty($facultyId, $name, $email, $contact, $address, $password);
    }
    

    public function getFaculty($id) {
        return $this->facultyModel->getFacultyById($id);
    }

    public function updateFaculty($id, $facultyId, $name, $email, $contact,$address) {
        return $this->facultyModel->updateFaculty($id, $facultyId, $name, $email, $contact,$address);
    }

    public function deleteFaculty($id) {
        return $this->facultyModel->deleteFaculty($id);
    }

    public function getTotalFaculty() {
        return $this->facultyModel->getTotalFaculty();
    }
}


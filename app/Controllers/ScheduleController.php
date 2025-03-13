<?php

namespace App\Controllers;

use App\Models\Schedule;

class ScheduleController {
    private $scheduleModel;

    public function __construct() {
        $this->scheduleModel = new Schedule();
    }

    public function showSchedules() {
        return $this->scheduleModel->getAllSchedules();
    }

    public function getSchedulesJson() {
        header('Content-Type: application/json');
        echo json_encode($this->scheduleModel->getAllSchedules());
        exit();
    }

    public function addNewSchedule() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);

            // ✅ Check if all fields exist and are not empty
            $requiredFields = ['faculty', 'day_of_week', 'subject', 'month_from', 'month_to', 'room', 'department', 'time_from', 'time_to', 'course', 'section', 'ratio'];

            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty(trim($data[$field]))) {
                    echo json_encode(["status" => "error", "message" => "$field is required"]);
                    exit();
                }
            }

            // ✅ Insert into DB and check if successful
            if ($this->scheduleModel->addSchedule(
                $data['faculty'], $data['day_of_week'], $data['subject'],
                $data['month_from'], $data['month_to'], $data['room'],
                $data['department'], $data['time_from'], $data['time_to'],
                $data['course'], $data['section'], $data['ratio']
            )) {
                echo json_encode(["status" => "success", "message" => "Schedule added successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to add schedule"]);
            }
            exit();
        }
    }
}

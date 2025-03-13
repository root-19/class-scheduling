<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

class Schedule {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAllSchedules() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM schedules");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => "Database error: " . $e->getMessage()];
        }
    }

    public function addSchedule($faculty, $day, $subject, $monthFrom, $monthTo, $room, $department, $timeFrom, $timeTo, $course, $section, $ratio) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO schedules (faculty, day_of_week, subject, month_from, month_to, room, department, time_from, time_to, course, section, ratio) VALUES (:faculty, :day, :subject, :monthFrom, :monthTo, :room, :department, :timeFrom, :timeTo, :course, :section, :ratio)");
            
            return $stmt->execute([
                ':faculty' => $faculty,
                ':day' => $day,
                ':subject' => $subject,
                ':monthFrom' => $monthFrom,
                ':monthTo' => $monthTo,
                ':room' => $room,
                ':department' => $department,
                ':timeFrom' => $timeFrom,
                ':timeTo' => $timeTo,
                ':course' => $course,
                ':section' => $section,
                ':ratio' => $ratio
            ]);
        } catch (PDOException $e) {
            error_log("Error inserting schedule: " . $e->getMessage());
            return false;
        }
    }
}

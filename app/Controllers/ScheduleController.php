<?php
namespace App\Controllers;

require_once __DIR__ . '/../Models/Schedule.php';
require_once __DIR__ . '/../Config/Database.php';

use App\Models\Schedule;
use App\Config\Database;
use PDO;

class ScheduleController {
    private $scheduleModel;
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
        $this->scheduleModel = new Schedule($this->conn);
    }

    public function getSchedules($department = null) {
        $query = "SELECT id, faculty, room, department, subject, course, section, time_from, time_to, month_from, building FROM schedules";
        if ($department) {
            $query .= " WHERE department = :department";
        }

        $stmt = $this->conn->prepare($query);

        if ($department) {
            $stmt->bindParam(':department', $department, PDO::PARAM_STR);
        }

        $stmt->execute();
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $events = [];
        foreach ($schedules as $schedule) {
            if (empty($schedule['subject']) || $schedule['subject'] === 'No Class') {
                continue;
            }

            $date = date('Y') . '-' . date('m-d', strtotime($schedule['month_from']));

            $events[] = [
                'id' => $schedule['id'],
                'title' => $schedule['subject'] . ' ' . $schedule['time_from'],
                'start' => $date,
                'extendedProps' => [
                    'faculty' => $schedule['faculty'],
                    'room' => $schedule['room'],
                    'department' => $schedule['department'],
                    'course' => $schedule['course'],
                    'section' => $schedule['section'],
                    'time_from' => $schedule['time_from'],
                    'time_to' => $schedule['time_to'],
                    'building' => $schedule['building']

                ]
            ];
        }

        return $events;
    }

    public function getDepartments() {
        $query = "SELECT DISTINCT department FROM schedules ORDER BY department";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addSchedule() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'faculty' => $_POST['faculty'] ?? '',
                'day_of_week' => $_POST['day_of_week'] ?? '',
                'subject' => $_POST['subject'] ?? '',
                'month_from' => $_POST['month_from'] ?? '',
                'month_to' => $_POST['month_to'] ?? '',
                'room' => $_POST['room'] ?? '',
                'department' => $_POST['department'] ?? '',
                'time_from' => $_POST['time_from'] ?? '',
                'time_to' => $_POST['time_to'] ?? '',
                'course' => $_POST['course'] ?? '',
                'section' => $_POST['section'] ?? '',
                'building' => $_POST['building'] ?? ''

            ];

            if ($this->scheduleModel->addSchedule($data)) {
                echo json_encode(['status' => 'success', 'message' => 'Schedule added successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add schedule']);
            }
            exit();
        }
    }
}


// Handle requests
$scheduleController = new ScheduleController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scheduleController->addSchedule();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $scheduleController->getSchedules();
}
?>

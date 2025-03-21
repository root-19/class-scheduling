<?php
namespace App\Controllers;

require_once __DIR__ . '/../Models/Schedule.php';
require_once __DIR__ . '/../Config/Database.php'; // Ensure this file exists

use App\Models\Schedule;
use App\Config\Database;
use PDO;

class ScheduleController {
    private $scheduleModel;
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect(); // Store connection in a property
        $this->scheduleModel = new Schedule($this->conn); // Pass the connection to Schedule model
    }

    public function getSchedules() {
        $query = "SELECT id, faculty, room, department, course, section, time_from, time_to, month_from FROM schedules";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $events = [];
        foreach ($schedules as $schedule) {
            $date = date('Y') . '-' . date('m-d', strtotime($schedule['month_from']));
    
            $events[] = [
                'id' => $schedule['id'],
                'title' => $schedule['faculty'],
                'start' => $date,
                'extendedProps' => [
                    'room' => $schedule['room'],
                    'department' => $schedule['department'],
                    'course' => $schedule['course'],
                    'section' => $schedule['section'],
                    'time_from' => $schedule['time_from'],
                    'time_to' => $schedule['time_to']
                ]
            ];
        }
    
        return $events; // Make sure to return the data instead of echoing it
    }
    
    public function addSchedule() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Debugging: Log raw POST data
            error_log("Raw POST data: " . print_r($_POST, true));

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
                'section' => $_POST['section'] ?? ''
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

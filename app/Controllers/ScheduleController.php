<?php
namespace App\Controllers;

require_once __DIR__ . '/../Models/Schedule.php';
require_once __DIR__ . '/../Models/Notification.php';
require_once __DIR__ . '/../Config/Database.php';

use App\Models\Schedule;
use App\Models\Notification;
use App\Config\Database;
use PDO;

class ScheduleController {
    private $scheduleModel;
    private $notificationModel;
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
        $this->scheduleModel = new Schedule($this->conn);
        $this->notificationModel = new Notification();
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
                'faculty' => htmlspecialchars(trim($_POST['faculty'] ?? '')),
                'day_of_week' => htmlspecialchars(trim($_POST['day_of_week'] ?? '')),
                'subject' => htmlspecialchars(trim($_POST['subject'] ?? '')),
                'month_from' => htmlspecialchars(trim($_POST['month_from'] ?? '')),
                'month_to' => htmlspecialchars(trim($_POST['month_to'] ?? '')),
                'room' => htmlspecialchars(trim($_POST['room'] ?? '')),
                'department' => htmlspecialchars(trim($_POST['department'] ?? '')),
                'time_from' => htmlspecialchars(trim($_POST['time_from'] ?? '')),
                'time_to' => htmlspecialchars(trim($_POST['time_to'] ?? '')),
                'course' => htmlspecialchars(trim($_POST['course'] ?? '')),
                'section' => htmlspecialchars(trim($_POST['section'] ?? '')),
                'building' => htmlspecialchars(trim($_POST['building'] ?? ''))
            ];
    
            try {
                // Calculate duration of new schedule
                $newFrom = new \DateTime($data['time_from']);
                $newTo = new \DateTime($data['time_to']);
                $newDuration = ($newTo->getTimestamp() - $newFrom->getTimestamp()) / 3600;
    
                // Check existing total for the day for this faculty
                $query = "SELECT time_from, time_to FROM schedules 
                          WHERE faculty = :faculty AND month_from = :day";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':faculty', $data['faculty']);
                $stmt->bindParam(':day', $data['month_from']);
                $stmt->execute();
                $existingSchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                $totalHours = 0;
                foreach ($existingSchedules as $sched) {
                    $from = new \DateTime($sched['time_from']);
                    $to = new \DateTime($sched['time_to']);
                    $hours = ($to->getTimestamp() - $from->getTimestamp()) / 3600;
                    $totalHours += $hours;
                }
    
                if (($totalHours + $newDuration) > 8) {
                    echo json_encode(['status' => 'error', 'message' => 'Faculty can only have up to 8 hours of schedule per day']);
                    exit();
                }
    
                // Proceed to add schedule
                if ($this->scheduleModel->addSchedule($data)) {
                    echo json_encode(['status' => 'success', 'message' => 'Schedule added successfully']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to add schedule']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
            }
    
            exit();
        }
    }

   public function getSchedulesForUser($faculty, $course, $section) {
    $stmt = $this->conn->prepare("SELECT * FROM schedules WHERE faculty = ? AND course = ? AND section = ?");
    $stmt->execute([$faculty, $course, $section]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $events = [];
    foreach ($rows as $row) {
        $events[] = [
            'title' => $row['subject'],
            'start' => $row['start_date'],
            'end' => $row['end_date'],
            'extendedProps' => [
                'faculty' => $row['faculty'],
                'room' => $row['room'],
                'department' => $row['department'],
                'course' => $row['course'],
                'section' => $row['section'],
                'time_from' => $row['time_from'],
                'time_to' => $row['time_to'],
                'building' => $row['building']
            ]
        ];
    }

    return $events;
}

public function getTotalSchedules() {
    return $this->scheduleModel->getTotalSchedules();
}

public function updateSchedule($id, $data) {
    try {
        // Validate time constraints
        $newFrom = new \DateTime($data['time_from']);
        $newTo = new \DateTime($data['time_to']);
        $newDuration = ($newTo->getTimestamp() - $newFrom->getTimestamp()) / 3600;

        // Check existing total for the day for this faculty
        $query = "SELECT time_from, time_to FROM schedules 
                  WHERE faculty = :faculty AND id != :id AND month_from = :month_from";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':faculty', $data['faculty']);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':month_from', $data['month_from']);
        $stmt->execute();
        $existingSchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalHours = 0;
        foreach ($existingSchedules as $sched) {
            $from = new \DateTime($sched['time_from']);
            $to = new \DateTime($sched['time_to']);
            $hours = ($to->getTimestamp() - $from->getTimestamp()) / 3600;
            $totalHours += $hours;
        }

        if (($totalHours + $newDuration) > 8) {
            return [
                'status' => 'error',
                'message' => 'Faculty can only have up to 8 hours of schedule per day'
            ];
        }

        // Update the schedule
        $success = $this->scheduleModel->updateSchedule($id, $data);
        
        if ($success) {
            // Create notification for the faculty
            $notificationMessage = sprintf(
                'Your schedule has been updated. New time: %s - %s, Room: %s, Building: %s',
                $data['time_from'],
                $data['time_to'],
                $data['room'],
                $data['building']
            );
            
            $this->notificationModel->createNotification(
                $data['faculty'],
                $notificationMessage,
                'schedule_update'
            );
            
            return [
                'status' => 'success',
                'message' => 'Schedule updated successfully'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Failed to update schedule'
            ];
        }
    } catch (PDOException $e) {
        error_log("Update Schedule Error: " . $e->getMessage());
        return [
            'status' => 'error',
            'message' => 'An error occurred while updating the schedule'
        ];
    }
}

public function getNotifications($faculty = null) {
    $query = "SELECT * FROM notifications";
    $params = [];
    
    if ($faculty) {
        $query .= " WHERE faculty = :faculty";
        $params['faculty'] = $faculty;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function markNotificationAsRead($id) {
    $query = "UPDATE notifications SET is_read = 1 WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    return $stmt->execute(['id' => $id]);
}

// Add this method to handle all requests
public function handleRequest() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['scheduleId'])) {
            // Handle schedule update
            $data = [
                'faculty' => htmlspecialchars(trim($_POST['faculty'] ?? '')),
                'room' => htmlspecialchars(trim($_POST['room'] ?? '')),
                'department' => htmlspecialchars(trim($_POST['department'] ?? '')),
                'course' => htmlspecialchars(trim($_POST['course'] ?? '')),
                'section' => htmlspecialchars(trim($_POST['section'] ?? '')),
                'time_from' => htmlspecialchars(trim($_POST['time_from'] ?? '')),
                'time_to' => htmlspecialchars(trim($_POST['time_to'] ?? '')),
                'building' => htmlspecialchars(trim($_POST['building'] ?? '')),
                'month_from' => date('Y-m-d') // Current date for the update
            ];
            
            $response = $this->updateSchedule($_POST['scheduleId'], $data);
            
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } else {
            // Handle regular schedule addition
            $this->addSchedule();
        }
    }
}
}    

// Only handle POST requests here, GET requests are handled in the view files
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new ScheduleController();
    $controller->handleRequest();
}
?>

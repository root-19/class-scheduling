<?php
namespace App\Controllers;

require_once __DIR__ . '/../Models/Schedule.php';
require_once __DIR__ . '/../Models/Notification.php';
require_once __DIR__ . '/../Config/Database.php';

use App\Models\Schedule;
use App\Models\Notification;
use App\Config\Database;
use PDO;
use DateTime;

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
        $query = "SELECT id, faculty, room, department, subject, course, section, time_from, time_to, month_from, month_to, building, day_of_week FROM schedules";
        if ($department) {
            $query .= " WHERE department = :department";
        }

        $stmt = $this->conn->prepare($query);

        if ($department) {
            $stmt->bindParam(':department', $department, PDO::PARAM_STR);
        }

        $stmt->execute();
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("Raw schedules from database: " . print_r($schedules, true));

        $events = [];
        foreach ($schedules as $schedule) {
            if (empty($schedule['subject']) || $schedule['subject'] === 'No Class') {
                continue;
            }

            // Get the start and end dates
            $startDate = new DateTime($schedule['month_from']);
            $endDate = new DateTime($schedule['month_to']);
            
            // Get the number of meetings from day_of_week
            $numberOfMeetings = (int)$schedule['day_of_week'];
            
            // Calculate the interval between meetings
            $totalDays = $startDate->diff($endDate)->days;
            $interval = floor($totalDays / ($numberOfMeetings - 1));
            
            // Create exactly the number of events specified in day_of_week
            for ($i = 0; $i < $numberOfMeetings; $i++) {
                $eventDate = clone $startDate;
                if ($i > 0) {
                    $eventDate->modify('+' . ($i * $interval) . ' days');
                }
                
                // Ensure we don't go past the end date
                if ($eventDate > $endDate) {
                    $eventDate = clone $endDate;
                }

                $event = [
                    'id' => $schedule['id'] . '_' . ($i + 1),
                    'title' => $schedule['subject'] . ' (' . ($i + 1) . '/' . $numberOfMeetings . ')',
                    'start' => $eventDate->format('Y-m-d') . 'T' . $schedule['time_from'],
                    'end' => $eventDate->format('Y-m-d') . 'T' . $schedule['time_to'],
                    'extendedProps' => [
                        'faculty' => $schedule['faculty'],
                        'room' => $schedule['room'],
                        'department' => $schedule['department'],
                        'course' => $schedule['course'],
                        'section' => $schedule['section'],
                        'time_from' => $schedule['time_from'],
                        'time_to' => $schedule['time_to'],
                        'building' => $schedule['building'],
                        'month_from' => $schedule['month_from'],
                        'month_to' => $schedule['month_to'],
                        'day_of_week' => $schedule['day_of_week'],
                        'occurrence' => $i + 1,
                        'total_occurrences' => $numberOfMeetings
                    ]
                ];
                
                error_log("Created event: " . print_r($event, true));
                $events[] = $event;
            }
        }

        error_log("Total events created: " . count($events));
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
                // Check for time conflicts
                $query = "SELECT * FROM schedules WHERE 
                    ((time_from <= :time_from AND time_to > :time_from) OR
                    (time_from < :time_to AND time_to >= :time_to) OR
                    (time_from >= :time_from AND time_to <= :time_to)) AND
                    (room = :room OR faculty = :faculty)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':time_from', $data['time_from']);
                $stmt->bindParam(':time_to', $data['time_to']);
                $stmt->bindParam(':room', $data['room']);
                $stmt->bindParam(':faculty', $data['faculty']);
                $stmt->execute();
                
                $conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($conflicts)) {
                    echo json_encode([
                        'status' => 'error', 
                        'message' => 'Schedule conflict detected! There is already a schedule at this time for either the room or faculty.'
                    ]);
                    exit();
                }

                // Calculate duration of new schedule
                $newFrom = new \DateTime($data['time_from']);
                $newTo = new \DateTime($data['time_to']);
                $newDuration = ($newTo->getTimestamp() - $newFrom->getTimestamp()) / 3600;
    
                // Check existing total for the day for this faculty
                $query = "SELECT time_from, time_to FROM schedules 
                          WHERE faculty = :faculty";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':faculty', $data['faculty']);
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

   public function getSchedulesForUser($faculty, $course = null, $section = null) {
        $query = "SELECT * FROM schedules WHERE faculty = :faculty";
        $params = [':faculty' => $faculty];

        if ($course) {
            $query .= " AND course = :course";
            $params[':course'] = $course;
        }
        if ($section) {
            $query .= " AND section = :section";
            $params[':section'] = $section;
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $events = [];
        foreach ($schedules as $schedule) {
            if (empty($schedule['subject']) || $schedule['subject'] === 'No Class') {
                continue;
            }

            // Get the start and end dates
            $startDate = new DateTime($schedule['month_from']);
            $endDate = new DateTime($schedule['month_to']);
            
            // Get the number of meetings from day_of_week
            $numberOfMeetings = (int)$schedule['day_of_week'];
            
            // Calculate the interval between meetings
            $totalDays = $startDate->diff($endDate)->days;
            $interval = floor($totalDays / ($numberOfMeetings - 1));
            
            // Create exactly the number of events specified in day_of_week
            for ($i = 0; $i < $numberOfMeetings; $i++) {
                $eventDate = clone $startDate;
                if ($i > 0) {
                    $eventDate->modify('+' . ($i * $interval) . ' days');
                }
                
                // Ensure we don't go past the end date
                if ($eventDate > $endDate) {
                    $eventDate = clone $endDate;
                }

                $events[] = [
                    'id' => $schedule['id'] . '_' . ($i + 1),
                    'title' => $schedule['subject'] . ' (' . ($i + 1) . '/' . $numberOfMeetings . ')',
                    'start' => $eventDate->format('Y-m-d') . 'T' . $schedule['time_from'],
                    'end' => $eventDate->format('Y-m-d') . 'T' . $schedule['time_to'],
                    'extendedProps' => [
                        'faculty' => $schedule['faculty'],
                        'room' => $schedule['room'],
                        'department' => $schedule['department'],
                        'course' => $schedule['course'],
                        'section' => $schedule['section'],
                        'time_from' => $schedule['time_from'],
                        'time_to' => $schedule['time_to'],
                        'building' => $schedule['building'],
                        'month_from' => $schedule['month_from'],
                        'month_to' => $schedule['month_to'],
                        'day_of_week' => $schedule['day_of_week'],
                        'occurrence' => $i + 1,
                        'total_occurrences' => $numberOfMeetings
                    ]
                ];
            }
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
                'month_from' => htmlspecialchars(trim($_POST['month_from'] ?? '')),
                'month_to' => htmlspecialchars(trim($_POST['month_to'] ?? '')),
                'day_of_week' => htmlspecialchars((trim($_POST['day_of_week'] ?? '')))
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

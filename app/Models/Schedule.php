<?php
namespace App\Models;

require_once __DIR__ . '/../Config/Database.php';
use PDO;
use App\Config\Database;
use PDOException;

class Schedule {
    private $conn;
    private $table = "schedules";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getAllSchedules() {
        $query = "SELECT id, faculty, day_of_week, subject, month_from, month_to, room, department, time_from, time_to, course, section FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addSchedule($data) {
        $query = "INSERT INTO " . $this->table . " 
        (faculty, day_of_week, subject, month_from, month_to, room, department, time_from, time_to, course, section, building) 
        VALUES 
        (:faculty, :day_of_week, :subject, :month_from, :month_to, :room, :department, :time_from, :time_to, :course, :section, :building)";

        $stmt = $this->conn->prepare($query);

        // Debugging: Log the data to check if it is correctly received
        // error_log("Received data: " . print_r($data, true));

        if ($stmt->execute($data)) {
            return true;
        } else {
            // Debugging: Log SQL errors if insertion fails
            // error_log("SQL Error: " . print_r($stmt->errorInfo(), true));
            return false;
        }
    }

    public function getTotalSchedules() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function updateSchedule($id, $data) {
        $query = "UPDATE " . $this->table . " SET 
            faculty = :faculty,
            room = :room,
            day_of_week = :day_of_week,

            department = :department,
            course = :course,
            section = :section,
            time_from = :time_from,
            time_to = :time_to,
            building = :building,
            month_from = :month_from,
            month_to = :month_to
            WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            
            // Log the update attempt
            error_log("Attempting to update schedule ID: " . $id);
            error_log("Update data: " . print_r($data, true));
            
            $result = $stmt->execute(array_merge($data, ['id' => $id]));
            
            if (!$result) {
                error_log("Update failed. Error info: " . print_r($stmt->errorInfo(), true));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Update Schedule Error: " . $e->getMessage());
            return false;
        }
    }
}
?>

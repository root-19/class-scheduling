<?php
namespace App\Models;

require_once __DIR__ . '/../Config/Database.php';
use PDO;
use App\Config\Database;

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
}
?>

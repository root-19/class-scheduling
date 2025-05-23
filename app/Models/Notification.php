<?php
namespace App\Models;

require_once __DIR__ . '/../Config/Database.php';
use PDO;
use App\Config\Database;

class Notification {
    private $conn;
    private $table = "notifications";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
        
        // Create notifications table if it doesn't exist
        $this->createNotificationsTable();
    }

    private function createNotificationsTable() {
        $query = "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            faculty VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(50) NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->conn->exec($query);
    }

    public function createNotification($faculty, $message, $type = 'schedule_update') {
        $query = "INSERT INTO notifications (faculty, message, type) VALUES (:faculty, :message, :type)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            'faculty' => $faculty,
            'message' => $message,
            'type' => $type
        ]);
    }

    public function getNotificationsForFaculty($faculty) {
        $query = "SELECT * FROM notifications WHERE faculty = :faculty ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['faculty' => $faculty]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead($notification_id) {
        $query = "UPDATE notifications SET is_read = TRUE WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute(['id' => $notification_id]);
    }

    public function getUnreadCount($faculty) {
        $query = "SELECT COUNT(*) as count FROM notifications WHERE faculty = :faculty AND is_read = FALSE";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['faculty' => $faculty]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
}
?> 
<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Config\Database;

class GradeController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function edit() {
        header('Content-Type: application/json');
        
        if (isset($_POST['id']) && isset($_POST['exam'])) {
            try {
                $id = $_POST['id'];
                $exam = $_POST['exam'];

                // Log the received data
                error_log("Updating id: " . $id . " with exam: " . $exam);

                $sql = "UPDATE grades SET exam = ? WHERE id = ?";
                $stmt = $this->conn->prepare($sql);
                
                if (!$stmt) {
                    error_log("Failed to prepare statement");
                    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
                    exit;
                }

                $result = $stmt->execute([$exam, $id]);
                
                if ($result) {
                    // Verify the update
                    $verify = $this->conn->query("SELECT exam FROM grades WHERE id = " . $id);
                    $updated = $verify->fetch();
                    error_log("Updated exam value: " . print_r($updated, true));
                    
                    echo json_encode(['success' => true]);
                } else {
                    error_log("Failed to execute update");
                    echo json_encode(['success' => false, 'message' => 'Failed to update grade']);
                }
            } catch (\Exception $e) {
                error_log("Error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            error_log("Missing required data - id: " . isset($_POST['id']) . ", exam: " . isset($_POST['exam']));
            echo json_encode(['success' => false, 'message' => 'Missing required data']);
        }
        exit;
    }

    public function getGrades($studentId) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM grades WHERE student_id = ?");
            $stmt->execute([$studentId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $controller = new GradeController();
    $controller->edit();
} 
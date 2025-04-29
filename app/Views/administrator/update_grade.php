<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/Database.php';

use App\Config\Database;

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->connect();

    // Get form data
    $student_id = $_POST['student_id'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $prelim = $_POST['prelim'] ?? null;
    $midterm = $_POST['midterm'] ?? null;
    $final = $_POST['final'] ?? null;
    $exam = $_POST['exam'] ?? null;

    // Validate input
    if (empty($student_id) || empty($subject)) {
        throw new Exception('Student ID and Subject are required');
    }

    // Check if grade record exists
    $checkStmt = $conn->prepare("SELECT id FROM grades WHERE student_id = ? AND subject = ?");
    $checkStmt->execute([$student_id, $subject]);
    $gradeExists = $checkStmt->fetch();

    // Check if exam column exists
    $columnCheck = $conn->query("SHOW COLUMNS FROM grades LIKE 'exam'");
    $examColumnExists = $columnCheck->rowCount() > 0;

    if ($gradeExists) {
        if ($examColumnExists) {
            // Update existing grade with exam
            $stmt = $conn->prepare("UPDATE grades SET prelim = ?, midterm = ?, final = ?, exam = ? WHERE student_id = ? AND subject = ?");
            $stmt->execute([$prelim, $midterm, $final, $exam, $student_id, $subject]);
        } else {
            // Update existing grade without exam
            $stmt = $conn->prepare("UPDATE grades SET prelim = ?, midterm = ?, final = ? WHERE student_id = ? AND subject = ?");
            $stmt->execute([$prelim, $midterm, $final, $student_id, $subject]);
        }
    } else {
        if ($examColumnExists) {
            // Insert new grade with exam
            $stmt = $conn->prepare("INSERT INTO grades (student_id, subject, prelim, midterm, final, exam) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$student_id, $subject, $prelim, $midterm, $final, $exam]);
        } else {
            // Insert new grade without exam
            $stmt = $conn->prepare("INSERT INTO grades (student_id, subject, prelim, midterm, final) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$student_id, $subject, $prelim, $midterm, $final]);
        }
    }

    echo json_encode(['success' => true, 'message' => 'Grades updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 
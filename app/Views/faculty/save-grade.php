<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Config\Database;

$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = $_POST['student_id'] ?? null;
    $subjectName = trim($_POST['new_subject'] ?? '');
    $grades = $_POST['new_grades'] ?? [];

    if (!$studentId || !$subjectName || empty($grades)) {
        echo "Incomplete data.";
        exit();
    }

    // Check if subject already exists for this student
    $checkStmt = $conn->prepare("SELECT * FROM grades WHERE student_id = ? AND LOWER(subject) = LOWER(?)");
    $checkStmt->execute([$studentId, $subjectName]);

    if ($checkStmt->rowCount() > 0) {
        echo "This subject already has grades recorded.";
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO grades (student_id, subject, prelim, midterm, final)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $studentId,
        $subjectName,
        $grades['prelim'],
        $grades['midterm'],
        $grades['final']
    ]);

    header("Location: view-user.php?id=" . $studentId);
    exit();


// Refresh the page to prevent form resubmission
header("Location: view-user.php?id=" . $id);
exit();
}

?>

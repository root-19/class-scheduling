<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Config\Database;

$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['student_id']) && !empty($_POST['grades'])) {
    $studentId = $_POST['student_id'];
    $grades = $_POST['grades'];

    foreach ($grades as $subject => $gradeSet) {
        $subject = trim($subject);
        if (empty($subject)) continue;

        $prelim = isset($gradeSet['prelim']) && $gradeSet['prelim'] !== '' ? floatval($gradeSet['prelim']) : null;
        $midterm = isset($gradeSet['midterm']) && $gradeSet['midterm'] !== '' ? floatval($gradeSet['midterm']) : null;
        $final = isset($gradeSet['final']) && $gradeSet['final'] !== '' ? floatval($gradeSet['final']) : null;

        // Check if grade already exists
        $check = $conn->prepare("SELECT id FROM grades WHERE student_id = ? AND subject = ?");
        $check->execute([$studentId, $subject]);

        if ($check->rowCount() > 0) {
            // Update
            $update = $conn->prepare("UPDATE grades SET prelim = ?, midterm = ?, final = ?, updated_at = NOW() WHERE student_id = ? AND subject = ?");
            $update->execute([$prelim, $midterm, $final, $studentId, $subject]);
        } else {
            // Insert
            $insert = $conn->prepare("INSERT INTO grades (student_id, subject, prelim, midterm, final, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $insert->execute([$studentId, $subject, $prelim, $midterm, $final]);
        }
    }

    header("Location: view-user.php?id=" . urlencode($studentId));
    exit();
}

echo "Invalid request.";
exit();

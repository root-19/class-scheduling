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
        INSERT INTO grades (student_id, subject, prelim, midterm, final, exam)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $studentId,
        $subjectName,
        $grades['prelim'],
        $grades['midterm'],
        $grades['final'],
        $grades['exam']
    ]);

    header("Location: view-user.php?id=" . $studentId);
    exit();


// Refresh the page to prevent form resubmission
header("Location: view-user.php?id=" . $id);
exit();
}

?>

<div class="mb-4">
    <label for="exam" class="block text-sm font-medium text-gray-700">Exam Grade</label>
    <input type="number" step="0.01" min="0" max="100" name="exam" id="exam" 
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
           value="<?= isset($grade['exam']) ? $grade['exam'] : '' ?>">
</div>

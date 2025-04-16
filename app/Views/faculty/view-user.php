<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Config\Database;

$db = new Database();
$conn = $db->connect();

if (!isset($_GET['id'])) {
    echo "Invalid request.";
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo "Student not found.";
    exit();
}

include './layout/sidebar.php';

$subjects = explode(',', $student['subjects']);

// Fetch grades from the database
$gradesStmt = $conn->prepare("SELECT * FROM grades WHERE student_id = ?");
$gradesStmt->execute([$student['id']]);
$gradesData = $gradesStmt->fetchAll(PDO::FETCH_ASSOC);

// Build a map of subject => grades with clean keys
$gradesMap = [];
foreach ($gradesData as $grade) {
    $key = strtolower(trim($grade['subject']));
    $gradesMap[$key] = $grade;
}
?>

<div class="p-6 w-full">
    <h1 class="text-2xl font-bold mb-4">Student Details</h1>

    <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
        <p><strong>First Name:</strong> <?= htmlspecialchars($student['first_name']) ?></p>
        <p><strong>Last Name:</strong> <?= htmlspecialchars($student['last_name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
        <p><strong>Student ID:</strong> <?= htmlspecialchars($student['student_id']) ?></p>
        <p><strong>Contact:</strong> <?= htmlspecialchars($student['contact']) ?></p>
        <p><strong>Faculty:</strong> <?= htmlspecialchars($student['faculty']) ?></p>
        <p><strong>Subjects:</strong> <?= htmlspecialchars($student['subjects']) ?></p>
        <p><strong>Sections:</strong> <?= htmlspecialchars($student['sections']) ?></p>
        <p><strong>Semester:</strong> <?= htmlspecialchars($student['semester']) ?></p>
        <a href="my-student.php" class="text-blue-500 mt-4 inline-block">← Back to List</a>
    </div>

    <h2 class="text-xl font-semibold mb-4">Enter Grades</h2>

    <form method="POST" action="save-grade.php">
        <input type="hidden" name="student_id" value="<?= $student['id'] ?>">

        <?php foreach ($subjects as $subject):
    $originalSubject = trim($subject);
    $subjectKey = strtolower(trim($subject));
    if (empty($subjectKey)) continue;

    $prelim = $gradesMap[$subjectKey]['prelim'] ?? '';
    $midterm = $gradesMap[$subjectKey]['midterm'] ?? '';
    $final = $gradesMap[$subjectKey]['final'] ?? '';
?>
            <div class="mb-4 p-4 border rounded shadow">
                <h3 class="text-lg font-bold mb-2"><?= htmlspecialchars($originalSubject) ?></h3>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block">Prelim</label>
                        <input type="number" step="0.01" min="0" max="100" name="grades[<?= htmlspecialchars($originalSubject) ?>][prelim]" value="<?= htmlspecialchars($prelim ?? '') ?>" class="w-full border px-2 py-1 rounded">
                    </div>
                    <div>
                        <label class="block">Midterm</label>
                        <input type="number" step="0.01" min="0" max="100" name="grades[<?= htmlspecialchars($originalSubject) ?>][midterm]" value="<?= htmlspecialchars($midterm ?? '') ?>" class="w-full border px-2 py-1 rounded">
                    </div>
                    <div>
                        <label class="block">Final</label>
                        <input type="number" step="0.01" min="0" max="100" name="grades[<?= htmlspecialchars($originalSubject) ?>][final]" value="<?= htmlspecialchars($final ?? '') ?>" class="w-full border px-2 py-1 rounded">
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            Save Grades
        </button>
    </form>
</div>

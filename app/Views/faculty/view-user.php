<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Config\Database;

$db = new Database();
$conn = $db->connect();

// Check if ID is provided
if (!isset($_GET['id'])) {
    echo "Invalid request.";
    exit();
}

$id = $_GET['id'];

// Get student info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo "Student not found.";
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $studentId = $_POST['student_id'] ?? $id;

    if ($action === 'add') {
        $subjectName = trim($_POST['new_subject'] ?? '');
        $grades = $_POST['new_grades'] ?? [];

        if (!$subjectName || empty($grades)) {
            echo "Incomplete data.";
            exit();
        }

        // Check if subject already exists
        $checkStmt = $conn->prepare("SELECT * FROM grades WHERE student_id = ? AND LOWER(subject) = LOWER(?)");
        $checkStmt->execute([$studentId, $subjectName]);

        if ($checkStmt->rowCount() > 0) {
            echo "<script>alert('This subject already has grades recorded.');</script>";
        } else {
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
        }

    } elseif ($action === 'edit') {
        $gradeId = $_POST['grade_id'];
        $stmt = $conn->prepare("UPDATE grades SET prelim = ?, midterm = ?, final = ? WHERE id = ? AND student_id = ?");
        $stmt->execute([
            $_POST['prelim'],
            $_POST['midterm'],
            $_POST['final'],
            $gradeId,
            $studentId
        ]);

    } elseif ($action === 'delete') {
        $gradeId = $_POST['grade_id'];
        $stmt = $conn->prepare("DELETE FROM grades WHERE id = ? AND student_id = ?");
        $stmt->execute([$gradeId, $studentId]);
    }

    // Redirect after any POST action
    header("Location: view-user.php?id=" . $studentId);
    exit();
}

// Fetch grades
$gradesStmt = $conn->prepare("SELECT * FROM grades WHERE student_id = ?");
$gradesStmt->execute([$id]);
$gradesData = $gradesStmt->fetchAll(PDO::FETCH_ASSOC);

include './layout/sidebar.php';
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
        <!-- <p><strong>Subjects:</strong> <?= htmlspecialchars($student['subjects']) ?></p> -->
        <p><strong>Sections:</strong> <?= htmlspecialchars($student['sections']) ?></p>
        <!-- <p><strong>Semester:</strong> <?= htmlspecialchars($student['semester']) ?></p> -->
        <a href="my-student.php" class="text-blue-500 mt-4 inline-block">← Back to List</a>
    </div>

    <!-- Add New Subject -->
    <h2 class="text-xl font-semibold mb-4">Add New Subject with Grades</h2>

    <form method="POST" class="mb-6">
        <input type="hidden" name="student_id" value="<?= $id ?>">
        <input type="hidden" name="action" value="add">

        <div class="mb-4 p-4 border rounded shadow">
            <div class="mb-2">
                <label class="block font-semibold">Subject Name</label>
                <input type="text" name="new_subject" placeholder="e.g. Math 101" required class="w-full border px-2 py-1 rounded">
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block">Prelim</label>
                    <input type="number" step="0.01" min="0" max="100" name="new_grades[prelim]" required class="w-full border px-2 py-1 rounded">
                </div>
                <div>
                    <label class="block">Midterm</label>
                    <input type="number" step="0.01" min="0" max="100" name="new_grades[midterm]" required class="w-full border px-2 py-1 rounded">
                </div>
                <div>
                    <label class="block">Final</label>
                    <input type="number" step="0.01" min="0" max="100" name="new_grades[final]" required class="w-full border px-2 py-1 rounded">
                </div>
            </div>
        </div>

        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            Add Subject & Save Grades
        </button>
    </form>

    <!-- Existing Grades -->
    <h2 class="text-xl font-semibold mb-4">Existing Subjects & Grades</h2>

    <?php if (!empty($gradesData)): ?>
        <?php foreach ($gradesData as $grade): ?>
            <form method="POST" class="mb-4 p-4 border rounded shadow">
                <input type="hidden" name="grade_id" value="<?= $grade['id'] ?>">
                <input type="hidden" name="student_id" value="<?= $id ?>">

                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-bold"><?= htmlspecialchars($grade['subject']) ?></h3>
                    <!-- <div class="space-x-2">
                        <button type="submit" name="action" value="edit" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">Update</button>
                        <button type="submit" name="action" value="delete" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded" onclick="return confirm('Are you sure you want to delete this subject?')">Delete</button>
                    </div> -->
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block">Prelim</label>
                        <input type="number" step="0.01" name="prelim" min="0" max="100" value="<?= $grade['prelim'] ?>" class="w-full border px-2 py-1 rounded" required>
                    </div>
                    <div>
                        <label class="block">Midterm</label>
                        <input type="number" step="0.01" name="midterm" min="0" max="100" value="<?= $grade['midterm'] ?>" class="w-full border px-2 py-1 rounded" required>
                    </div>
                    <div>
                        <label class="block">Final</label>
                        <input type="number" step="0.01" name="final" min="0" max="100" value="<?= $grade['final'] ?>" class="w-full border px-2 py-1 rounded" required>
                    </div>
                </div>
            </form>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No grades available yet.</p>
    <?php endif; ?>
</div>

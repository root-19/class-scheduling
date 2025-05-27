<?php
session_start();
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

// Check if faculty is logged in
if (!isset($_SESSION['faculty_name'])) {
    echo "Please log in to access this page.";
    exit();
}

// Get faculty's assigned subjects
$facultyStmt = $conn->prepare("SELECT subjects FROM faculty WHERE name = ?");
$facultyStmt->execute([$_SESSION['faculty_name']]);
$facultySubjects = $facultyStmt->fetch(PDO::FETCH_ASSOC);
$facultyAssignedSubjects = $facultySubjects ? explode(',', $facultySubjects['subjects']) : [];

// Get student's assigned subjects
$studentSubjectsStmt = $conn->prepare("SELECT subjects FROM users WHERE id = ?");
$studentSubjectsStmt->execute([$id]);
$studentSubjects = $studentSubjectsStmt->fetch(PDO::FETCH_ASSOC);
$studentAssignedSubjects = $studentSubjects ? explode(',', $studentSubjects['subjects']) : [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $studentId = $_POST['student_id'] ?? $id;

    if ($action === 'add') {
        $subject = trim($_POST['subject'] ?? '');
        $grades = $_POST['new_grades'] ?? [];
        $attendance = $_POST['attendance'] ?? 0;

        if (!$subject || empty($grades)) {
            echo "<script>alert('Please fill in all grade fields.');</script>";
        } else {
            // Check if faculty is assigned to this subject AND student is also assigned to this subject
            if (!in_array($subject, $facultyAssignedSubjects)) {
                echo "<script>alert('You are not authorized to add grades for this subject.');</script>";
            } elseif (!in_array($subject, $studentAssignedSubjects)) {
                echo "<script>alert('This subject is not assigned to the student.');</script>";
            } else {
                // Check if subject already exists for this student
                $checkStmt = $conn->prepare("SELECT * FROM grades WHERE student_id = ? AND subject = ?");
                $checkStmt->execute([$studentId, $subject]);

                if ($checkStmt->rowCount() > 0) {
                    echo "<script>alert('Grades for this subject already exist.');</script>";
                } else {
                    $stmt = $conn->prepare("
                        INSERT INTO grades (student_id, subject, prelim, midterm, final, attendance)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    try {
                        $stmt->execute([
                            $studentId,
                            $subject,
                            $grades['prelim'],
                            $grades['midterm'],
                            $grades['final'],
                            $attendance
                        ]);
                        echo "<script>alert('Grades and attendance added successfully!');</script>";
                    } catch (PDOException $e) {
                        echo "<script>alert('Error saving grades. Please try again.');</script>";
                    }
                }
            }
        }
    } elseif ($action === 'edit') {
        $gradeId = $_POST['grade_id'];
        $subject = trim($_POST['subject'] ?? '');
        $attendance = $_POST['attendance'] ?? 0;
        
        // Check if faculty is assigned to this subject AND student is also assigned to this subject
        if (!in_array($subject, $facultyAssignedSubjects)) {
            echo "<script>alert('You are not authorized to edit grades for this subject.');</script>";
        } elseif (!in_array($subject, $studentAssignedSubjects)) {
            echo "<script>alert('This subject is not assigned to the student.');</script>";
        } else {
            try {
                $stmt = $conn->prepare("UPDATE grades SET prelim = ?, midterm = ?, final = ?, attendance = ? WHERE id = ? AND student_id = ?");
                $stmt->execute([
                    $_POST['prelim'],
                    $_POST['midterm'],
                    $_POST['final'],
                    $attendance,
                    $gradeId,
                    $studentId
                ]);
                echo "<script>alert('Grades and attendance updated successfully!');</script>";
            } catch (PDOException $e) {
                echo "<script>alert('Error updating grades. Please try again.');</script>";
            }
        }
    } elseif ($action === 'delete') {
        $gradeId = $_POST['grade_id'];
        $subject = trim($_POST['subject'] ?? '');
        
        // Check if faculty is assigned to this subject AND student is also assigned to this subject
        if (!in_array($subject, $facultyAssignedSubjects)) {
            echo "<script>alert('You are not authorized to delete grades for this subject.');</script>";
        } elseif (!in_array($subject, $studentAssignedSubjects)) {
            echo "<script>alert('This subject is not assigned to the student.');</script>";
        } else {
            $stmt = $conn->prepare("DELETE FROM grades WHERE id = ? AND student_id = ?");
            $stmt->execute([$gradeId, $studentId]);
        }
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
        <p><strong>Sections:</strong> <?= htmlspecialchars($student['sections']) ?></p>
        <a href="my-student.php" class="text-blue-500 mt-4 inline-block">← Back to List</a>
    </div>

    <!-- Student's Subjects & Grades -->
    <h2 class="text-xl font-semibold mb-4">Student's Subjects & Grades</h2>

    <?php
    // Get student's assigned subjects
    $subjectsStmt = $conn->prepare("SELECT subjects FROM users WHERE id = ?");
    $subjectsStmt->execute([$id]);
    $studentSubjects = $subjectsStmt->fetch(PDO::FETCH_ASSOC);
    $subjects = explode(',', $studentSubjects['subjects']);
    ?>

    <?php if (!empty($subjects) && $subjects[0] !== ''): ?>
        <?php foreach ($subjects as $subject): ?>
            <?php
            $trimmedSubject = trim($subject);
            // Check if grades exist for this subject
            $gradeStmt = $conn->prepare("SELECT * FROM grades WHERE student_id = ? AND subject = ?");
            $gradeStmt->execute([$id, $trimmedSubject]);
            $grade = $gradeStmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if faculty is assigned to this subject AND student is also assigned to this subject
            $isFacultyAssigned = in_array($trimmedSubject, $facultyAssignedSubjects);
            $isStudentAssigned = in_array($trimmedSubject, $studentAssignedSubjects);
            $canEditGrades = $isFacultyAssigned && $isStudentAssigned;
            ?>
            
            <form method="POST" class="mb-4 p-4 border rounded shadow">
                <input type="hidden" name="student_id" value="<?= $id ?>">
                <input type="hidden" name="action" value="<?= $grade ? 'edit' : 'add' ?>">
                <input type="hidden" name="subject" value="<?= htmlspecialchars($trimmedSubject) ?>">
                <?php if ($grade): ?>
                    <input type="hidden" name="grade_id" value="<?= $grade['id'] ?>">
                <?php endif; ?>

                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-bold"><?= htmlspecialchars($trimmedSubject) ?></h3>
                </div>

                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <label class="block">Prelim</label>
                        <input type="number" step="0.01" name="<?= $grade ? 'prelim' : 'new_grades[prelim]' ?>" 
                               min="0" max="100" value="<?= $grade ? $grade['prelim'] : '' ?>" 
                               class="w-full border px-2 py-1 rounded" <?= !$canEditGrades ? 'disabled' : '' ?>>
                    </div>
                    <div>
                        <label class="block">Midterm</label>
                        <input type="number" step="0.01" name="<?= $grade ? 'midterm' : 'new_grades[midterm]' ?>" 
                               min="0" max="100" value="<?= $grade ? $grade['midterm'] : '' ?>" 
                               class="w-full border px-2 py-1 rounded" <?= !$canEditGrades ? 'disabled' : '' ?>>
                    </div>
                    <div>
                        <label class="block">Final</label>
                        <input type="number" step="0.01" name="<?= $grade ? 'final' : 'new_grades[final]' ?>" 
                               min="0" max="100" value="<?= $grade ? $grade['final'] : '' ?>" 
                               class="w-full border px-2 py-1 rounded" <?= !$canEditGrades ? 'disabled' : '' ?>>
                    </div>
                    <div>
                        <label class="block">Attendance (Days)</label>
                        <input type="number" name="<?= $grade ? 'attendance' : 'attendance' ?>" 
                               min="0" value="<?= $grade ? $grade['attendance'] : '0' ?>" 
                               class="w-full border px-2 py-1 rounded" <?= !$canEditGrades ? 'disabled' : '' ?>>
                    </div>
                </div>

                <div class="mt-4">
                    <?php if ($canEditGrades): ?>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                            <?= $grade ? 'Update Grades & Attendance' : 'Add Grades & Attendance' ?>
                        </button>
                    <?php else: ?>
                        <p class="text-gray-500 italic">
                            <?php
                            if (!$isFacultyAssigned) {
                                echo "You are not assigned to this subject";
                            } elseif (!$isStudentAssigned) {
                                echo "This subject is not assigned to the student";
                            }
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
            </form>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No subjects assigned to this student yet.</p>
    <?php endif; ?>
</div>

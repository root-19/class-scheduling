<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Config\Database;
use App\Models\User;
use App\Controllers\AuthController;

$db = new Database();
$conn = $db->connect();
$auth = new AuthController();

$message = '';
$messageType = '';

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $messageType = 'success';
    $changes = isset($_GET['changes']) ? urldecode($_GET['changes']) : '';
    $message = $changes ? "Successfully updated: $changes" : "Student information updated successfully";
} elseif (isset($_GET['error'])) {
    $messageType = 'error';
    switch ($_GET['error']) {
        case 'missing_fields':
            $fields = isset($_GET['fields']) ? urldecode($_GET['fields']) : '';
            $message = "Required fields are missing: $fields";
            break;
        case 'invalid_email':
            $message = "Invalid email format";
            break;
        case 'duplicate_email':
            $message = "Email address is already taken by another student";
            break;
        case 'duplicate_student_id':
            $message = "Student ID is already taken by another student";
            break;
        case 'student_not_found':
            $message = "Student not found";
            break;
        case 'database_error':
            $message = "Database error occurred while updating student information";
            break;
        case 'exception':
            $message = isset($_GET['message']) ? urldecode($_GET['message']) : "An error occurred";
            break;
        case 'fatal_error':
            $message = "A system error occurred. Please contact support.";
            break;
        default:
            $message = "An unknown error occurred";
    }
}

error_log("Page loaded with message: $message (Type: $messageType)");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $faculty = $_POST['faculty_name'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $student_id = $_POST['student_id'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $password = $_POST['password'] ?? '';
    $prelim = $_POST['prelim'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $sections = $_POST['sections'] ?? '';
    $subjects = $_POST['subjects'] ?? [];
    $course = $_POST['course'] ?? '';

    // Call register method with all required parameters
    $result = $auth->register(
        $firstName,
        $lastName,
        $email,
        $student_id,
        $contact,
        $password,
        'student',
        $subjects,
        $sections,
        $prelim,
        $semester,
        $faculty,
        $course
    );

    if ($result['success']) {
        header("Location: register.php?message=Registration successful");
        exit();
    } else {
        $message = $result['message'];
    }
}

// Fetch registered students
$stmt = $conn->prepare("
    SELECT id, first_name, last_name, email, student_id, contact, image, 
           subjects, sections, faculty, course, prelim, semester
    FROM users 
    WHERE role = 'student'
");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle deletion
if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
    $stmt->execute([$deleteId]);
    header("Location: register.php?message=Student deleted");
    exit();
}

// Fetch faculties
$facultyQuery = $conn->query("SELECT * FROM faculty");
$faculties = $facultyQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch students and their grades with attendance
foreach ($students as &$student) {
    try {
        $stmt = $conn->prepare("SELECT id, subject, prelim, midterm, final, exam, attendance FROM grades WHERE student_id = ?");
        $stmt->execute([$student['id']]);
        $student['grades'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // If exam column doesn't exist, fetch without it
        $stmt = $conn->prepare("SELECT id, subject, prelim, midterm, final, attendance FROM grades WHERE student_id = ?");
        $stmt->execute([$student['id']]);
        $student['grades'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
unset($student); // best practice
include './layout/sidebar.php';
?>

<!-- Add SweetAlert2 CSS and JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<div class="p-8 w-full bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <?php if ($message): ?>
            <div class="mb-4 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Header Section -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <h1 class="text-2xl font-bold text-gray-800">Student Management</h1>
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <input type="text" 
                                id="searchInput"
                                placeholder="Search students..." 
                                class="w-64 px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($students as $student): ?>
                            <?php
                            // Calculate total attendance
                            $totalAttendance = 0;
                            if (!empty($student['grades'])) {
                                foreach ($student['grades'] as $grade) {
                                    $totalAttendance += intval($grade['attendance'] ?? 0);
                                }
                            }
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick='openModal(<?= json_encode($student) ?>)'>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($student['id']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($student['student_id']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <img src="/uploads/<?= htmlspecialchars($student['image']) ?>" 
                                             alt="Student Image" 
                                             class="w-8 h-8 rounded-full mr-3 object-cover">
                                        <div>
                                            <div class="font-medium"><?= htmlspecialchars($student['first_name']) ?> <?= htmlspecialchars($student['last_name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($student['email']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($student['contact']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $totalAttendance ?> days</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button type="button" 
                                                onclick='openModal(<?= json_encode($student) ?>)' 
                                                class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View
                                        </button>
                                        <a href="?delete=<?= $student['id'] ?>" 
                                           onclick="return confirm('Are you sure you want to delete this student?')" 
                                           class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Student Modal -->
<div id="viewModal" class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-center items-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl mx-4 max-h-[90vh] flex flex-col">
        <div class="p-6 border-b border-gray-200 flex-shrink-0">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800">Student Details</h2>
                <div class="flex gap-2">
                    <button onclick="editStudent()" class="text-blue-600 hover:text-blue-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="p-6 overflow-y-auto">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Student Image and Basic Info -->
                <div class="flex-shrink-0">
                    <img id="modalImage" src="" alt="Student Image" class="w-32 h-32 rounded-lg object-cover border">
                </div>
                
                <div class="flex-grow">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800" id="modalFirstName"></h3>
                            <p class="text-gray-600" id="modalLastName"></p>
                        </div>
                        <div class="space-y-2">
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Email:</span> 
                                <span id="modalEmail" class="text-gray-800"></span>
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Student ID:</span> 
                                <span id="modalStudentId" class="text-gray-800"></span>
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Contact:</span> 
                                <span id="modalContact" class="text-gray-800"></span>
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Section:</span> 
                                <span id="modalSections" class="text-gray-800"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Section -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Grade Statistics</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="text-md font-medium text-gray-700 mb-3">Top 3 Highest Grades</h4>
                        <canvas id="highestGradesChart"></canvas>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="text-md font-medium text-gray-700 mb-3">Top 3 Lowest Grades</h4>
                        <canvas id="lowestGradesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Grades Section -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Academic Performance</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prelim</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Midterm</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Final</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="modalGrades" class="bg-white divide-y divide-gray-200">
                            <!-- Grades injected by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div id="editStudentModal" class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-center items-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl mx-4 max-h-[90vh] flex flex-col">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800">Edit Student Information</h2>
                <button onclick="closeEditStudentModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <form action="update_student.php" method="POST" class="p-6 overflow-y-auto">
            <input type="hidden" id="edit_id" name="id">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                    <input type="text" id="edit_first_name" name="first_name" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input type="text" id="edit_last_name" name="last_name" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="edit_email" name="email" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Student ID</label>
                    <input type="text" id="edit_student_id" name="student_id" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact</label>
                    <input type="text" id="edit_contact" name="contact" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Faculty</label>
                    <select id="edit_faculty" name="faculty_name" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <?php foreach ($faculties as $faculty): ?>
                            <option value="<?= htmlspecialchars($faculty['name']) ?>">
                                <?= htmlspecialchars($faculty['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Course</label>
                    <input type="text" id="edit_course" name="course" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                    <input type="text" id="edit_sections" name="sections" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Subjects</label>
                <div id="edit-subjects-container" class="space-y-2 p-4 border border-gray-200 rounded-lg">
                    <?php
                    $subjectStmt = $conn->query("SELECT * FROM subjects");
                    while ($subject = $subjectStmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<div class="flex items-center space-x-2">
                            <input type="checkbox" 
                                   name="subjects[]" 
                                   value="' . htmlspecialchars($subject['subject_name']) . '" 
                                   id="edit_subject_' . $subject['id'] . '"
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="edit_subject_' . $subject['id'] . '" class="text-sm text-gray-700">
                                ' . htmlspecialchars($subject['subject_name']) . '
                            </label>
                        </div>';
                    }
                    ?>
                </div>
            </div>

            <div class="flex justify-end mt-6 gap-3">
                <button type="button" onclick="closeEditStudentModal()" 
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
                <button type="submit" name="update_student"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('tbody tr');
        
        tableRows.forEach(row => {
            const studentId = row.cells[1].textContent.toLowerCase();
            const name = row.cells[2].textContent.toLowerCase();
            const email = row.cells[3].textContent.toLowerCase();
            const contact = row.cells[4].textContent.toLowerCase();
            
            if (studentId.includes(searchTerm) || 
                name.includes(searchTerm) || 
                email.includes(searchTerm) || 
                contact.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});

let highestGradesChart = null;
let lowestGradesChart = null;
let currentStudent = null;

function calculateAverageGrade(grade) {
    const prelim = parseFloat(grade.prelim) || 0;
    const midterm = parseFloat(grade.midterm) || 0;
    const final = parseFloat(grade.final) || 0;
    const exam = parseFloat(grade.exam) || 0;
    
    // Count how many grades are actually present (non-zero)
    const validGrades = [prelim, midterm, final, exam].filter(g => g > 0);
    
    // Return null if there aren't enough grades
    if (validGrades.length === 0) return null;
    
    // Calculate average of existing grades
    return validGrades.reduce((a, b) => a + b, 0) / validGrades.length;
}

function updateGradeCharts(grades) {
    // Calculate average grades for each subject, filtering out incomplete grades
    const gradeData = grades
        .map(grade => ({
            subject: grade.subject,
            average: calculateAverageGrade(grade)
        }))
        .filter(grade => grade.average !== null); // Remove subjects with no grades

    // Get the chart containers
    const highestChartsContainer = document.getElementById('highestGradesChart').closest('.bg-white');
    const lowestChartsContainer = document.getElementById('lowestGradesChart').closest('.bg-white');

    // Hide charts if there aren't at least 3 subjects with grades
    if (gradeData.length < 3) {
        highestChartsContainer.style.display = 'none';
        lowestChartsContainer.style.display = 'none';
        return; // Exit function if we don't have enough grades
    }

    // Sort grades by average
    gradeData.sort((a, b) => b.average - a.average);

    // Check if any grades are below 85
    const hasLowGrades = gradeData.some(grade => grade.average < 85);
    
    // Hide charts if any grade is below 85
    if (hasLowGrades) {
        highestChartsContainer.style.display = 'none';
        lowestChartsContainer.style.display = 'none';
        return;
    }

    // Show charts if we have enough grades and all are 85 or above
    highestChartsContainer.style.display = 'block';
    lowestChartsContainer.style.display = 'block';

    // Get exactly 3 highest and 3 lowest
    const highestGrades = gradeData.slice(0, 3);
    const lowestGrades = gradeData.slice(-3).reverse();

    // Only proceed if we have exactly 3 grades for both charts
    if (highestGrades.length !== 3 || lowestGrades.length !== 3) {
        highestChartsContainer.style.display = 'none';
        lowestChartsContainer.style.display = 'none';
        return;
    }

    // Destroy existing charts if they exist
    if (highestGradesChart) highestGradesChart.destroy();
    if (lowestGradesChart) lowestGradesChart.destroy();

    // Create highest grades chart
    const highestCtx = document.getElementById('highestGradesChart').getContext('2d');
    highestGradesChart = new Chart(highestCtx, {
        type: 'bar',
        data: {
            labels: highestGrades.map(g => g.subject),
            datasets: [{
                label: 'Average Grade',
                data: highestGrades.map(g => g.average),
                backgroundColor: 'rgba(34, 197, 94, 0.5)',
                borderColor: 'rgb(34, 197, 94)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Create lowest grades chart
    const lowestCtx = document.getElementById('lowestGradesChart').getContext('2d');
    lowestGradesChart = new Chart(lowestCtx, {
        type: 'bar',
        data: {
            labels: lowestGrades.map(g => g.subject),
            datasets: [{
                label: 'Average Grade',
                data: lowestGrades.map(g => g.average),
                backgroundColor: 'rgba(239, 68, 68, 0.5)',
                borderColor: 'rgb(239, 68, 68)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

function openModal(student) {
    currentStudent = student;
    document.getElementById('modalImage').src = `/uploads/${student.image}`;
    document.getElementById('modalFirstName').textContent = student.first_name;
    document.getElementById('modalLastName').textContent = student.last_name;
    document.getElementById('modalEmail').textContent = student.email;
    document.getElementById('modalStudentId').textContent = student.student_id;
    document.getElementById('modalContact').textContent = student.contact;
    document.getElementById('modalSections').textContent = student.sections;

    const gradesBody = document.getElementById('modalGrades');
    gradesBody.innerHTML = '';

    if (student.grades && student.grades.length > 0) {
        updateGradeCharts(student.grades);
        student.grades.forEach(grade => {
            const row = `<tr>
                <td class="border px-4 py-2">${grade.subject}</td>
                <td class="border px-4 py-2">${grade.prelim || '-'}</td>
                <td class="border px-4 py-2">${grade.midterm || '-'}</td>
                <td class="border px-4 py-2">${grade.final || '-'}</td>
                <td class="border px-4 py-2">${grade.exam || '-'}</td>
                <td class="border px-4 py-2">${grade.attendance || '0'} days</td>
                <td class="border px-4 py-2">
                    <button onclick="editGrade('${grade.id}', event)" 
                            class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </button>
                </td>
            </tr>`;
            gradesBody.innerHTML += row;
        });
    } else {
        gradesBody.innerHTML = `<tr>
            <td colspan="7" class="px-4 py-2 text-center text-sm text-gray-500">
                No grades available for this student.
            </td>
        </tr>`;
    }

    document.getElementById('viewModal').classList.remove('hidden');
}

function editStudent() {
    // Populate the edit form
    document.getElementById('edit_id').value = currentStudent.id;
    document.getElementById('edit_first_name').value = currentStudent.first_name;
    document.getElementById('edit_last_name').value = currentStudent.last_name;
    document.getElementById('edit_email').value = currentStudent.email;
    document.getElementById('edit_student_id').value = currentStudent.student_id;
    document.getElementById('edit_contact').value = currentStudent.contact;
    document.getElementById('edit_faculty').value = currentStudent.faculty;
    document.getElementById('edit_course').value = currentStudent.course;
    document.getElementById('edit_sections').value = currentStudent.sections;

    // Set selected subjects
    const studentSubjects = currentStudent.subjects ? currentStudent.subjects.split(',').map(s => s.trim()) : [];
    document.querySelectorAll('#edit-subjects-container input[type="checkbox"]').forEach(checkbox => {
        checkbox.checked = studentSubjects.includes(checkbox.value);
    });

    // Hide view modal and show edit modal
    document.getElementById('viewModal').classList.add('hidden');
    document.getElementById('editStudentModal').classList.remove('hidden');
}

function closeEditStudentModal() {
    document.getElementById('editStudentModal').classList.add('hidden');
    document.getElementById('viewModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('viewModal').classList.add('hidden');
}

function editGrade(gradeId, event) {
    event.stopPropagation(); // Stop event from bubbling up to parent elements
    
    // Get the current row data
    const row = event.target.closest('tr');
    const cells = row.getElementsByTagName('td');
    
    // Show edit form for the specific grade
    Swal.fire({
        title: 'Edit Grade',
        html: `
            <form id="editGradeForm" class="space-y-4">
                <input type="hidden" id="edit_grade_id" value="${gradeId}">
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700">Subject</label>
                    <input type="text" id="edit_subject_display" value="${cells[0].textContent}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                </div>
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700">Prelim</label>
                    <input type="number" id="edit_prelim" value="${cells[1].textContent !== '-' ? cells[1].textContent : ''}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="0" max="100">
                </div>
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700">Midterm</label>
                    <input type="number" id="edit_midterm" value="${cells[2].textContent !== '-' ? cells[2].textContent : ''}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="0" max="100">
                </div>
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700">Final</label>
                    <input type="number" id="edit_final" value="${cells[3].textContent !== '-' ? cells[3].textContent : ''}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="0" max="100">
                </div>
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700">Exam</label>
                    <input type="number" id="edit_exam" value="${cells[4].textContent !== '-' ? cells[4].textContent : ''}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="0" max="100">
                </div>
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700">Attendance (days)</label>
                    <input type="number" id="edit_attendance" value="${cells[5].textContent.replace(' days', '')}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="0">
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Save Changes',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            const formData = new FormData();
            formData.append('action', 'edit');
            formData.append('id', document.getElementById('edit_grade_id').value);
            formData.append('prelim', document.getElementById('edit_prelim').value);
            formData.append('midterm', document.getElementById('edit_midterm').value);
            formData.append('final', document.getElementById('edit_final').value);
            formData.append('exam', document.getElementById('edit_exam').value);
            formData.append('attendance', document.getElementById('edit_attendance').value);

            return fetch('../../../app/Controllers/GradeController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to update grade');
                }
                return data;
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Grade has been updated successfully',
                timer: 1500
            }).then(() => {
                location.reload();
            });
        }
    }).catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message || 'Failed to update grade'
        });
    });
}

// Handle edit form submission
document.getElementById('editStudentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'update');

    // Debug log all form data
    console.log('Form data being sent:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    // Show loading alert
    Swal.fire({
        title: 'Updating...',
        text: 'Please wait while we update the student information',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Debug log the request URL
    console.log('Sending request to:', 'update_student.php');

    fetch('update_student.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Debug log the raw response
        console.log('Raw response:', response);
        return response.json();
    })
    .then(data => {
        // Debug log the parsed data
        console.log('Parsed response data:', data);
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'Student information has been updated successfully.',
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload();
                }
            });
        } else {
            console.error('Update failed:', data.message);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to update student information',
                confirmButtonColor: '#d33'
            });
        }
    })
    .catch(error => {
        // Debug log any errors
        console.error('Fetch error:', error);
        console.error('Error stack:', error.stack);
        
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An unexpected error occurred while updating student information. Check console for details.',
            confirmButtonColor: '#d33'
        });
    });
});

</script>

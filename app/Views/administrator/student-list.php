<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Config\Database;
use App\Models\User;
use App\Controllers\AuthController;

$db = new Database();
$conn = $db->connect();
$auth = new AuthController();

$message = '';

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
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, student_id, contact, image, subjects, sections, faculty, course FROM users WHERE role = 'student'");
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

<div class="p-8 w-full bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Header Section -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <h1 class="text-2xl font-bold text-gray-800">Student Management</h1>
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <input type="text" 
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
                            <tr class="hover:bg-gray-50 transition-colors">
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
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
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

<!-- Edit Grade Modal -->
<div id="editGradeModal" class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-center items-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800">Edit Grades</h2>
                <button onclick="closeEditGradeModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <form id="editGradeForm" method="POST" action="update_grade.php" class="p-6 space-y-4">
            <input type="hidden" id="edit_student_id" name="student_id">
            <input type="hidden" id="edit_subject" name="subject">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <input type="text" id="edit_subject_display" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg" readonly>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prelim</label>
                <input type="number" name="prelim" id="edit_prelim" step="0.01" min="0" max="100" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Midterm</label>
                <input type="number" name="midterm" id="edit_midterm" step="0.01" min="0" max="100" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Final</label>
                <input type="number" name="final" id="edit_final" step="0.01" min="0" max="100" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Exam</label>
                <input type="number" name="exam" id="edit_exam" step="0.01" min="0" max="100" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Attendance (Days)</label>
                <input type="number" name="attendance" id="edit_attendance" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4">
                <button type="button" onclick="closeEditGradeModal()" 
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Update Grades
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let highestGradesChart = null;
let lowestGradesChart = null;

function calculateAverageGrade(grade) {
    const prelim = parseFloat(grade.prelim) || 0;
    const midterm = parseFloat(grade.midterm) || 0;
    const final = parseFloat(grade.final) || 0;
    const exam = parseFloat(grade.exam) || 0;
    
    // Calculate average if at least one grade exists
    const grades = [prelim, midterm, final, exam].filter(g => g > 0);
    return grades.length > 0 ? grades.reduce((a, b) => a + b, 0) / grades.length : 0;
}

function updateGradeCharts(grades) {
    // Calculate average grades for each subject
    const gradeData = grades.map(grade => ({
        subject: grade.subject,
        average: calculateAverageGrade(grade)
    }));

    // Sort grades by average
    gradeData.sort((a, b) => b.average - a.average);

    // Get top 3 highest and lowest
    const highestGrades = gradeData.slice(0, 3);
    const lowestGrades = gradeData.slice(-3).reverse();

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
        // Update grade charts
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

function closeModal() {
    document.getElementById('viewModal').classList.add('hidden');
}

function editGrade(gradeId, event) {
    // Show the edit modal
    document.getElementById('editGradeModal').classList.remove('hidden');
    
    // Get the current row data
    const row = event.target.closest('tr');
    const cells = row.getElementsByTagName('td');
    
    // Set the values in the edit form
    document.getElementById('edit_subject_display').value = cells[0].textContent;
    document.getElementById('edit_prelim').value = cells[1].textContent !== '-' ? cells[1].textContent : '';
    document.getElementById('edit_midterm').value = cells[2].textContent !== '-' ? cells[2].textContent : '';
    document.getElementById('edit_final').value = cells[3].textContent !== '-' ? cells[3].textContent : '';
    document.getElementById('edit_exam').value = cells[4].textContent !== '-' ? cells[4].textContent : '';
    document.getElementById('edit_attendance').value = cells[5].textContent.replace(' days', '');
    
    // Store the grade ID
    document.getElementById('editGradeForm').setAttribute('data-grade-id', gradeId);
}

// Handle form submission
document.getElementById('editGradeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'edit');
    formData.append('id', this.getAttribute('data-grade-id'));
    formData.append('prelim', document.getElementById('edit_prelim').value);
    formData.append('midterm', document.getElementById('edit_midterm').value);
    formData.append('final', document.getElementById('edit_final').value);
    formData.append('exam', document.getElementById('edit_exam').value);
    formData.append('attendance', document.getElementById('edit_attendance').value);

    fetch('../../../app/Controllers/GradeController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating grades: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error saving grades');
    });
});

function closeEditGradeModal() {
    document.getElementById('editGradeModal').classList.add('hidden');
}
</script>

<script>
    document.getElementById('add-subject').addEventListener('click', function () {
        const container = document.getElementById('subjects-container');
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'subjects[]';
        input.placeholder = 'Subject';
        input.className = 'w-full px-4 py-2 border rounded-lg focus:ring mt-2';
        container.appendChild(input);
    });
</script>

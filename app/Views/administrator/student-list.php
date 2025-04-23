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


// Fetch students and their grades
foreach ($students as &$student) {
    $stmt = $conn->prepare("SELECT subject, prelim, midterm, final FROM grades WHERE student_id = ?");
    $stmt->execute([$student['id']]);
    $student['grades'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($student); // best practice
include './layout/sidebar.php';
?>


<div class="p-6 w-full">

   <!-- Registered Students Table -->
    <div class="bg-white shadow-lg rounded-lg p-6 mt-8">
        <h2 class="text-lg font-semibold mb-3">Registered Students</h2>
        <table class="w-full border-collapse border">
        <thead>
    <tr class="bg-blue-600 text-white">
        <th class="p-2 border">ID</th>
        <th class="p-2 border">First Name</th>
        <th class="p-2 border">Last Name</th>
        <th class="p-2 border">Email</th>
        <th class="p-2 border">Student ID</th>
        <th class="p-2 border">Contact</th>
        <th class="p-2 border">Actions</th>
    </tr>
</thead>
           <!-- Add buttons to each row -->
           <?php foreach ($students as $student): ?>
<tr class="border">
    <td class="p-2 border text-center"><?= htmlspecialchars($student['id']) ?></td>
    <img src="/uploads/<?= htmlspecialchars($student['image']) ?>" 
         alt="Student Image" 
         class="w-16 h-16 object-cover mx-auto rounded-full hidden">
    <td class="p-2 border text-center hidden"><?= htmlspecialchars($student['faculty_name']) ?></td>
    <td class="p-2 border text-center"><?= htmlspecialchars($student['first_name']) ?></td>
    <td class="p-2 border text-center"><?= htmlspecialchars($student['last_name']) ?></td>
    <td class="p-2 border text-center"><?= htmlspecialchars($student['email']) ?></td>
    <td class="p-2 border text-center"><?= htmlspecialchars($student['student_id']) ?></td>
    <td class="p-2 border text-center"><?= htmlspecialchars($student['contact']) ?></td>
    <td class="p-2 border text-center hidden"><?= htmlspecialchars($student['sections']) ?></td>
    <td class="p-2 border text-center hidden"><?= htmlspecialchars($student['subjects']) ?></td>
    <td class="p-2 border text-center hidden"><?= htmlspecialchars($student['prelim']) ?></td>
    <td class="p-2 border text-center hidden"><?= htmlspecialchars($student['semester']) ?></td>

    <td class="p-2 border text-center space-x-2">
        <a href="?delete=<?= $student['id'] ?>" 
           onclick="return confirm('Are you sure you want to delete this student?')" 
           class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</a>

        <button type="button" 
                onclick='openModal(<?= json_encode($student) ?>)' 
                class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">View</button>
    </td>
</tr>
<?php endforeach; ?>
</tbody>

        </table>
    </div>
</div>


<!-- Modal -->
<div id="viewModal" class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg w-full max-w-3xl shadow-lg relative">
        <button onclick="closeModal()" class="absolute top-2 right-2 text-red-500 text-2xl font-bold">&times;</button>

        <div class="flex space-x-6 items-center mb-4">
            <img id="modalImage" src="" alt="Student Image" class="w-24 h-24 object-cover rounded-full border">
            <div>
                <h2 id="modalFirstName" class="text-2xl font-bold"></h2>
                <p id="modalLastName" class="text-lg text-gray-700"></p>
                <p class="text-sm text-gray-600">Email: <span id="modalEmail"></span></p>
                <p class="text-sm text-gray-600">Student ID: <span id="modalStudentId"></span></p>
                <p class="text-sm text-gray-600">Contact: <span id="modalContact"></span></p>
                <p class="text-sm text-gray-600">Section: <span id="modalSections"></span></p>
            </div>
        </div>

        <h3 class="mt-4 text-lg font-semibold">Grades:</h3>
        <table class="w-full mt-2 text-sm border">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border px-4 py-2">Subject</th>
                    <th class="border px-4 py-2">Prelim</th>
                    <th class="border px-4 py-2">Midterm</th>
                    <th class="border px-4 py-2">Final</th>
                </tr>
            </thead>
            <tbody id="modalGrades" class="bg-white">
                <!-- Grades injected by JS -->
            </tbody>
        </table>
    </div>
</div>



<script>
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
        student.grades.forEach(grade => {
            const row = `<tr>
                <td class="border px-4 py-2">${grade.subject}</td>
                <td class="border px-4 py-2">${grade.prelim}</td>
                <td class="border px-4 py-2">${grade.midterm}</td>
                <td class="border px-4 py-2">${grade.final}</td>
            </tr>`;
            gradesBody.innerHTML += row;
        });
    } else {
        gradesBody.innerHTML = `<tr><td colspan="4" class="text-center p-2">No grades found.</td></tr>`;
    }

    document.getElementById('viewModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('viewModal').classList.add('hidden');
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

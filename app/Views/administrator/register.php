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
    $sections = $_POST['sections'] ?? [];
    $subjects = $_POST['subjects'] ?? [];

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
        $faculty
    );

    if ($result['success']) {
        header("Location: register.php?message=Registration successful");
        exit();
    } else {
        $message = $result['message'];
    }
}

// Fetch registered students
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, student_id, contact, image, subjects, sections, faculty FROM users WHERE role = 'student'");
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

include './layout/sidebar.php';
?>


<div class="p-6 w-full">
    <h1 class="text-2xl font-bold mb-4">Register Student</h1>

    <!-- Registration Form -->
    <div class="bg-white shadow-lg rounded-lg p-6 mt-8">
        <?php if ($message): ?>
            <p class="text-red-500 text-center"><?= $message ?></p>
        <?php endif; ?>
      <!-- Registration Form -->
<div class="bg-white shadow-lg rounded-lg p-6 mt-8">
    <?php if ($message): ?>
        <p class="text-red-500 text-center"><?= $message ?></p>
    <?php endif; ?>
    <form action="" method="POST" class="space-y-4" enctype="multipart/form-data">
    <select name="faculty_name" required class="w-full px-4 py-2 border rounded-lg focus:ring">
    <option value="">Select Faculty</option>
    <?php foreach ($faculties as $faculty): ?>
        <option value="<?= htmlspecialchars($faculty['name']) ?>">
            <?= htmlspecialchars($faculty['name']) ?>
        </option>
    <?php endforeach; ?>
</select>

        <input type="text" name="first_name" placeholder="First Name" required class="w-full px-4 py-2 border rounded-lg focus:ring">
        <input type="text" name="last_name" placeholder="Last Name" required class="w-full px-4 py-2 border rounded-lg focus:ring">
        <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2 border rounded-lg focus:ring">
        <input type="text" name="student_id" placeholder="Student ID" required class="w-full px-4 py-2 border rounded-lg focus:ring">
        <input type="text" name="sections" placeholder="Section" class="w-full px-4 py-2 border rounded-lg focus:ring">
        <input type="text" name="prelim" placeholder="Prelim" required class="w-full px-4 py-2 border rounded-lg focus:ring">
<input type="text" name="semester" placeholder="Semester" required class="w-full px-4 py-2 border rounded-lg focus:ring">

        <input type="text" name="contact" placeholder="Contact Number" required class="w-full px-4 py-2 border rounded-lg focus:ring">
        <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border rounded-lg focus:ring">
        <input type="file" name="image" accept="image/*" class="w-full px-4 py-2 border rounded-lg focus:ring">
        
        <div id="subjects-container">
            <label for="subjects" class="block font-medium">Subjects</label>
            <div class="flex space-x-4">
                <input type="text" name="subjects[]" placeholder="Subject" class="w-full px-4 py-2 border rounded-lg focus:ring">
            <button type="button" id="add-subject" class="text-blue-500 mt-2">Add Another Subject</button>
        </div>

        <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">Register Student</button>
    </form>
</div>

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
<tbody>
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
    onclick='viewStudent(<?= json_encode($student) ?>)' 
    class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
    View
</button>
</tr>
<?php endforeach; ?>
</tbody>

        </table>
    </div>
</div>
<!-- Modal -->

<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg w-96 relative">
        <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-500">&times;</button>
        <h3 class="text-xl font-semibold mb-4">Student Information</h3>
        <img id="modalImage" src="../../../uploads/" alt="Student Image" class="w-24 h-24 object-cover mx-auto rounded-full mb-4" />
        <!-- <p><strong>Faculty:</strong> <span id="modalFacultyName"></span></p> -->
        <p><strong>First Name:</strong> <span id="modalFirstName"></span></p>
        <p><strong>Last Name:</strong> <span id="modalLastName"></span></p>
        <p><strong>Email:</strong> <span id="modalEmail"></span></p>
        <p><strong>Student ID:</strong> <span id="modalStudentId"></span></p>
        <p><strong>Contact:</strong> <span id="modalContact"></span></p>
        <p><strong>Section:</strong> <span id="modalSections"></span></p>
        <!-- <p><strong>Prelim:</strong> <span id="modalPrelim"></span></p> -->
        <p><strong>Subjects:</strong>
            <ul id="modalSubjects" class="list-disc list-inside text-sm text-gray-700"></ul>
        </p>
    </div>
</div>





<script>
function viewStudent(student) {
     // Set image
     document.getElementById('modalImage').src = `/uploads/${student.image}`;
    // document.getElementById('modalFacultyName').innerText = student.faculty_name;

    document.getElementById('modalFirstName').innerText = student.first_name;
    document.getElementById('modalLastName').innerText = student.last_name;
    document.getElementById('modalEmail').innerText = student.email;
    document.getElementById('modalStudentId').innerText = student.student_id;
    document.getElementById('modalContact').innerText = student.contact;
    document.getElementById('modalSections').innerText = student.sections;


    const subjects = JSON.parse(student.subjects || '[]');
    
    const modalSubjects = document.getElementById('modalSubjects');
    // const modalSections = document.getElementById('modalSections');

    modalSubjects.innerHTML = ''; 
    // modalSections.innerHTML = ''; 


    for (let i = 0; i < subjects.length; i++) {
        const li = document.createElement('li');
        li.textContent = `${subjects[i]}}`; 
        modalSubjects.appendChild(li);
    }

    document.getElementById('viewModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('viewModal').classList.add('hidden');
}


    document.getElementById('add-subject').addEventListener('click', function() {
        const subjectContainer = document.getElementById('subjects-container');
        
 
        const newSubject = document.createElement('div');
        newSubject.classList.add('flex', 'space-x-4');
        newSubject.innerHTML = `
            <input type="text" name="subjects[]" placeholder="Subject" class="w-full px-4 py-2 border rounded-lg focus:ring">
           
        `;
        
        subjectContainer.appendChild(newSubject);
    });
</script>

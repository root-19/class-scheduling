<?php
require_once __DIR__ . '/../../Controllers/StudentController.php';
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Models/User.php';


use App\Controllers\StudentController;
use App\Config\Database;
$database = new Database();
$db = $database->connect();
$studentController = new StudentController($db);
$students = $studentController->getAllStudents();

include "./layout/sidebar.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 ">
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
<tbody>
<?php foreach ($students as $student): ?>
<tr class="border">
    <td class="p-2 border text-center"><?= htmlspecialchars($student['id']) ?></td>
    <td class="p-2 border text-center"><?= htmlspecialchars($student['first_name']) ?></td>
    <td class="p-2 border text-center"><?= htmlspecialchars($student['last_name']) ?></td>
    <td class="p-2 border text-center"><?= htmlspecialchars($student['email']) ?></td>
    <td class="p-2 border text-center"><?= htmlspecialchars($student['student_id']) ?></td>
    <td class="p-2 border text-center"><?= htmlspecialchars($student['contact']) ?></td>
    <td class="p-2 border text-center space-x-2">
        <a href="?delete=<?= $student['id'] ?>" 
           onclick="return confirm('Are you sure you want to delete this student?')" 
           class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</a>
           <button type="button"
    onclick="viewStudent(
        '<?= htmlspecialchars($student['id']) ?>',
        '<?= htmlspecialchars($student['first_name']) ?>',
        '<?= htmlspecialchars($student['last_name']) ?>',
        '<?= htmlspecialchars($student['email']) ?>',
        '<?= htmlspecialchars($student['student_id']) ?>',
        '<?= htmlspecialchars($student['contact']) ?>'
    )"
    class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
    View
</button>

    </td>
</tr>
<?php endforeach; ?>
</tbody>

        </table>
    </div>
</div>

<div id="viewStudentModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
  <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative">
    <h2 class="text-xl font-bold mb-4">Student Details</h2>
    <!-- <p><strong>ID:</strong> <span id="view_id"></span></p> -->
    <p><strong>First Name:</strong> <span id="view_first_name"></span></p>
    <p><strong>Last Name:</strong> <span id="view_last_name"></span></p>
    <p><strong>Email:</strong> <span id="view_email"></span></p>
    <p><strong>Student ID:</strong> <span id="view_student_id"></span></p>
    <p><strong>Contact:</strong> <span id="view_contact"></span></p>
    <button onclick="closeModal()" class="mt-4 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Close</button>
  </div>
</div>



<script>
function viewStudent(id, firstName, lastName, email, studentId, contact) {
    // document.getElementById('view_id').textContent = id;
    document.getElementById('view_first_name').textContent = firstName;
    document.getElementById('view_last_name').textContent = lastName;
    document.getElementById('view_email').textContent = email;
    document.getElementById('view_student_id').textContent = studentId;
    document.getElementById('view_contact').textContent = contact;

    document.getElementById('viewStudentModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('viewStudentModal').classList.add('hidden');
}
</script>


<script>



    document.getElementById('add-subject').addEventListener('click', function() {
        const subjectContainer = document.getElementById('subjects-container');
        
        // Create new subject and section input fields
        const newSubject = document.createElement('div');
        newSubject.classList.add('flex', 'space-x-4');
        newSubject.innerHTML = `
            <input type="text" name="subjects[]" placeholder="Subject" class="w-full px-4 py-2 border rounded-lg focus:ring">
            <input type="text" name="sections[]" placeholder="Section" class="w-full px-4 py-2 border rounded-lg focus:ring">
        `;
        
        subjectContainer.appendChild(newSubject);
    });
</script>


    <script>
// function viewStudent(student) {
//     document.getElementById('modalFirstName').innerText = student.first_name;
//     document.getElementById('modalLastName').innerText = student.last_name;
//     document.getElementById('modalEmail').innerText = student.email;
//     document.getElementById('modalStudentId').innerText = student.student_id;
//     document.getElementById('modalContact').innerText = student.contact;
//     document.getElementById('viewModal').classList.remove('hidden');
// }

// function closeModal() {
//     document.getElementById('viewModal').classList.add('hidden');
// }

//         function viewStudent(id) {
//             fetch(`get_student.php?id=${id}`)
//                 .then(response => response.json())
//                 .then(data => {
//                     document.getElementById("modalName").innerText = data.first_name + " " + data.last_name;
//                     document.getElementById("modalEmail").innerText = data.email;
//                     document.getElementById("studentModal").classList.remove("hidden");
//                 });
//         }

//         function closeModal() {
//             document.getElementById("studentModal").classList.add("hidden");
//         }

        function deleteStudent(id) {
            Swal.fire({
                title: "Are you sure?",
                text: "This action cannot be undone!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`delete_student.php?id=${id}`, { method: "POST" })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire("Deleted!", "Student has been deleted.", "success")
                                    .then(() => location.reload());
                            } else {
                                Swal.fire("Error!", "Failed to delete student.", "error");
                            }
                        });
                }
            });
        }
    </script>
</body>
</html>

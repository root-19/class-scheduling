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
    <div class="max-w-6xl mx-auto bg-white shadow-lg p-6 rounded-lg">
        <h2 class="text-lg font-bold mb-4">Student List</h2>
        <table class="w-full bg-white border border-gray-300 rounded-lg overflow-hidden shadow-md">
            <thead class="bg-gray-200">
                <tr>
                    <th class="p-3 text-left">ID</th>
                    <th class="p-3 text-left">Student ID</th>
                    <th class="p-3 text-left">Name</th>
                    <th class="p-3 text-left">Contact</th>

                    <th class="p-3 text-left">Email</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr class="border-t">
                        <td class="p-3"><?php echo $student['id']; ?></td>
                        <td class="p-3"><?php echo $student['student_id']; ?></td>
                        <td class="p-3"><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></td>
                        <td class="p-3"><?php echo $student['contact']; ?></td>
                        <td class="p-3"><?php echo $student['email']; ?></td>
                        <td class="p-3">
                            <button onclick="viewStudent(<?php echo $student['id']; ?>)" class="px-3 py-1 bg-blue-500 text-white rounded">View</button>
                            <button onclick="editStudent(<?php echo $student['id']; ?>)" class="px-3 py-1 bg-green-500 text-white rounded">Edit</button>
                            <button onclick="deleteStudent(<?php echo $student['id']; ?>)" class="px-3 py-1 bg-red-500 text-white rounded">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

    <!-- Modal for View / Edit -->
    <div id="studentModal" class="hidden fixed inset-0 flex items-center justify-center z-50">
        <div class="modal-overlay absolute inset-0 bg-black bg-opacity-50" onclick="closeModal()"></div>
        <div class="bg-white p-6 rounded-lg shadow-xl w-1/3 relative z-50">
            <h3 class="text-lg font-bold mb-4">Student Details</h3>
            <p><strong>Name:</strong> <span id="modalName"></span></p>
            <p><strong>Email:</strong> <span id="modalEmail"></span></p>
            <button onclick="closeModal()" class="mt-4 px-4 py-2 bg-red-500 text-white rounded">Close</button>
        </div>
    </div>

    <script>
        function viewStudent(id) {
            fetch(`get_student.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById("modalName").innerText = data.first_name + " " + data.last_name;
                    document.getElementById("modalEmail").innerText = data.email;
                    document.getElementById("studentModal").classList.remove("hidden");
                });
        }

        function closeModal() {
            document.getElementById("studentModal").classList.add("hidden");
        }

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

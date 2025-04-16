<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
session_start();
use App\Config\Database;

$db = new Database();
$conn = $db->connect();

if (!isset($_SESSION['faculty_name'])) {
    // header("Location: login.php");
}

$loggedInFacultyName = $_SESSION['faculty_name'];
// Fetch users under the logged-in faculty
$stmt = $conn->prepare("SELECT * FROM users WHERE faculty = ?");
$stmt->execute([$loggedInFacultyName]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

include './layout/sidebar.php';
?>

<div class="p-6 w-full">
    <h1 class="text-2xl font-bold mb-4">Students Under Faculty: <?= htmlspecialchars($loggedInFacultyName) ?></h1>

    <div class="bg-white shadow-lg rounded-lg p-6 mt-4">
        <?php if (empty($students)): ?>
            <p class="text-gray-500 text-center">No students assigned to your faculty.</p>
        <?php else: ?>
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
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr class="border text-center">
                            <td class="p-2 border"><?= htmlspecialchars($student['id']) ?></td>
                            <td class="p-2 border"><?= htmlspecialchars($student['first_name']) ?></td>
                            <td class="p-2 border"><?= htmlspecialchars($student['last_name']) ?></td>
                            <td class="p-2 border"><?= htmlspecialchars($student['email']) ?></td>
                            <td class="p-2 border"><?= htmlspecialchars($student['student_id']) ?></td>
                            <td class="p-2 border"><?= htmlspecialchars($student['contact']) ?></td>
                            <td class="p-2 border">
                                <a href="view-user.php?id=<?= $student['id'] ?>" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

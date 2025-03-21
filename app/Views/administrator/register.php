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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $student_id = $_POST['student_id'];
    $contact = $_POST['contact'];
    $password = $_POST['password'];

    $result = $auth->register($firstName, $lastName, $email, $student_id, $contact, $password, 'student'); // Role set to 'student'

    if ($result['success']) {
        header("Location: register.php?message=Registration successful");
        exit();
    } else {
        $message = "Error registering user.";
    }
}

// Fetch registered students
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, student_id, contact FROM users WHERE role = 'student'");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

include './layout/sidebar.php';
?>

<div class="p-6 w-full">
    <h1 class="text-2xl font-bold mb-4">Register Student</h1>

    <!-- Registration Form -->
    <div class="bg-white shadow-lg rounded-lg p-6 mt-8">
        <?php if ($message): ?>
            <p class="text-red-500 text-center"><?= $message ?></p>
        <?php endif; ?>
        <form action="" method="POST" class="space-y-4">
            <input type="text" name="first_name" placeholder="First Name" required class="w-full px-4 py-2 border rounded-lg focus:ring">
            <input type="text" name="last_name" placeholder="Last Name" required class="w-full px-4 py-2 border rounded-lg focus:ring">
            <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2 border rounded-lg focus:ring">
            <input type="text" name="student_id" placeholder="Student ID" required class="w-full px-4 py-2 border rounded-lg focus:ring">
            <input type="text" name="contact" placeholder="Contact Number" required class="w-full px-4 py-2 border rounded-lg focus:ring">
            <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border rounded-lg focus:ring">
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
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($students)): ?>
                    <?php foreach ($students as $student): ?>
                        <tr class="border">
                            <td class="p-2 border text-center"><?= htmlspecialchars($student['id']) ?></td>
                            <td class="p-2 border text-center"><?= htmlspecialchars($student['first_name']) ?></td>
                            <td class="p-2 border text-center"><?= htmlspecialchars($student['last_name']) ?></td>
                            <td class="p-2 border text-center"><?= htmlspecialchars($student['email']) ?></td>
                            <td class="p-2 border text-center"><?= htmlspecialchars($student['student_id']) ?></td>
                            <td class="p-2 border text-center"><?= htmlspecialchars($student['contact']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center p-4">No students registered.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

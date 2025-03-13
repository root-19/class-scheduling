<?php  
session_start();

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Controllers/SubjectController.php';


use App\Controllers\SubjectController;

$subjectController = new SubjectController();
$subjectController->handleRequest();
$subjects = $subjectController->getSubjects();
include './layout/sidebar.php';
?>
 <div class="p-6 w-full">
        <h1 class="    text-2xl font-bold mb-4">Manage Subjects</h1>

        <!-- Subject Form -->
        <form method="POST" action="dashboard.php" class="bg-white p-4 shadow-md rounded-lg mb-6">
            <div>
                <label class="block font-semibold">Subject Name:</label>
                <input type="text" name="subject_name" required class="w-full p-2 border rounded-lg">
            </div>
            <div class="mt-2">
                <label class="block font-semibold">Description:</label>
                <textarea name="description" required class="w-full p-2 border rounded-lg"></textarea>
            </div>
            <button type="submit" name="add_subject" class="mt-3 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Add Subject</button>
        </form>

        <!-- Subjects Table -->
        <div class="bg-white p-4 shadow-md rounded-lg">
            <h2 class="text-lg font-semibold mb-3">Subject List</h2>
            <table class="w-full border-collapse border">
                <thead>
                    <tr class="bg-blue-600 text-white">
                        <th class="p-2 border">ID</th>
                        <th class="p-2 border">Subject</th>
                        <th class="p-2 border">Description</th>
                        <th class="p-2 border">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $row): ?>
                        <tr class="border">
                            <td class="p-2 border"><?= $row['id'] ?></td>
                            <td class="p-2 border"><?= $row['subject_name'] ?></td>
                            <td class="p-2 border"><?= $row['description'] ?></td>
                            <td class="p-2 border text-center">
                                <a href="dashboard.php?delete=<?= $row['id'] ?>" class="bg-red-600 text-white px-3 py-1 rounded-lg hover:bg-red-700">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

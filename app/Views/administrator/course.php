<?php  
session_start();

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Controllers/CourseController.php';

use App\Controllers\CourseController;

$courseController = new CourseController();
$courseController->handleRequest();
$course = $courseController->getCourse();

$editMode = false;
$course_id = "";
$course_name = "";
$description = "";

if (isset($_GET['edit'])) {
    $editMode = true;
    $course_id = $_GET['edit'];
    $courseData = $courseController->getCourseById($course_id);
    $course_name = $courseData['course_name'];
    $description = $courseData['description'];
}

include './layout/sidebar.php';
?>

<div class="p-6 w-full">
    <h1 class="text-2xl font-bold mb-4">Manage Course</h1>

    <!-- Course Form -->
    <form method="POST" action="course.php" class="bg-white p-4 shadow-md rounded-lg mb-6">
        <input type="hidden" name="course_id" value="<?= $editMode ? $course_id : '' ?>">

        <div>
            <label class="block font-semibold">Course Name:</label>
            <input type="text" name="course_name" value="<?= htmlspecialchars($course_name) ?>" required class="w-full p-2 border rounded-lg">
        </div>
        <div class="mt-2">
            <label class="block font-semibold">Description:</label>
            <textarea name="description" required class="w-full p-2 border rounded-lg"><?= htmlspecialchars($description) ?></textarea>
        </div>
        <button type="submit" name="<?= $editMode ? 'update_course' : 'add_course' ?>" class="mt-3 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <?= $editMode ? 'Update Course' : 'Add Course' ?>
        </button>
    </form>

    <!-- Course Table -->
    <div class="bg-white p-4 shadow-md rounded-lg">
        <h2 class="text-lg font-semibold mb-3">Course List</h2>
        <table class="w-full border-collapse border">
            <thead>
                <tr class="bg-blue-600 text-white">
                    <th class="p-2 border">ID</th>
                    <th class="p-2 border">Course</th>
                    <th class="p-2 border">Description</th>
                    <th class="p-2 border">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($course) && is_array($course)): ?>
                    <?php foreach ($course as $row): ?>
                        <tr class="border">
                            <td class="p-2 border"><?= htmlspecialchars($row['id']) ?></td>
                            <td class="p-2 border"><?= htmlspecialchars($row['course_name']) ?></td>
                            <td class="p-2 border"><?= htmlspecialchars($row['description']) ?></td>
                            <td class="p-2 border text-center">
                                <a href="course.php?edit=<?= $row['id'] ?>" class="bg-yellow-500 text-white px-3 py-1 rounded-lg hover:bg-yellow-600">Edit</a>
                                <a href="course.php?delete=<?= $row['id'] ?>" class="bg-red-600 text-white px-3 py-1 rounded-lg hover:bg-red-700">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center p-4">No courses available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

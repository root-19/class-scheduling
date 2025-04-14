<?php
require_once __DIR__ . '/../../Controllers/ScheduleController.php';
require_once __DIR__ . '/../../Config/Database.php';

use App\Controllers\ScheduleController;
use App\Config\Database; // ✅ Make sure this comes before you use "new Database()"

// Initialize schedule controller
$scheduleController = new ScheduleController();
$schedulesJson = $scheduleController->getSchedules();

// Fetch faculty list
$database = new Database();
$conn = $database->connect();

$facultyList = [];
$query = "SELECT id, name FROM faculty";
$stmt = $conn->prepare($query);
$stmt->execute();
$facultyList = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "./layout/sidebar.php";
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Form</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="p-6 w-full">
    <div class="max-w-4xl mx-auto bg-white shadow-lg p-6 rounded-lg">

        <h2 class="text-lg font-bold mb-4">Add Schedule</h2>
        <form id="scheduleForm" method="POST" class="space-y-2">
        <select name="faculty" required class="w-full p-2 border rounded">
    <option value="" disabled selected>Select Faculty</option>
    <?php foreach ($facultyList as $faculty): ?>
        <option value="<?= htmlspecialchars($faculty['name']) ?>">
            <?= htmlspecialchars($faculty['name']) ?>
        </option>
    <?php endforeach; ?>
    </select>
    <input type="text" name="day_of_week" placeholder="Day of Week" required class="w-full p-2 border rounded">
    <input type="text" name="subject" placeholder="Subject" required class="w-full p-2 border rounded">
    
    <!-- Calendar-based Month From Input -->
    <input type="month" name="month_from" required class="w-full p-2 border rounded">
    
    <!-- Calendar-based Month To Input -->
    <input type="month" name="month_to" required class="w-full p-2 border rounded">

    <!-- Building Dropdown -->
    <select name="building" required class="w-full p-2 border rounded">
        <option value="" disabled selected>Select Building</option>
        <option value="Building 1">Building 1</option>
        <option value="Building 2">Building 2</option>
        <option value="Building 3">Building 3</option>
        <option value="Building 4">Building 4</option>
        <option value="Building 5">Building 5</option>
    </select>
    
    <input type="text" name="room" placeholder="Room" required class="w-full p-2 border rounded">
    <input type="text" name="department" placeholder="Department" required class="w-full p-2 border rounded">
    <input type="time" name="time_from" required class="w-full p-2 border rounded">
    <input type="time" name="time_to" required class="w-full p-2 border rounded">
    <input type="text" name="course" placeholder="Course" required class="w-full p-2 border rounded">
    <input type="text" name="section" placeholder="Section" required class="w-full p-2 border rounded">
    <div class="flex justify-end space-x-2">
        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded">Save</button>
    </div>
</form>

    </div>
    </div>

    <!-- Form submission to backend -->
    <script>
        document.querySelector("#scheduleForm").addEventListener("submit", function(event) {
            event.preventDefault();

            let formData = new FormData(this);

            fetch("../../Controllers/ScheduleController.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === "success") {
                    location.reload();
                }
            })
            .catch(error => console.error("Error:", error));
        });
    </script>
</body>
</html>

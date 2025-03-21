<?php
require_once __DIR__ . '/../../Controllers/ScheduleController.php';

use App\Controllers\ScheduleController;

$scheduleController = new ScheduleController();
$schedulesJson = $scheduleController->getSchedules();

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
            <input type="text" name="faculty" placeholder="Faculty" required class="w-full p-2 border rounded">
            <input type="text" name="day_of_week" placeholder="Day of Week" required class="w-full p-2 border rounded">
            <input type="text" name="subject" placeholder="Subject" required class="w-full p-2 border rounded">
            <input type="text" name="month_from" placeholder="Month From" required class="w-full p-2 border rounded">
            <input type="text" name="month_to" placeholder="Month To" required class="w-full p-2 border rounded">
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

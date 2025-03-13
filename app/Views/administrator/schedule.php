<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
use App\Controllers\ScheduleController;

$scheduleController = new ScheduleController();

if (isset($_GET['action']) && $_GET['action'] == 'fetch') {
    $scheduleController->getSchedulesJson();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $scheduleController->addNewSchedule();
    exit();
}

include './layout/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-900">

<div class="flex justify-between items-center">
    <h1 class="text-2xl font-bold text-blue-700">Schedule Management</h1>
    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow-md hover:bg-blue-700 transition">
        + Add Schedule
    </button>
</div>

    <div class="p-6">
        <div class="bg-white shadow-lg rounded-lg p-4">
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Modal -->
    <div id="scheduleModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg">
            <h2 class="text-xl font-semibold mb-4">Add Schedule</h2>
            <form id="scheduleForm" class="space-y-3">
                <input type="text" id="faculty" placeholder="Faculty" class="border p-2 w-full rounded">
                <input type="text" id="day_of_week" placeholder="Day" class="border p-2 w-full rounded">
                <input type="text" id="subject" placeholder="Subject" class="border p-2 w-full rounded">
                <input type="date" id="month_from" class="border p-2 w-full rounded">
                <input type="date" id="month_to" class="border p-2 w-full rounded">
                <input type="text" id="room" placeholder="Room" class="border p-2 w-full rounded">
                <input type="text" id="department" placeholder="Department" class="border p-2 w-full rounded">
                <input type="time" id="time_from" class="border p-2 w-full rounded">
                <input type="time" id="time_to" class="border p-2 w-full rounded">
                <input type="text" id="course" placeholder="Course" class="border p-2 w-full rounded">
                <input type="text" id="section" placeholder="Section" class="border p-2 w-full rounded">
                <input type="number" id="ratio" placeholder="Ratio" class="border p-2 w-full rounded">
                
                <div class="flex justify-end space-x-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700">Save</button>
                    <button type="button" onclick="closeModal()" class="bg-gray-400 text-white px-4 py-2 rounded-lg shadow hover:bg-gray-500">Close</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let calendarEl = document.getElementById("calendar");
            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: "dayGridMonth",
                themeSystem: "bootstrap",
                headerToolbar: {
                    left: "prev,next today",
                    center: "title",
                    right: "dayGridMonth,timeGridWeek,timeGridDay"
                },
                events: "schedule.php?action=fetch",
            });
            calendar.render();
        });

        function openModal() {
            document.getElementById("scheduleModal").classList.remove("hidden");
        }

        function closeModal() {
            document.getElementById("scheduleModal").classList.add("hidden");
        }

        document.getElementById("scheduleForm").addEventListener("submit", function (e) {
            e.preventDefault();
            let formData = {
                faculty: document.getElementById("faculty").value,
                day_of_week: document.getElementById("day_of_week").value,
                subject: document.getElementById("subject").value,
                month_from: document.getElementById("month_from").value,
                month_to: document.getElementById("month_to").value,
                room: document.getElementById("room").value,
                department: document.getElementById("department").value,
                time_from: document.getElementById("time_from").value,
                time_to: document.getElementById("time_to").value,
                course: document.getElementById("course").value,
                section: document.getElementById("section").value,
                ratio: document.getElementById("ratio").value
            };

            fetch("schedule.php", {
                method: "POST",
                body: JSON.stringify(formData),
                headers: { "Content-Type": "application/json" }
            }).then(response => response.json()).then(() => location.reload());
        });
    </script>
</body>
</html>

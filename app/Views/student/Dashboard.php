<?php
require_once __DIR__ . '/../../Controllers/ScheduleController.php';
use App\Controllers\ScheduleController;
session_start();
$scheduleController = new ScheduleController();

$departmentFilter = $_GET['department'] ?? null;
$schedules = $scheduleController->getSchedules($departmentFilter);
$departments = $scheduleController->getDepartments();
$isFiltering = !empty($departmentFilter);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Schedule Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .modal { display: none; z-index: 50; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); }
    </style>
</head>
<body class="bg-gray-100">

<div class="p-6 w-full">
    <div class="max-w-6xl mx-auto bg-white shadow-lg p-6 rounded-lg">
    <div class="mb-6 p-4 bg-white shadow-md rounded-xl border border-gray-200">
    <?php if ($_SESSION['role'] === 'student') { ?>
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <svg class="w-6 h-6 text-blue-500 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M5.121 17.804A13.937 13.937 0 0112 15c2.485 0 4.79.664 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path d="M19.428 15.341A8 8 0 106.57 15.34M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Student Profile
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-gray-700">
            <p><strong class="font-medium text-gray-900">Name:</strong> <?= $_SESSION['first_name'] ?? 'N/A' ?> <?= $_SESSION['last_name'] ?? 'N/A' ?></p>
            <!-- <p><strong class="font-medium text-gray-900">Faculty:</strong> <?= $_SESSION['faculty_name'] ?? 'N/A' ?></p> -->
            <p><strong class="font-medium text-gray-900">Course:</strong> <?= $_SESSION['course'] ?? 'N/A' ?></p>
            <p><strong class="font-medium text-gray-900">Section:</strong> <?= $_SESSION['sections'] ?? 'N/A' ?></p>
        </div>
    <?php } ?>
</div>


        <h2 class="text-lg font-bold mb-4">Schedule Calendar</h2>
        <div id="calendar"></div>
    </div>
</div>

<!-- Modal -->
<div id="scheduleModal" class="modal fixed inset-0 flex items-center justify-center">
    <div class="modal-overlay" onclick="closeModal()"></div>
    <div class="bg-white p-6 rounded-lg shadow-xl w-1/3 z-50 relative">
        <h3 class="text-lg font-bold mb-4" id="modalTitle"></h3>
        <p><strong>Faculty:</strong> <span id="modalFaculty"></span></p>
        <p><strong>Room:</strong> <span id="modalRoom"></span></p>
        <p><strong>Department:</strong> <span id="modalDepartment"></span></p>
        <p><strong>Course:</strong> <span id="modalCourse"></span></p>
        <p><strong>Section:</strong> <span id="modalSection"></span></p>
        <p><strong>Time:</strong> <span id="modalTime"></span></p>
        <!-- <p><strong>Time end:</strong> <span id="modalTime_end"></span></p> -->

        <p><strong>building:</strong> <span id="modalBuilding"></span></p>

        <div class="mt-6 flex justify-end">
            <button onclick="closeModal()" class="px-4 py-2 bg-red-500 text-white rounded shadow-lg hover:bg-red-600">Close</button>
        </div>
    </div>
</div>

<script>
    const schedules = <?= json_encode($schedules) ?>;
    const isFiltering = <?= json_encode($isFiltering) ?>;

    document.addEventListener("DOMContentLoaded", function () {
        const calendarEl = document.getElementById("calendar");
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: "dayGridMonth",
            events: schedules,
            eventClick: function (info) {
                if (isFiltering) {
                    // When filtered by department, DO NOT open modal
                    return;
                }

                const props = info.event.extendedProps;
                document.getElementById("modalTitle").innerText = info.event.title;
                document.getElementById("modalFaculty").innerText = props.faculty;
                document.getElementById("modalRoom").innerText = props.room;
                document.getElementById("modalDepartment").innerText = props.department;
                document.getElementById("modalCourse").innerText = props.course;
                document.getElementById("modalSection").innerText = props.section;
                document.getElementById("modalTime").innerText = props.time_from + " - " + props.time_to;
                document.getElementById("modalBuilding").innerText = props.building;

                document.getElementById("scheduleModal").style.display = "flex";
            }
        });
        calendar.render();
    });

    function closeModal() {
        document.getElementById("scheduleModal").style.display = "none";
    }

    function filterEvents() {
        const selectedDept = document.getElementById("departmentFilter").value;
        const url = new URL(window.location.href);
        url.searchParams.set('department', selectedDept);
        window.location.href = url.toString();
    }
</script>

</body>
</html>

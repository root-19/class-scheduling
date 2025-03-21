<?php
require_once __DIR__ . '/../../Controllers/ScheduleController.php';
use App\Controllers\ScheduleController;

$scheduleController = new ScheduleController();
$schedules = $scheduleController->getSchedules();

include "./layout/sidebar.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Calendar</title>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Ensure modal is on top */
        .modal {
            display: none;
            z-index: 50;
        }
        /* Background overlay */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="p-6 w-full">
        <div class="max-w-6xl mx-auto bg-white shadow-lg p-6 rounded-lg">
            <h2 class="text-lg font-bold mb-4">Schedule Calendar</h2>
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Modal for Schedule Details -->
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
            <div class="mt-6 flex justify-end">
                <button onclick="closeModal()" class="px-4 py-2 bg-red-500 text-white rounded shadow-lg hover:bg-red-600">Close</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var calendarEl = document.getElementById("calendar");
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: "dayGridMonth",
                events: <?php echo json_encode($schedules); ?>,
                eventClick: function(info) {
                    let event = info.event;
                    document.getElementById("modalTitle").innerText = event.title;
                    document.getElementById("modalFaculty").innerText = event.extendedProps.faculty;
                    document.getElementById("modalRoom").innerText = event.extendedProps.room;
                    document.getElementById("modalDepartment").innerText = event.extendedProps.department;
                    document.getElementById("modalCourse").innerText = event.extendedProps.course;
                    document.getElementById("modalSection").innerText = event.extendedProps.section;
                    document.getElementById("modalTime").innerText = event.extendedProps.time_from + " - " + event.extendedProps.time_to;
                    
                    document.getElementById("scheduleModal").style.display = "flex";
                }
            });
            calendar.render();
        });

        function closeModal() {
            document.getElementById("scheduleModal").style.display = "none";
        }
    </script>
</body>
</html>

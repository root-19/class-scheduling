<?php
require_once __DIR__ . '/../../Controllers/ScheduleController.php';
use App\Controllers\ScheduleController;

$scheduleController = new ScheduleController();

$departmentFilter = $_GET['department'] ?? null;
$schedules = $scheduleController->getSchedules($departmentFilter);
$departments = $scheduleController->getDepartments();
$isFiltering = !empty($departmentFilter);

include "./layout/sidebar.php";
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
        
        /* Custom FullCalendar Styles */
        .fc-event {
            cursor: pointer;
            transition: transform 0.2s;
        }
        .fc-event:hover {
            transform: scale(1.02);
        }
        .fc-toolbar-title {
            font-size: 1.5rem !important;
            font-weight: 600 !important;
        }
        .fc-button-primary {
            background-color: #3b82f6 !important;
            border-color: #3b82f6 !important;
        }
        .fc-button-primary:hover {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
        }
        .fc-daygrid-day-number {
            font-size: 0.9rem;
            color: #4b5563;
        }
    </style>
</head>
<body class="bg-gray-50">

<div class="p-8 w-full">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Header Section -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <h1 class="text-2xl font-bold text-gray-800">Schedule Calendar</h1>
                    <div class="flex items-center gap-4">
                        <label for="departmentFilter" class="text-sm font-medium text-gray-700">Department:</label>
                        <select id="departmentFilter" 
                            class="block w-64 px-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="filterEvents()">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept) { ?>
                                <option value="<?= htmlspecialchars($dept['department']) ?>" 
                                    <?= $departmentFilter === $dept['department'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['department']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Calendar Section -->
            <div class="p-6">
                <div id="calendar" class="calendar-container"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="scheduleModal" class="modal fixed inset-0 flex items-center justify-center">
    <div class="modal-overlay" onclick="closeModal()"></div>
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 z-50 relative">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800" id="modalTitle"></h3>
        </div>
        
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Faculty</p>
                    <p class="mt-1 text-sm text-gray-900" id="modalFaculty"></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Room</p>
                    <p class="mt-1 text-sm text-gray-900" id="modalRoom"></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Department</p>
                    <p class="mt-1 text-sm text-gray-900" id="modalDepartment"></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Course</p>
                    <p class="mt-1 text-sm text-gray-900" id="modalCourse"></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Section</p>
                    <p class="mt-1 text-sm text-gray-900" id="modalSection"></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Time</p>
                    <p class="mt-1 text-sm text-gray-900" id="modalTime"></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Building</p>
                    <p class="mt-1 text-sm text-gray-900" id="modalBuilding"></p>
                </div>
            </div>
        </div>

        <div class="p-6 bg-gray-50 rounded-b-xl flex justify-end">
            <button onclick="closeModal()" 
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                Close
            </button>
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
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: schedules,
            eventClick: function (info) {
                if (isFiltering) {
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

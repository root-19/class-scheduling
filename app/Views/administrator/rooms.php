<?php
require_once __DIR__ . '/../../Controllers/ScheduleController.php';
use App\Controllers\ScheduleController;

$scheduleController = new ScheduleController();
$schedules = $scheduleController->getSchedules();
$departments = $scheduleController->getDepartments();
include "./layout/sidebar.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Schedule Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .hidden { display: none; }
        .calendar-container { max-width: 1200px; margin: 0 auto; }
    </style>
</head>
<body class="bg-gray-100">
<div class="p-6 w-full">
    <div class="max-w-6xl mx-auto bg-white shadow-lg p-6 rounded-lg">
        <h2 class="text-lg font-bold mb-4">Schedule Calendar</h2>

        <!-- Department Dropdown -->
        <div class="mb-4">
            <label for="departmentFilter" class="text-lg font-semibold">Select Department:</label>
            <select id="departmentFilter" class="mt-2 p-2 border rounded" onchange="filterEvents()">
                <option value="">All Departments</option>
                <?php foreach ($departments as $department) { ?>
                    <option value="<?= $department['id'] ?>"><?= $department['department'] ?></option>
                <?php } ?>
            </select>
        </div>

        <!-- Calendar -->
        <div id="calendar" class="calendar-container"></div>

        <!-- Schedule Table (Visible when an event is clicked) -->
        <div id="scheduleTable" class="hidden mt-6 p-4 bg-white shadow-lg rounded-lg">
            <h3 class="text-xl font-bold mb-4" id="tableTitle"></h3>
            <table class="min-w-full border-collapse table-auto">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border">Faculty</th>
                        <th class="px-4 py-2 border">Room</th>
                        <th class="px-4 py-2 border">Department</th>
                        <th class="px-4 py-2 border">Course</th>
                        <th class="px-4 py-2 border">Section</th>
                        <th class="px-4 py-2 border">Time</th>
                    </tr>
                </thead>
                <tbody id="scheduleDetails"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const calendarEl = document.getElementById("calendar");
        let events = <?php echo json_encode($schedules); ?>;

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: "dayGridMonth",
            events: events,
            eventClick: function (info) {
                const event = info.event;

                // Skip events with no class
                if (event.title === "No Class") return;

                // Display event details in the table
                displayEventDetails(event);
            }
        });

        calendar.render();

        // Filter events based on selected department
        window.filterEvents = function() {
            const selectedDepartment = document.getElementById('departmentFilter').value;
            const filteredEvents = events.filter(event => 
                selectedDepartment === "" || event.extendedProps.department_id === selectedDepartment
            );
            calendar.removeAllEvents();
            calendar.addEventSource(filteredEvents);
        }
    });

    // Display event details in the table format
    function displayEventDetails(event) {
        document.getElementById("tableTitle").innerText = event.title || "Event Details";

        const details = `
            <tr>
                <td class="px-4 py-2 border">${event.extendedProps.faculty || "N/A"}</td>
                <td class="px-4 py-2 border">${event.extendedProps.room || "N/A"}</td>
                <td class="px-4 py-2 border">${event.extendedProps.department || "N/A"}</td>
                <td class="px-4 py-2 border">${event.extendedProps.course || "N/A"}</td>
                <td class="px-4 py-2 border">${event.extendedProps.section || "N/A"}</td>
                <td class="px-4 py-2 border">${event.extendedProps.time_from || "N/A"} - ${event.extendedProps.time_to || "N/A"}</td>
            </tr>
        `;
        document.getElementById("scheduleDetails").innerHTML = details;
        document.getElementById("scheduleTable").classList.remove("hidden");
    }

    window.filterEvents = function() {
    const selectedDepartment = document.getElementById('departmentFilter').value;
    const filteredEvents = events.filter(event => 
        selectedDepartment === "" || event.extendedProps.department_id === selectedDepartment
    );
    calendar.removeAllEvents();
    calendar.addEventSource(filteredEvents);
}

</script>
</body>
</html>

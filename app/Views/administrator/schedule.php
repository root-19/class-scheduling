<?php
require_once __DIR__ . '/../../Controllers/ScheduleController.php';
use App\Controllers\ScheduleController;

// Create a new instance of the controller
$scheduleController = new ScheduleController();

// Get the department filter from GET parameters
$departmentFilter = $_GET['department'] ?? null;

// Get schedules and departments
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

        /* Animation classes */
        .animate-fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        .animate-fade-out {
            animation: fadeOut 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        /* Input focus styles */
        input:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
        }

        /* Error state for inputs */
        input.error {
            border-color: #EF4444;
        }

        /* Time input specific styles */
        input[type="time"] {
            appearance: none;
            -webkit-appearance: none;
            padding: 0.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .modal > div {
                margin: 0.5rem;
                width: calc(100% - 1rem);
            }
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
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 z-50 relative">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800" id="modalTitle"></h3>
        </div>
        
        <div class="p-6">
            <form id="editScheduleForm" class="space-y-6">
                <input type="hidden" id="scheduleId" name="scheduleId">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Faculty -->
                    <div class="col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Faculty Name</label>
                        <input type="text" id="modalFaculty" name="faculty" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    <!-- Department -->
                    <div class="col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <input type="text" id="modalDepartment" name="department" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    <!-- Course -->
                    <div class="col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                        <input type="text" id="modalCourse" name="course" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    <!-- Section -->
                    <div class="col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Section</label>
                        <input type="text" id="modalSection" name="section" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    <!-- Time -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Time</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Start Time</label>
                                <input type="time" id="modalTimeFrom" name="time_from" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">End Time</label>
                                <input type="time" id="modalTimeTo" name="time_to" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Building</label>
                                <input type="text" id="modalBuilding" name="building" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Room</label>
                                <input type="text" id="modalRoom" name="room" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-6 bg-gray-50 rounded-b-xl flex justify-end space-x-3">
            <button onclick="closeModal()" 
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                Cancel
            </button>
            <button onclick="saveScheduleChanges()" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Save Changes
            </button>
        </div>
    </div>
</div>

<!-- Notification Toast -->
<div id="notificationToast" class="hidden fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    <p id="notificationMessage"></p>
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

                const event = info.event;
                const props = event.extendedProps;
                
                // Populate modal fields
                document.getElementById("scheduleId").value = event.id;
                document.getElementById("modalTitle").innerText = event.title;
                document.getElementById("modalFaculty").value = props.faculty;
                document.getElementById("modalRoom").value = props.room;
                document.getElementById("modalDepartment").value = props.department;
                document.getElementById("modalCourse").value = props.course;
                document.getElementById("modalSection").value = props.section;
                document.getElementById("modalTimeFrom").value = props.time_from;
                document.getElementById("modalTimeTo").value = props.time_to;
                document.getElementById("modalBuilding").value = props.building;

                document.getElementById("scheduleModal").style.display = "flex";
            }
        });
        calendar.render();
    });

    function closeModal() {
        document.getElementById("scheduleModal").style.display = "none";
    }

    function showNotification(message, isSuccess = true) {
        const toast = document.getElementById("notificationToast");
        const messageEl = document.getElementById("notificationMessage");
        
        toast.className = `fixed bottom-4 right-4 ${isSuccess ? 'bg-green-500' : 'bg-red-500'} text-white px-6 py-3 rounded-lg shadow-lg z-50`;
        messageEl.textContent = message;
        toast.classList.remove("hidden");
        
        // Add animation classes
        toast.classList.add('animate-fade-in');
        
        setTimeout(() => {
            toast.classList.add('animate-fade-out');
            setTimeout(() => {
                toast.classList.add("hidden");
                toast.classList.remove('animate-fade-in', 'animate-fade-out');
            }, 300);
        }, 3000);
    }

    function saveScheduleChanges() {
        const form = document.getElementById("editScheduleForm");
        const formData = new FormData(form);
        
        // Show loading state
        const saveButton = document.querySelector('button[onclick="saveScheduleChanges()"]');
        const originalText = saveButton.textContent;
        saveButton.textContent = 'Saving...';
        saveButton.disabled = true;
        
        fetch('../../Controllers/ScheduleController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showNotification('Schedule updated successfully');
                closeModal();
                // Refresh the calendar
                window.location.reload();
            } else {
                showNotification(data.message || 'Failed to update schedule', false);
            }
        })
        .catch(error => {
            showNotification('An error occurred while updating the schedule', false);
            console.error('Error:', error);
        })
        .finally(() => {
            // Reset button state
            saveButton.textContent = originalText;
            saveButton.disabled = false;
        });
    }

    function filterEvents() {
        const selectedDept = document.getElementById('departmentFilter').value;
        const url = new URL(window.location.href);
        url.searchParams.set('department', selectedDept);
        window.location.href = url.toString();
    }

    // Add validation before saving
    function validateScheduleForm() {
        const form = document.getElementById("editScheduleForm");
        const requiredFields = ['faculty', 'room', 'department', 'course', 'section', 'time_from', 'time_to'];
        let isValid = true;
        let firstInvalidField = null;

        requiredFields.forEach(field => {
            const input = form.elements[field];
            const value = input.value.trim();
            
            if (!value) {
                isValid = false;
                input.classList.add('border-red-500');
                if (!firstInvalidField) firstInvalidField = input;
            } else {
                input.classList.remove('border-red-500');
            }
        });

        if (!isValid && firstInvalidField) {
            firstInvalidField.focus();
            showNotification('Please fill in all required fields', false);
        }

        return isValid;
    }
</script>

</body>
</html>

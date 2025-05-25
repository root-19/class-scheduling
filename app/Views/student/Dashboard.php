<?php
require_once __DIR__ . '/../../Controllers/ScheduleController.php';
require_once __DIR__ . '/../../Config/Database.php';
use App\Controllers\ScheduleController;
session_start();
$scheduleController = new ScheduleController();

// Get all schedules
$schedules = $scheduleController->getSchedules();

// Debug information
error_log("Student Session Data: " . print_r($_SESSION, true));

// Filter schedules based on student's section, course, and faculty
$filteredSchedules = array_filter($schedules, function($schedule) {
    $matches = $schedule['extendedProps']['section'] === $_SESSION['sections'] && 
               $schedule['extendedProps']['course'] === $_SESSION['course'];
    
    // Debug information
    error_log("Schedule: " . print_r($schedule['extendedProps'], true));
    error_log("Matches: " . ($matches ? 'true' : 'false'));
    
    return $matches;
});

// Update schedules with filtered results
$schedules = array_values($filteredSchedules);

// Debug information
error_log("Filtered Schedules: " . print_r($schedules, true));

// ✅ Initialize DB connection
$db = (new \App\Config\Database())->connect();

// Fetch student grades
$stmt = $db->prepare("SELECT * FROM grades WHERE student_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$gradeStats = [];
foreach ($grades as $grade) {
    $prelim = floatval($grade['prelim'] ?? 0);
    $midterm = floatval($grade['midterm'] ?? 0);
    $final = floatval($grade['final'] ?? 0);
    $exam = floatval($grade['exam'] ?? 0);
    
    $gradesArray = array_filter([$prelim, $midterm, $final, $exam], function($g) { return $g > 0; });
    $average = !empty($gradesArray) ? array_sum($gradesArray) / count($gradesArray) : 0;
    
    $gradeStats[] = [
        'subject' => $grade['subject'],
        'average' => $average
    ];
}

// Sort grades by average
usort($gradeStats, function($a, $b) {
    return $b['average'] - $a['average'];
});

$topGrades = array_slice($gradeStats, 0, 3);
$lowestGrades = array_slice($gradeStats, -3);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .modal { 
            display: none; 
            z-index: 50; 
        }
        .modal-overlay { 
            position: fixed; 
            inset: 0; 
            background: rgba(0, 0, 0, 0.5); 
        }
        @media (max-width: 640px) {
            .fc-toolbar {
                flex-direction: column;
                gap: 1rem;
            }
            .fc-toolbar-chunk {
                display: flex;
                justify-content: center;
            }
        }
    </style>
</head>
<body class="bg-gray-100">

<div class="p-4 sm:p-6 w-full">
    <div class="max-w-6xl mx-auto">
        <!-- Profile Section -->
        <div class="mb-6 bg-white shadow-md rounded-xl border border-gray-200 p-4">
            <?php if ($_SESSION['role'] === 'student') { ?>
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 text-blue-500 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M5.121 17.804A13.937 13.937 0 0112 15c2.485 0 4.79.664 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path d="M19.428 15.341A8 8 0 106.57 15.34M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Student Profile
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-gray-700">
                    <p><strong class="font-medium text-gray-900">Name:</strong> <?= htmlspecialchars($_SESSION['first_name'] ?? 'N/A') ?> <?= htmlspecialchars($_SESSION['last_name'] ?? 'N/A') ?></p>
                    <p><strong class="font-medium text-gray-900">Course:</strong> <?= htmlspecialchars($_SESSION['course'] ?? 'N/A') ?></p>
                    <p><strong class="font-medium text-gray-900">Section:</strong> <?= htmlspecialchars($_SESSION['sections'] ?? 'N/A') ?></p>
                </div>
            <?php } ?>
        </div>

        <!-- Grades Section -->
        <div class="mb-6 bg-white shadow-md rounded-xl border border-gray-200 p-4">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-6 h-6 text-green-500 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Academic Performance
            </h2>

            <!-- Grade Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white p-4 rounded-lg shadow">
                    <h4 class="text-md font-medium text-gray-700 mb-3">Top 3 Highest Grades</h4>
                    <canvas id="highestGradesChart"></canvas>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h4 class="text-md font-medium text-gray-700 mb-3">Top 3 Lowest Grades</h4>
                    <canvas id="lowestGradesChart"></canvas>
                </div>
            </div>

            <!-- Grades Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prelim</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Midterm</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Final</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($grades as $grade): 
                            $prelim = floatval($grade['prelim'] ?? 0);
                            $midterm = floatval($grade['midterm'] ?? 0);
                            $final = floatval($grade['final'] ?? 0);
                            $exam = floatval($grade['exam'] ?? 0);
                            
                            $gradesArray = array_filter([$prelim, $midterm, $final, $exam], function($g) { return $g > 0; });
                            $average = !empty($gradesArray) ? array_sum($gradesArray) / count($gradesArray) : 0;
                        ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2"><?= htmlspecialchars($grade['subject']) ?></td>
                                <td class="px-4 py-2"><?= $prelim > 0 ? number_format($prelim, 2) : '-' ?></td>
                                <td class="px-4 py-2"><?= $midterm > 0 ? number_format($midterm, 2) : '-' ?></td>
                                <td class="px-4 py-2"><?= $final > 0 ? number_format($final, 2) : '-' ?></td>
                                <td class="px-4 py-2"><?= $exam > 0 ? number_format($exam, 2) : '-' ?></td>
                                <td class="px-4 py-2 font-medium"><?= number_format($average, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Schedule Section -->
        <div class="bg-white shadow-lg p-4 sm:p-6 rounded-lg">
            <h2 class="text-lg font-bold mb-4">Schedule Calendar</h2>
            <div id="calendar" class="w-full"></div>
        </div>
    </div>
</div>

<!-- Schedule Modal -->
<div id="scheduleModal" class="modal fixed inset-0 flex items-center justify-center">
    <div class="modal-overlay" onclick="closeModal()"></div>
    <div class="bg-white p-4 sm:p-6 rounded-lg shadow-xl w-11/12 sm:w-1/3 z-50 relative">
        <h3 class="text-lg font-bold mb-4" id="modalTitle"></h3>
        <div class="space-y-2">
            <p><strong>Faculty:</strong> <span id="modalFaculty"></span></p>
            <p><strong>Room:</strong> <span id="modalRoom"></span></p>
            <p><strong>Department:</strong> <span id="modalDepartment"></span></p>
            <p><strong>Course:</strong> <span id="modalCourse"></span></p>
            <p><strong>Section:</strong> <span id="modalSection"></span></p>
            <p><strong>Time:</strong> <span id="modalTime"></span></p>
            <p><strong>Building:</strong> <span id="modalBuilding"></span></p>
        </div>

        <div class="mt-6 flex justify-end">
            <button onclick="closeModal()" class="px-4 py-2 bg-red-500 text-white rounded shadow-lg hover:bg-red-600">Close</button>
        </div>
    </div>
</div>

<script>
    const schedules = <?= json_encode($schedules) ?>;
    console.log("Schedules for calendar:", schedules); // Debug log

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
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            },
            eventClick: function (info) {
                const props = info.event.extendedProps;
                if (!props || !info.event.title) {
                    console.log("No data for event:", info.event); // Debug log
                    return;
                }

                // Update modal content
                document.getElementById("modalTitle").innerText = info.event.title;
                document.getElementById("modalFaculty").innerText = props.faculty || 'N/A';
                document.getElementById("modalRoom").innerText = props.room || 'N/A';
                document.getElementById("modalDepartment").innerText = props.department || 'N/A';
                document.getElementById("modalCourse").innerText = props.course || 'N/A';
                document.getElementById("modalSection").innerText = props.section || 'N/A';
                document.getElementById("modalTime").innerText = (props.time_from && props.time_to) ? 
                    `${props.time_from} - ${props.time_to}` : 'N/A';
                document.getElementById("modalBuilding").innerText = props.building || 'N/A';

                // Show modal
                document.getElementById("scheduleModal").style.display = "flex";
            },
            height: 'auto',
            contentHeight: 'auto',
            eventDidMount: function(info) {
                // Add tooltip with occurrence information
                const props = info.event.extendedProps;
                if (props && props.occurrence && props.total_occurrences) {
                    info.el.title = `Meeting ${props.occurrence} of ${props.total_occurrences}`;
                }
            }
        });
        calendar.render();
    });

    function closeModal() {
        document.getElementById("scheduleModal").style.display = "none";
    }

    // Initialize grade charts
    document.addEventListener("DOMContentLoaded", function() {
        // Highest Grades Chart
        const highestCtx = document.getElementById('highestGradesChart').getContext('2d');
        new Chart(highestCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($topGrades, 'subject')) ?>,
                datasets: [{
                    label: 'Average Grade',
                    data: <?= json_encode(array_column($topGrades, 'average')) ?>,
                    backgroundColor: 'rgba(34, 197, 94, 0.5)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });

        // Lowest Grades Chart
        const lowestCtx = document.getElementById('lowestGradesChart').getContext('2d');
        new Chart(lowestCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($lowestGrades, 'subject')) ?>,
                datasets: [{
                    label: 'Average Grade',
                    data: <?= json_encode(array_column($lowestGrades, 'average')) ?>,
                    backgroundColor: 'rgba(239, 68, 68, 0.5)',
                    borderColor: 'rgb(239, 68, 68)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    });
</script>

</body>
</html>

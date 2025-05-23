<?php  
session_start();

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Controllers/SubjectController.php';
require_once __DIR__ . '/../../Models/Notification.php';
require_once __DIR__ . '/../../Controllers/ScheduleController.php';

use App\Controllers\SubjectController;
use App\Models\Notification;
use App\Config\Database;
use App\Controllers\ScheduleController;

if (!isset($_SESSION['faculty_name'])) {
    header('Location: /login.php');
    exit();
}

$facultyName = $_SESSION['faculty_name'];
$facultyId = $_SESSION['faculty_id'] ?? null;

// Get subjects data
$subjectController = new SubjectController();
$subjects = $subjectController->getSubjectsByFacultyId($facultyId);

// Get notifications
$notificationModel = new Notification();
$notifications = $notificationModel->getNotificationsForFaculty($facultyName);
$unreadCount = $notificationModel->getUnreadCount($facultyName);

// Get today's schedule
$todaySchedule = $subjectController->getTodaySchedule($facultyId);

// Get total students from my-student list
$db = new Database();
$conn = $db->connect();
$stmt = $conn->prepare("SELECT COUNT(DISTINCT id) as total FROM users WHERE faculty = ?");
$stmt->execute([$facultyName]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalStudents = $result['total'];

// Get schedule data
$scheduleController = new ScheduleController();
$schedules = $scheduleController->getSchedulesForUser($facultyName, null, null);

// Calculate today's classes from $schedules
$today = date('Y-m-d');
$todayClassesCount = 0;
foreach ($schedules as $event) {
    if (isset($event['start']) && $event['start'] === $today) {
        $todayClassesCount++;
    }
}

include './layout/sidebar.php';
?>

<div class="min-h-screen bg-gray-50">
    <div class="p-8">
        <div class="max-w-7xl mx-auto">
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 rounded-2xl shadow-xl p-8 mb-8 transform hover:scale-[1.02] transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div class="text-white">
                        <h1 class="text-4xl font-bold mb-3">Welcome back, <?php echo htmlspecialchars($facultyName); ?>! 👋</h1>
                        <p class="text-indigo-100 text-lg">Here's your teaching overview for today</p>
                    </div>
                    <div class="hidden md:block">
                        <div class="w-32 h-32 bg-white/10 rounded-full flex items-center justify-center">
                            <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Students Card -->
                <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-4 bg-emerald-50 rounded-xl">
                            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-500 text-sm font-medium">Total Students</h2>
                            <p class="text-3xl font-bold text-gray-800 mt-1"><?php echo $totalStudents; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Today's Classes Card -->
                <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-4 bg-violet-50 rounded-xl">
                            <svg class="w-8 h-8 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-500 text-sm font-medium">Today's Classes</h2>
                            <p class="text-3xl font-bold text-gray-800 mt-1"><?php echo $todayClassesCount; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Notifications Card -->
                <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-4 bg-amber-50 rounded-xl">
                            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-500 text-sm font-medium">New Notifications</h2>
                            <p class="text-3xl font-bold text-gray-800 mt-1"><?php echo $unreadCount; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="w-full max-w-4xl mx-auto flex flex-col gap-8">
                <!-- Left Column: Calendar + Notifications -->
                <div class="flex flex-col gap-8 w-full">
                    <!-- Calendar Section -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden w-full">
                        <div class="p-8 border-b border-gray-100">
                            <h2 class="text-xl font-bold text-gray-800">Schedule Calendar</h2>
                        </div>
                        <div class="p-8">
                            <div id="calendar" class="calendar-container"></div>
                        </div>
                    </div>
                    <!-- Notifications Section (now below calendar) -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                            <h2 class="text-xl font-bold text-gray-800">Recent Notifications</h2>
                            <a href="notifications.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center">
                                View All
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <?php if (empty($notifications)): ?>
                                    <div class="text-center py-8">
                                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m14 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                            </svg>
                                        </div>
                                        <p class="text-gray-500">No new notifications</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach (array_slice($notifications, 0, 3) as $notification): ?>
                                        <div class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition-colors duration-200">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0">
                                                    <div class="w-2 h-2 mt-2 rounded-full <?php echo $notification['is_read'] ? 'bg-gray-400' : 'bg-indigo-500'; ?>"></div>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-gray-800"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                    <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($notification['created_at']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Right Column: (empty or for future use) -->
            </div>
        </div>
    </div>
</div>

<!-- Schedule Modal -->
<style>
    .custom-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
        z-index: 50;
        transition: all 0.3s ease;
    }
    .custom-modal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #fff;
        border-radius: 1.5rem;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        padding: 2rem;
        min-width: 400px;
        max-width: 90vw;
        z-index: 100;
        opacity: 0;
        pointer-events: none;
        transition: all 0.3s ease;
    }
    .custom-modal.active {
        opacity: 1;
        pointer-events: auto;
    }
    .custom-modal-fade {
        animation: modalFadeIn 0.3s ease;
    }
    @keyframes modalFadeIn {
        from { opacity: 0; transform: translate(-50%, -60%); }
        to { opacity: 1; transform: translate(-50%, -50%); }
    }
    body.modal-open {
        overflow: hidden;
    }
</style>

<div id="customModalOverlay" class="custom-modal-overlay hidden"></div>
<div id="customModal" class="custom-modal">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-800" id="modalTitle"></h3>
        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    <div class="space-y-4">
        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
            <svg class="w-5 h-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <div>
                <p class="text-sm text-gray-500">Room</p>
                <p class="font-medium" id="modalRoom"></p>
            </div>
        </div>
        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
            <svg class="w-5 h-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <div>
                <p class="text-sm text-gray-500">Department</p>
                <p class="font-medium" id="modalDepartment"></p>
            </div>
        </div>
        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
            <svg class="w-5 h-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <div>
                <p class="text-sm text-gray-500">Course</p>
                <p class="font-medium" id="modalCourse"></p>
            </div>
        </div>
        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
            <svg class="w-5 h-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <div>
                <p class="text-sm text-gray-500">Section</p>
                <p class="font-medium" id="modalSection"></p>
            </div>
        </div>
        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
            <svg class="w-5 h-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <p class="text-sm text-gray-500">Time</p>
                <p class="font-medium" id="modalTime"></p>
            </div>
        </div>
        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
            <svg class="w-5 h-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <div>
                <p class="text-sm text-gray-500">Building</p>
                <p class="font-medium" id="modalBuilding"></p>
            </div>
        </div>
    </div>
</div>

<!-- Add FullCalendar CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<script>
function openModal() {
    document.getElementById('customModalOverlay').classList.remove('hidden');
    document.getElementById('customModal').classList.add('active', 'custom-modal-fade');
    document.body.classList.add('modal-open');
}
function closeModal() {
    document.getElementById('customModalOverlay').classList.add('hidden');
    document.getElementById('customModal').classList.remove('active', 'custom-modal-fade');
    document.body.classList.remove('modal-open');
}
document.getElementById('customModalOverlay').onclick = closeModal;

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: <?php echo json_encode($schedules); ?>,
        eventClick: function(info) {
            const event = info.event;
            const props = event.extendedProps;
            document.getElementById('modalTitle').innerText = event.title;
            document.getElementById('modalRoom').innerText = props.room || 'N/A';
            document.getElementById('modalDepartment').innerText = props.department || 'N/A';
            document.getElementById('modalCourse').innerText = props.course || 'N/A';
            document.getElementById('modalSection').innerText = props.section || 'N/A';
            document.getElementById('modalTime').innerText = `${props.time_from || 'N/A'} - ${props.time_to || 'N/A'}`;
            document.getElementById('modalBuilding').innerText = props.building || 'N/A';
            openModal();
        },
        height: 'auto',
        contentHeight: 'auto'
    });
    calendar.render();
});
</script>
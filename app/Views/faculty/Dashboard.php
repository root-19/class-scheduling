<?php  
session_start();

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Controllers/SubjectController.php';
require_once __DIR__ . '/../../Models/Notification.php';

use App\Controllers\SubjectController;
use App\Models\Notification;
use App\Config\Database;

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

include './layout/sidebar.php';
?>

<div class="mr-60 p-8">
    <div class="max-w-7xl mx-auto">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="text-white">
                    <h1 class="text-3xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars($facultyName); ?>!</h1>
                    <p class="text-blue-100">Here's what's happening with your classes today</p>
                </div>
                <div class="hidden md:block">
                    <!-- <img src="/assets/images/teacher-illustration.svg" alt="Teacher" class="h-32 w-32"> -->
                </div>
            </div>
        </div>

        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Classes -->
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-gray-600 text-sm">Total Classes</h2>
                        <p class="text-2xl font-bold text-gray-800"><?php echo count($subjects); ?></p>
                    </div>
                </div>
            </div>

            <!-- Students -->
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-gray-600 text-sm">Total Students</h2>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $totalStudents; ?></p>
                    </div>
                </div>
            </div>

            <!-- Today's Classes -->
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-gray-600 text-sm">Today's Classes</h2>
                        <p class="text-2xl font-bold text-gray-800"><?php echo count($todaySchedule); ?></p>
                    </div>
                </div>
            </div>

            <!-- Notifications -->
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-gray-600 text-sm">New Notifications</h2>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $unreadCount; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Today's Schedule -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Today's Schedule</h2>
                    <div class="space-y-4">
                        <?php if (empty($todaySchedule)): ?>
                            <p class="text-gray-500 text-center py-4">No classes scheduled for today</p>
                        <?php else: ?>
                            <?php foreach ($todaySchedule as $schedule): ?>
                                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0 w-16 text-center">
                                        <p class="text-sm font-semibold text-gray-600"><?php echo htmlspecialchars($schedule['start_time']); ?></p>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-gray-800 font-semibold"><?php echo htmlspecialchars($schedule['subject_name']); ?></h3>
                                        <p class="text-gray-600 text-sm">Room <?php echo htmlspecialchars($schedule['room']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Notifications -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-800">Recent Notifications</h2>
                        <a href="notifications.php" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
                    </div>
                    <div class="space-y-4">
                        <?php if (empty($notifications)): ?>
                            <p class="text-gray-500 text-center py-4">No new notifications</p>
                        <?php else: ?>
                            <?php foreach (array_slice($notifications, 0, 3) as $notification): ?>
                                <div class="border-l-4 <?php echo $notification['is_read'] ? 'border-gray-500' : 'border-blue-500'; ?> pl-4 py-2">
                                    <p class="text-gray-800"><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($notification['created_at']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
       
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any dashboard-specific JavaScript functionality here
    console.log('Dashboard loaded');
});
</script>
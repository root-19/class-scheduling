<?php
session_start();

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Controllers/SubjectController.php';
require_once __DIR__ . '/../../Controllers/StudentController.php';
require_once __DIR__ . '/../../Controllers/FacultyController.php';
require_once __DIR__ . '/../../Controllers/CourseController.php';
require_once __DIR__ . '/../../Controllers/ScheduleController.php';

use App\Controllers\SubjectController;
use App\Controllers\StudentController;
use App\Controllers\FacultyController;
use App\Controllers\CourseController;
use App\Controllers\ScheduleController;

$subjectController = new SubjectController();
$studentController = new StudentController();
$facultyController = new FacultyController();
$courseController = new CourseController();
$scheduleController = new ScheduleController();

$subjectController->handleRequest();
$subjects = $subjectController->getSubjects();

// Get total counts
$totalStudents = $studentController->getTotalStudents();
$totalFaculty = $facultyController->getTotalFaculty();
$totalSubjects = $subjectController->getTotalSubjects();
$totalCourses = $courseController->getTotalCourses();
$totalSchedules = $scheduleController->getTotalSchedules();

$admin = $_SESSION['first_name'] ?? null;
if (!$admin) {
    header('location /login.php');
    exit();
}

include './layout/sidebar.php';
?>

<div class="p-8 w-full bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-gray-800">Dashboard</h1>

                <!-- Welcome Section -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="text-white">
                    <h1 class="text-3xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars($admin); ?>!</h1>
                    <p class="text-blue-100">Here's what's happening with your classes today</p>
                </div>
                <div class="hidden md:block">
                    <!-- <img src="/assets/images/teacher-illustration.svg" alt="Teacher" class="h-32 w-32"> -->
                </div>
            </div>
        </div>

        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Total Students Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:scale-[1.02] hover:shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Students</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-2 animate-fade-in"><?= $totalStudents ?></h3>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full animate-pulse">
                        <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
                    </div>
                </div>
            </div>

            <!-- Total Faculty Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:scale-[1.02] hover:shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Faculty</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-2 animate-fade-in"><?= $totalFaculty ?></h3>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full animate-pulse">
                        <i data-lucide="user-check" class="w-6 h-6 text-green-600"></i>
                    </div>
                </div>
            </div>

            <!-- Total Subjects Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:scale-[1.02] hover:shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Subjects</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-2 animate-fade-in"><?= $totalSubjects ?></h3>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full animate-pulse">
                        <i data-lucide="book" class="w-6 h-6 text-purple-600"></i>
                    </div>
                </div>
            </div>

            <!-- Total Courses Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:scale-[1.02] hover:shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Courses</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-2 animate-fade-in"><?= $totalCourses ?></h3>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full animate-pulse">
                        <i data-lucide="layers" class="w-6 h-6 text-yellow-600"></i>
                    </div>
                </div>
            </div>

            <!-- Total Schedules Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:scale-[1.02] hover:shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Schedules</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-2 animate-fade-in"><?= $totalSchedules ?></h3>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full animate-pulse">
                        <i data-lucide="calendar" class="w-6 h-6 text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>


        <!-- Student Count Graph Section -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8 transform transition-all duration-300 hover:scale-[1.02]">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Student Enrollment Trends</h2>
            <div class="h-80">
                <canvas id="studentChart"></canvas>
            </div>
        </div>

    </div>
</div>

<!-- Add Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sample data - Replace with actual data from your backend
    const ctx = document.getElementById('studentChart').getContext('2d');
    const studentChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Student Enrollment',
                data: [65, 78, 90, 85, 95, 100],
                fill: true,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Monthly Student Enrollment'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });
});
</script>

<style>
@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

.animate-fade-in {
    animation: fade-in 1s ease-in;
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}
</style>

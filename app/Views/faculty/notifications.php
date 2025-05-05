<?php
require_once __DIR__ . '/../../Models/Notification.php';
use App\Models\Notification;

session_start();

if (!isset($_SESSION['faculty_name'])) {
    header('Location: /login.php');
    exit();
}

$notificationModel = new Notification();
$notifications = $notificationModel->getNotificationsForFaculty($_SESSION['faculty_name']);
$unreadCount = $notificationModel->getUnreadCount($_SESSION['faculty_name']);

// Handle marking notifications as read
if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
    $notificationModel->markAsRead($_POST['notification_id']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

include './layout/sidebar.php';
?>

<div class="mr-60 p-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Notifications</h1>
            <p class="text-gray-600">Stay updated with your latest alerts and messages</p>
        </div>

        <!-- Notification Filters -->
        <div class="flex gap-4 mb-6">
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">All</button>
            <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Unread</button>
            <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Read</button>
        </div>

        <!-- Notifications List -->
        <div class="space-y-4">
            <!-- Unread Notification -->
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 hover:shadow-md transition-shadow">
                <div class="flex items-start gap-4">
                    <div class="h-3 w-3 mt-2">
                        <div class="h-3 w-3 bg-blue-600 rounded-full"></div>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <h3 class="font-semibold text-gray-800">New Schedule Update</h3>
                            <span class="text-sm text-gray-500">2 hours ago</span>
                        </div>
                        <p class="text-gray-600 mt-1">Your class schedule for CS101 has been updated for next week.</p>
                        <div class="mt-2">
                            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">Mark as read</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Read Notification -->
            <div class="bg-white p-4 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-start gap-4">
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <h3 class="font-semibold text-gray-800">Student Request</h3>
                            <span class="text-sm text-gray-500">1 day ago</span>
                        </div>
                        <p class="text-gray-600 mt-1">A student has requested a consultation meeting.</p>
                        <div class="mt-2">
                            <button class="text-gray-600 hover:text-gray-800 text-sm font-medium">Remove</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Notification -->
            <div class="bg-white p-4 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-start gap-4">
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <h3 class="font-semibold text-gray-800">System Maintenance</h3>
                            <span class="text-sm text-gray-500">2 days ago</span>
                        </div>
                        <p class="text-gray-600 mt-1">The system will undergo maintenance this weekend. Please save all your work.</p>
                        <div class="mt-2">
                            <button class="text-gray-600 hover:text-gray-800 text-sm font-medium">Remove</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Load More Button -->
        <div class="mt-6 text-center">
            <button class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                Load More
            </button>
        </div>
    </div>
</div>

<script>
    // Add any JavaScript functionality here if needed
    document.addEventListener('DOMContentLoaded', function() {
        // Example: Mark as read functionality
        const markAsReadButtons = document.querySelectorAll('button');
        markAsReadButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Add your mark as read logic here
                console.log('Button clicked');
            });
        });
    });
</script> 
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

<div class="flex justify-center mr-80">
    <div class="p-8 w-full max-w-6xl">
        <div class="mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Notifications</h1>
                <p class="text-gray-600 text-lg leading-relaxed">Stay updated with your latest alerts and messages</p>
            </div>

            <!-- Notification Filters -->
            <div class="flex gap-4 mb-6">
                <button class="px-5 py-2 text-lg bg-blue-600 text-white rounded-lg hover:bg-blue-700">All</button>
            </div>

            <!-- Notifications List -->
            <div class="space-y-4 text-base leading-relaxed">
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-8">
                        <p class="text-gray-600 text-lg">No notifications found</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="<?php echo $notification['is_read'] ? 'bg-white' : 'bg-blue-50'; ?> p-6 rounded-lg border <?php echo $notification['is_read'] ? 'border-gray-200' : 'border-blue-100'; ?> hover:shadow-md transition-shadow">
                            <div class="flex items-start gap-4">
                                <?php if (!$notification['is_read']): ?>
                                    <div class="h-3 w-3 mt-2">
                                        <div class="h-3 w-3 bg-blue-600 rounded-full"></div>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <h3 class="font-semibold text-gray-800 text-lg"><?php echo htmlspecialchars($notification['type']); ?></h3>
                                        <span class="text-sm text-gray-500"><?php echo htmlspecialchars($notification['created_at']); ?></span>
                                    </div>
                                    <p class="text-gray-700 mt-2"><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <div class="mt-3">
                                        <?php if (!$notification['is_read']): ?>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                <button type="submit" name="mark_read" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Mark as read</button>
                                            </form>
                                        <?php else: ?>
                                            <button class="text-gray-600 hover:text-gray-800 text-sm font-medium">Remove</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Load More Button -->
            <?php if (count($notifications) >= 10): ?>
                <div class="mt-6 text-center">
                    <button class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Load More
                    </button>
                </div>
            <?php endif; ?>
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
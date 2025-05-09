<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Scheduling</title>
    <meta name="theme-color" content="#3b82f6">
    <meta name="description" content="Your ultimate scheduling platform for managing appointments, tasks, and events efficiently">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/app/Resources/image/icons/icon-192x192.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-white min-h-screen flex flex-col items-center">
    
    <!-- Student Install Prompt -->
    <div id="studentPrompt" class="hidden fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t border-gray-200 p-4 z-50">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-graduation-cap text-2xl text-blue-500"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">For Students</h3>
                        <p class="text-gray-600">Install our app for quick access to your schedule and classes</p>
                    </div>
                </div>
                <div class="flex space-x-4">
                    <button onclick="dismissInstallPrompt()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        Not now
                    </button>
                    <button onclick="installPWA()" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors flex items-center">
                        <i class="fas fa-download mr-2"></i>
                        Install App
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Regular Install Prompt (hidden by default) -->
    <div id="installPrompt" class="hidden fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t border-gray-200 p-4 z-50">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center">
                <img src="./app/Resources/image/logo/scheduling-logo.png" alt="Logo" class="w-12 h-12 mr-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Install Scheduling System</h3>
                    <p class="text-gray-600">Install our app for a better experience</p>
                </div>
            </div>
            <div class="flex space-x-4">
                <button onclick="dismissInstallPrompt()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    Not now
                </button>
                <button onclick="installPWA()" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    Install
                </button>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="w-full bg-white shadow-sm py-4 px-8 flex items-center justify-between fixed top-0 z-50">
        <div class="flex items-center">
            <img src="./app/Resources/image/logo/scheduling-logo.png" alt="Logo" class="w-14 h-14 mr-3">
            <h2 class="text-2xl font-bold text-gray-800">Scheduling System</h2>
        </div>
        <!-- <nav class="hidden md:flex space-x-6">
            <a href="#" class="text-gray-600">Home</a>
            <a href="#" class="text-gray-600">Features</a>
            <a href="#" class="text-gray-600">Contact</a>
        </nav> -->
    </header>
    
    <!-- Main Content -->
    <main class="flex flex-col items-center justify-center flex-grow text-center px-4 mt-24">
        <div class="max-w-4xl">
            <h1 class="text-5xl font-bold text-gray-800 mb-6">
                Welcome to Scheduling
            </h1>
            <p class="text-gray-600 text-lg mt-3 max-w-2xl leading-relaxed">
                Your <span class="font-semibold">ultimate scheduling platform</span> for managing appointments, tasks, and events efficiently. 
                Stay <span class="font-semibold">organized, productive, and stress-free</span> with our easy-to-use system.
            </p>

            <div class="grid md:grid-cols-2 gap-6 mt-12 ">
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                    <i class="fas fa-calendar-check text-3xl text-blue-500 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Easy Appointment Management</h3>
                    <p class="text-gray-600">Streamline your scheduling process with our intuitive interface</p>
                </div>
                
                <!-- <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                    <i class="fas fa-bell text-3xl text-blue-500 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Smart Notifications</h3>
                    <p class="text-gray-600">Never miss an appointment with automated reminders</p>
                </div> -->
                
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                    <i class="fas fa-chart-line text-3xl text-blue-500 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Analytics Dashboard</h3>
                    <p class="text-gray-600">Track and optimize your scheduling efficiency</p>
                </div>
            </div>
            
            <a href="./app/Views/login.php" 
               class="mt-12 inline-flex items-center px-8 py-3 text-lg font-semibold text-white bg-blue-500 rounded-lg">
                Get Started
                <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="w-full bg-gray-50 text-center py-6 text-gray-600 text-sm border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-center space-x-6 mb-4">
                <a href="#" class="text-gray-500"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-gray-500"><i class="fab fa-facebook"></i></a>
                <a href="#" class="text-gray-500"><i class="fab fa-linkedin"></i></a>
            </div>
            <p>© 2024 Scheduling System | All Rights Reserved</p>
        </div>
    </footer>

    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            let deferredPrompt;
            const installPrompt = document.getElementById('installPrompt');
            const studentPrompt = document.getElementById('studentPrompt');

            // Function to show the prompt with animation
            function showPrompt() {
                if (studentPrompt) {
                    studentPrompt.classList.remove('hidden');
                    studentPrompt.style.opacity = '0';
                    studentPrompt.style.transform = 'translateY(100%)';
                    studentPrompt.style.transition = 'all 0.5s ease-out';
                    
                    // Trigger animation
                    setTimeout(() => {
                        studentPrompt.style.opacity = '1';
                        studentPrompt.style.transform = 'translateY(0)';
                    }, 100);
                }
            }

            // Function to hide the prompt with animation
            function hidePrompt() {
                if (studentPrompt) {
                    studentPrompt.style.opacity = '0';
                    studentPrompt.style.transform = 'translateY(100%)';
                    setTimeout(() => {
                        studentPrompt.classList.add('hidden');
                    }, 500);
                }
            }

            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                
                // Show prompt after 5 seconds
                setTimeout(() => {
                    showPrompt();
                }, 5000);
            });

            window.addEventListener('appinstalled', () => {
                hidePrompt();
                deferredPrompt = null;
            });

            window.installPWA = function() {
                if (!deferredPrompt) return;
                
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    } else {
                        console.log('User dismissed the install prompt');
                    }
                    deferredPrompt = null;
                    hidePrompt();
                });
            };

            window.dismissInstallPrompt = function() {
                hidePrompt();
            };

            // Register service worker
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then(registration => {
                            console.log('ServiceWorker registration successful');
                        })
                        .catch(err => {
                            console.log('ServiceWorker registration failed: ', err);
                        });
                });
            }
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Scheduling</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-white min-h-screen flex flex-col items-center">
    
    <!-- Header -->
    <header class="w-full bg-white shadow-sm py-4 px-8 flex items-center justify-between fixed top-0 z-50">
        <div class="flex items-center">
            <img src="logo.png" alt="Logo" class="w-14 h-14 mr-3">
            <h2 class="text-2xl font-bold text-gray-800">Scheduling System</h2>
        </div>
        <nav class="hidden md:flex space-x-6">
            <a href="#" class="text-gray-600">Home</a>
            <a href="#" class="text-gray-600">Features</a>
            <a href="#" class="text-gray-600">Contact</a>
        </nav>
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

            <div class="grid md:grid-cols-3 gap-6 mt-12">
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                    <i class="fas fa-calendar-check text-3xl text-blue-500 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Easy Appointment Management</h3>
                    <p class="text-gray-600">Streamline your scheduling process with our intuitive interface</p>
                </div>
                
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                    <i class="fas fa-bell text-3xl text-blue-500 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Smart Notifications</h3>
                    <p class="text-gray-600">Never miss an appointment with automated reminders</p>
                </div>
                
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

</body>
</html>
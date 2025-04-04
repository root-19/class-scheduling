<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Scheduling</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 min-h-screen flex flex-col items-center">
    
    <!-- Header -->
    <header class="w-full bg-white/90 backdrop-blur-md shadow-md py-4 px-8 flex items-center justify-between fixed top-0 z-50">
        <div class="flex items-center">
            <img src="logo.png" alt="Logo" class="w-14 h-14 mr-3">
            <h2 class="text-2xl font-bold text-gray-800">Scheduling System</h2>
        </div>
        <!-- <nav class="hidden md:flex">
            <a href="#" class="text-gray-700 hover:text-blue-500 px-4 font-medium">Home</a>
            <a href="#" class="text-gray-700 hover:text-blue-500 px-4 font-medium">About</a>
            <a href="#" class="text-gray-700 hover:text-blue-500 px-4 font-medium">Contact</a>
        </nav> -->
    </header>
    
    <!-- Main Content -->
    <main class="flex flex-col items-center justify-center flex-grow text-center px-4 mt-24">
        <h1 class="text-5xl font-extrabold text-white drop-shadow-lg">Welcome to Scheduling</h1>
        <p class="text-white text-lg mt-3 max-w-2xl">Your <span class="font-bold">ultimate scheduling platform</span> for managing appointments, tasks, and events efficiently. Stay <span class="font-bold">organized, productive, and stress-free</span> with our easy-to-use system.</p>

        <div class="bg-white/80 backdrop-blur-lg p-8 mt-6 rounded-2xl shadow-xl w-full max-w-lg text-gray-800">
            <h2 class="text-2xl font-semibold">Why Choose Our Scheduling System?</h2>
            <ul class="mt-4 text-left text-lg list-disc list-inside space-y-2">
                <li> <span class="font-medium">Easy Appointment Management</span></li>
                <!-- <li>✅ <span class="font-medium">Automated Reminders & Notifications</span></li> -->
                <li><span class="font-medium">User-Friendly Dashboard</span></li>
                <li><span class="font-medium">Seamless Workflow Integration</span></li>
            </ul>
        </div>
        
        <a href="./app/Views/login.php" class="mt-8 bg-white text-blue-600 text-lg font-semibold px-8 py-3 rounded-full shadow-lg hover:bg-blue-600 hover:text-white transition-transform transform hover:scale-105">
            Get Started
        </a>
    </main>
    
    <!-- Footer -->
    <footer class="w-full bg-white/90 text-center py-4 text-gray-800 text-sm shadow-md">
        © 2024 Scheduling System | All Rights Reserved
    </footer>

</body>
</html>
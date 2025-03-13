<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Scheduling</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

    <!-- Header -->
    <header class="w-full bg-white shadow-md py-4 px-8 flex items-center justify-between">
        <div class="flex items-center">
            <img src="logo.png" alt="Logo" class="w-14 h-14 mr-3">
            <h2 class="text-2xl font-semibold text-gray-700">Scheduling System</h2>
        </div>
        <nav>
            <a href="#" class="text-gray-600 hover:text-blue-500 px-4">Home</a>
            <a href="#" class="text-gray-600 hover:text-blue-500 px-4">About</a>
            <a href="#" class="text-gray-600 hover:text-blue-500 px-4">Contact</a>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="flex flex-col items-center justify-center flex-grow text-center px-4">
        <h1 class="text-4xl font-bold text-gray-800">Welcome to Scheduling</h1>
        <p class="text-gray-600 text-lg mt-3">
            Your **ultimate scheduling platform** for managing appointments, tasks, and events efficiently.  
            Stay **organized, productive, and stress-free** with our easy-to-use system.
        </p>

        <div class="bg-white p-6 mt-6 rounded-lg shadow-md w-full max-w-lg">
            <h2 class="text-xl font-semibold text-gray-700">Why Choose Our Scheduling System?</h2>
            <ul class="text-gray-600 mt-3 text-left list-disc list-inside">
                <li>✅ **Easy Appointment Management**</li>
                <li>✅ **Automated Reminders & Notifications**</li>
                <li>✅ **User-Friendly Dashboard**</li>
                <li>✅ **Seamless Integration with Your Workflow**</li>
            </ul>
        </div>

        <a href="./app/Views/login.php" 
            class="mt-8 inline-block bg-blue-600 text-white text-lg font-semibold px-6 py-3 rounded-lg shadow-lg hover:bg-blue-700 transition">
            Get Started
        </a>
    </main>

    <!-- Footer -->
    <footer class="w-full bg-gray-200 text-center py-4 text-gray-700 text-sm">
        © 2024 Scheduling System | All Rights Reserved
    </footer>

</body>
</html>

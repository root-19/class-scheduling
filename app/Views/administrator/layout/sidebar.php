<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sidebar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../../../Resources/js/modal-faculty.js"></script>
</head>
<body class="bg-gray-100 flex">

    <!-- Sidebar -->
    <div class="w-64 bg-blue-800 text-white min-h-screen p-4 fixed md:relative transform -translate-x-full md:translate-x-0 transition duration-300 ease-in-out" id="sidebar">
        <!-- Logo & Title -->
        <div class="flex items-center space-x-2 mb-6">
            <img src="logo.png" alt="Logo" class="w-10">
            <h1 class="text-xl font-bold">Scheduling</h1>
        </div>

        <!-- Sidebar Menu -->
        <nav>
            <ul class="space-y-2">
                <li class="hover:bg-blue-700 p-2 rounded-lg flex items-center space-x-2 cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18"></path></svg>
                    <span>Home</span>
                </li>
                <li class="hover:bg-blue-700 p-2 rounded-lg flex items-center space-x-2 cursor-pointer">
                <a href="../../Views/administrator/register.php" class="flex items-center space-x-2 w-full text-white">    
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h7"></path></svg>
                    <span>Student List</span>
                </a>
                </li>
                <li class="hover:bg-blue-700 p-2 rounded-lg flex items-center space-x-2 cursor-pointer">
                <a href="../../Views/administrator/schedule.php" class="flex items-center space-x-2 w-full text-white">   
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 6v6l4 2M8 2h8a2 2 0 012 2v16a2 2 0 01-2 2H8a2 2 0 01-2-2V4a2 2 0 012-2z"></path></svg>
                    <span>Schedule</span>
                </a>
                </li>
                <li class="hover:bg-blue-700 p-2 rounded-lg flex items-center space-x-2 cursor-pointer">
                <a href="../../Views/administrator/Dashboard.php" class="flex items-center space-x-2 w-full text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 6h8M8 12h8m-4 6h4"></path></svg>
                    <span>Subject List</span>
                </a>
                </li>
                <li class="hover:bg-blue-700 p-2 rounded-lg flex items-center space-x-2 cursor-pointer">
                <a href="../../Views/administrator/faculty.php" class="flex items-center space-x-2 w-full text-white">     
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 01-8 0m8 6a4 4 0 01-8 0m10 6a4 4 0 01-12 0"></path></svg>
                    <span>Faculty List</span>
                </a>
                </li>
                <li class="hover:bg-blue-700 p-2 rounded-lg flex items-center space-x-2 cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 3h14a2 2 0 012 2v16l-7-4-7 4V5a2 2 0 012-2z"></path></svg>
                    <span>Users</span>
                </li>
                <li class="hover:bg-blue-700 p-2 rounded-lg flex items-center space-x-2 cursor-pointer">
                <a href="../../Views/administrator/course.php" class="flex items-center space-x-2 w-full text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M12 5v14M5 12h14"></path>
        </svg>
        <span>Course List</span>
    </a>
                </li>
                <li class="hover:bg-blue-700 p-2 rounded-lg flex items-center space-x-2 cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg>
                    <span>Rooms</span>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-6 md:ml-10">
        <button class="md:hidden bg-blue-600 text-white p-2 rounded-md mb-4" id="toggleSidebar">
            ☰ Menu
        </button>
       
    </div>

    <script>
        const sidebar = document.getElementById("sidebar");
        const toggleBtn = document.getElementById("toggleSidebar");

        toggleBtn.addEventListener("click", () => {
            sidebar.classList.toggle("-translate-x-full");
        });
    </script>
</body>
</html>

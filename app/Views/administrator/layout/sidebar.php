<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard | Sidebar</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100 flex">

  <!-- Sidebar -->
  <div id="sidebar" class="w-64 bg-blue-800 text-white min-h-screen p-4 fixed md:relative transform -translate-x-full md:translate-x-0 transition duration-300 ease-in-out">
    <!-- Logo & Title -->
    <div class="flex items-center space-x-3 mb-8">
      <!-- <img src="../../../Resources/image/logo/scheduling-logo.png" alt="Logo" class="w-10 h-10 rounded-full"> -->
      <h1 class="text-2xl font-bold">Scheduling</h1>
    </div>

    <!-- Sidebar Menu -->
    <nav>
      <ul class="space-y-3 text-sm font-medium">
        <li class="hover:bg-blue-700 p-3 rounded-lg flex items-center space-x-3 cursor-pointer">
        <a href="../../Views/administrator/Dashboard.php" class="hover:bg-blue-700 p-3 rounded-lg flex items-center space-x-3">
            <i data-lucide="list" class="w-5 h-5"></i>
            <span>Dashboard</span>
          </a>
        <li>
          <a href="../../Views/administrator/register.php" class="hover:bg-blue-700 p-3 rounded-lg flex items-center space-x-3">
            <i data-lucide="list" class="w-5 h-5"></i>
            <span>Student List</span>
          </a>
        </li>
        <li class="relative">
          <div onclick="toggleDropdown()" class="hover:bg-blue-700 p-3 rounded-lg flex items-center space-x-3 cursor-pointer">
            <i data-lucide="calendar" class="w-5 h-5"></i>
            <span>Schedule</span>
          </div>
          <ul id="scheduleDropdown" class="absolute left-0 mt-1 bg-blue-700 text-white shadow-lg rounded-lg hidden w-48">
            <li>
              <a href="../../Views/administrator/add_schedule.php" class="block px-4 py-2 hover:bg-blue-600">Add Schedule</a>
            </li>
            <li>
              <a href="../../Views/administrator/schedule.php" class="block px-4 py-2 hover:bg-blue-600">View Schedule</a>
            </li>
          </ul>
        </li>
        <li>
          <a href="../../Views/administrator/subject-list.php" class="hover:bg-blue-700 p-3 rounded-lg flex items-center space-x-3">
            <i data-lucide="book" class="w-5 h-5"></i>
            <span>Subject List</span>
          </a>
        </li>
        <li>
          <a href="../../Views/administrator/faculty.php" class="hover:bg-blue-700 p-3 rounded-lg flex items-center space-x-3">
            <i data-lucide="users" class="w-5 h-5"></i>
            <span>Faculty List</span>
          </a>
        </li>
        <li>
          <a href="../../Views/administrator/student-list.php" class="hover:bg-blue-700 p-3 rounded-lg flex items-center space-x-3">
            <i data-lucide="user-check" class="w-5 h-5"></i>
            <span>Users</span>
          </a>
        </li>
        <li>
          <a href="../../Views/administrator/course.php" class="hover:bg-blue-700 p-3 rounded-lg flex items-center space-x-3">
            <i data-lucide="layers" class="w-5 h-5"></i>
            <span>Course List</span>
          </a>
        </li>
        <li>
          <a href="../../Views/administrator/logout.php" class="hover:bg-red-600 p-3 rounded-lg flex items-center space-x-3">
            <i data-lucide="log-out" class="w-5 h-5"></i>
            <span>Logout</span>
          </a>
        </li>
      </ul>
    </nav>
  </div>

  <!-- Main Content -->
  <div class="flex-1 p-6 md:ml-140">
    <button class="md:hidden bg-blue-600 text-white p-2 rounded-md mb-4" id="toggleSidebar">☰ Menu</button>
  </div>

  <!-- Scripts -->
  <script>
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleSidebar");

    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("-translate-x-full");
    });

    function toggleDropdown() {
      document.getElementById('scheduleDropdown').classList.toggle('hidden');
    }

    lucide.createIcons();
  </script>
</body>
</html>


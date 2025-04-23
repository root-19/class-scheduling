<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex">

  <!-- Sidebar -->
  <div class="w-64 bg-blue-800 text-white min-h-screen p-4 fixed md:relative transform -translate-x-full md:translate-x-0 transition duration-300 ease-in-out" id="sidebar">
    
    <!-- Logo & Title -->
    <div class="flex items-center space-x-2 mb-6">
      <img src="logo.png" alt="Logo" class="w-10" />
      <h1 class="text-xl font-bold">Scheduling</h1>
    </div>

    <!-- Sidebar Menu -->
    <nav>
      <ul class="space-y-2">
        <li>
          <a href="#" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-blue-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M3 6h18M3 18h18" />
            </svg>
            <span>Home</span>
          </a>
        </li>
        <li>
          <a href="../../Views/faculty/my-student.php" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-blue-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
            <span>Student List</span>
          </a>
        </li>
        <li>
          <a href="/app/views/admin/logout/logout.php" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-red-600">
            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1" />
            </svg>
            <span>Logout</span>
          </a>
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

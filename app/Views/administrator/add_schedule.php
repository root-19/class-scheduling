<?php
require_once __DIR__ . '/../../Controllers/ScheduleController.php';
require_once __DIR__ . '/../../Config/Database.php';

use App\Controllers\ScheduleController;
use App\Config\Database; // ✅ Make sure this comes before you use "new Database()"

// Initialize schedule controller
$scheduleController = new ScheduleController();
$schedulesJson = $scheduleController->getSchedules();

// Fetch faculty list
$database = new Database();
$conn = $database->connect();

$facultyList = [];
$query = "SELECT id, name FROM faculty";
$stmt = $conn->prepare($query);
$stmt->execute();
$facultyList = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "./layout/sidebar.php";
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Form</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="p-6 w-full">
    <div class="max-w-4xl mx-auto bg-white shadow-lg p-6 rounded-lg">

        <h2 class="text-lg font-bold mb-4">Add Schedule</h2>
        <form id="scheduleForm" method="POST" class="space-y-2">
        <select name="faculty" required class="w-full p-2 border rounded">
    <option value="" disabled selected>Select Faculty</option>
    <?php foreach ($facultyList as $faculty): ?>
        <option value="<?= htmlspecialchars($faculty['name']) ?>">
            <?= htmlspecialchars($faculty['name']) ?>
        </option>
    <?php endforeach; ?>
    </select>
    <input type="text" name="day_of_week" placeholder="Day of Week" required class="w-full p-2 border rounded">
    <input type="text" name="subject" placeholder="Subject" required class="w-full p-2 border rounded">
    
    <!-- Calendar-based Month From Input -->
    <input type="month" name="month_from" required class="w-full p-2 border rounded">
    
    <!-- Calendar-based Month To Input -->
    <input type="month" name="month_to" required class="w-full p-2 border rounded">

  <!-- Building Dropdown -->
<select id="building" name="building" required class="w-full p-2 border rounded">
    <option value="" disabled selected>Select Building</option>
    <option value="Building 1">Building 1</option>
    <option value="Building 2">Building 2</option>
    <option value="Building 3">Building 3</option>
</select>

<!-- Room Dropdown (Hidden by default) -->
<select id="room" name="room" required class="w-full p-2 border rounded mt-2 hidden">
    <option value="" disabled selected>Select Room</option>
</select>
    <input type="text" name="department" placeholder="Department" required class="w-full p-2 border rounded">
    <label for="time_from">Time From:</label>
<select name="time_from" required class="w-full p-2 border rounded">
  <option value="07:00">7:00 AM</option>
  <option value="10:00">10:00 AM</option>
  <option value="13:00">1:00 PM</option>
  <option value="16:00">4:00 PM</option>
  <option value="19:00">7:00 PM</option>
</select>

<label for="time_to">Time To:</label>
<select name="time_to" required class="w-full p-2 border rounded">
  <option value="10:00">10:00 AM</option>
  <option value="13:00">1:00 PM</option>
  <option value="16:00">4:00 PM</option>
  <option value="19:00">7:00 PM</option>
  <option value="21:00">9:00 PM</option>
</select>

    <input type="text" name="course" placeholder="Course" required class="w-full p-2 border rounded">
    <input type="text" name="section" placeholder="Section" required class="w-full p-2 border rounded">
    <div class="flex justify-end space-x-2">
        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded">Save</button>
    </div>
</form>

    </div>
    </div>

    <!-- Form submission to backend -->
    <script>



        document.querySelector("#scheduleForm").addEventListener("submit", function(event) {
            event.preventDefault();

            let formData = new FormData(this);

            fetch("../../Controllers/ScheduleController.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === "success") {
                    location.reload();
                }
            })
            .catch(error => console.error("Error:", error));
        });
    </script>
    <script>
   const buildingSelect = document.getElementById("building");
  const roomSelect = document.getElementById("room");

  // Room ranges for each building
  const roomMap = {
    "Building 1": [1, 5],
    "Building 2": [6, 9],
    "Building 3": [10, 14]
  };

  buildingSelect.addEventListener("change", function () {
    const selectedBuilding = this.value;
    const [start, end] = roomMap[selectedBuilding] || [];

    // Clear and reset room dropdown
    roomSelect.innerHTML = '<option value="" disabled selected>Select Room</option>';

    // Populate the appropriate room numbers
    for (let i = start; i <= end; i++) {
      const option = document.createElement("option");
      option.value = `Room ${i}`;
      option.textContent = `Room ${i}`;
      roomSelect.appendChild(option);
    }

    // Show the room dropdown
    roomSelect.classList.remove("hidden");
  });
</script>
</body>
</html>

<?php
require_once __DIR__ . '/../../Controllers/ScheduleController.php';
require_once __DIR__ . '/../../Config/Database.php';

use App\Controllers\ScheduleController;
use App\Config\Database; 

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

// Fetch subjects list
$subjectsList = [];


$query = "SELECT id, subject_name FROM subjects";
$stmt = $conn->prepare($query);
$stmt->execute();
$subjectsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch courses list
$coursesList = [];
$query = "SELECT id, course_name, description FROM course";
$stmt = $conn->prepare($query);
$stmt->execute();
$coursesList = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    
    <select name="subject" required class="w-full p-2 border rounded">
        <option value="" disabled selected>Select Subject</option>
        <?php foreach ($subjectsList as $subject): ?>
            <option value="<?= htmlspecialchars($subject['subject_name']) ?>">
                <?= htmlspecialchars($subject['subject_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <!-- Date From Input -->
    <label for="month_from">Date From:</label>
    <input type="date" name="month_from" required class="w-full p-2 border rounded">
    
    <!-- Date To Input -->
    <label for="month_to">Date To:</label>
    <input type="date" name="month_to" required class="w-full p-2 border rounded">

<!-- Building Dropdown -->
<select id="building" name="building" required class="w-full p-2 border rounded">
  <option value="" disabled selected>Select Building</option>
  <option value="Building A">Building A</option>
  <option value="Building B">Building B</option>
  <option value="Building C">Building C</option>
  <option value="Building D">Building D</option>
  <option value="Forest Room">Forest Room</option>
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
<label class="block mb-2 font-medium text-gray-700">Course</label>
<select name="course" required class="w-full px-4 py-2 border rounded-lg focus:ring">
  <option value="">Select Course</option>
  <?php foreach ($coursesList as $course): ?>
    <option value="<?= htmlspecialchars($course['course_name']) ?>">
      <?= htmlspecialchars($course['course_name']) ?> – <?= htmlspecialchars($course['description']) ?>
    </option>
  <?php endforeach; ?>
</select>
    <input type="text" name="section" placeholder="Section" required class="w-full p-2 border rounded">
    <div class="flex justify-end space-x-2">
        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded">Save</button>
    </div>
</form>

    </div>
    </div>

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

  // Room list for each building
  const roomMap = {
    "Building A": [
      "A101", "A102", "A103",
      "Computer laboratory", "Engineering Lab", "Mock lab"
    ],
    "Building B": [
      "B102", "B103", "B104", "B105", "B106", "B107"
    ],
    "Building C": [
      "C-A", "C-B", "C-C", "C-D", "C-E", "C-F", "C-H", "C-I", "C-J", "C-K",
      "C-L", "C-M", "C-N", "C-O", "C-P", "C-Q", "C-R", "C-S", "C-T"
    ],
    "Building D": [
      "D101", "D102", "D103", "D104", "D108",
      "D201", "D202", "D203", "D204", "D205", "D206", "D207", "D208",
      "D301", "D302", "D303", "D304", "D305", "D306", "D307", "D308"
    ],
    "Forest Room": [
      "FR 1", "FR 2", "FR 3", "FR 4", "FR 5"
    ]
  };

  buildingSelect.addEventListener("change", function () {
    const selectedBuilding = this.value;
    const rooms = roomMap[selectedBuilding] || [];

    // Reset room dropdown
    roomSelect.innerHTML = '<option value="" disabled selected>Select Room</option>';

    // Populate rooms
    rooms.forEach(room => {
      const option = document.createElement("option");
      option.value = room;
      option.textContent = room;
      roomSelect.appendChild(option);
    });

    // Show room dropdown
    roomSelect.classList.remove("hidden");
  });
</script>
</body>
</html>

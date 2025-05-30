<?php

session_start();

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Controllers/FacultyController.php';

use App\Config\Database;
use App\Controllers\FacultyController;

$database = new Database();
$conn = $database->connect();
$facultyController = new FacultyController();

// Handle Add Faculty
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_faculty'])) {
    $facultyId = $_POST['faculty_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $subjects = $_POST['subjects'] ?? [];
    $password = $_POST['password']; 
    $confirmPassword = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirmPassword) {
        $error = "Passwords do not match. Please try again.";
    } else if ($facultyController->addFaculty($facultyId, $name, $email, $contact, $address, $subjects, $password)) {
        header("Location: faculty.php");
        exit();
    } else {
        $error = "Failed to add faculty member. Please try again.";
    }
}


// Handle Update Faculty
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_faculty'])) {
    $id = $_POST['id'];
    $facultyId = $_POST['faculty_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $subjects = $_POST['subjects'] ?? [];

    if ($facultyController->updateFaculty($id, $facultyId, $name, $email, $contact, $address, $subjects)) {
        header("Location: faculty.php");
        exit();
    } else {
        $error = "Failed to update faculty member. Please try again.";
    }
}

// Handle Delete Faculty
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM faculty WHERE id=?");
    $stmt->execute([$id]);

    header("Location: faculty.php");
    exit();
}

// Pagination settings
$itemsPerPage = 5;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Count total records for pagination
$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM faculty");
$countStmt->execute();
$totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $itemsPerPage);

// Fetch paginated Faculty List
$stmt = $conn->prepare("SELECT * FROM faculty LIMIT :offset, :limit");
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->execute();
$facultyList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include sidebar
include './layout/sidebar.php';
?>
<script src="../../Resources/js/modal-faculty.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="p-8 w-full bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Header Section -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <h1 class="text-2xl font-bold text-gray-800">Faculty Management</h1>
                    <button onclick="openModal('addFacultyModal')" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add New Faculty
                    </button>
                </div>
            </div>

            <!-- Faculty Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faculty ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($facultyList) > 0): ?>
                            <?php foreach ($facultyList as $faculty): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($faculty['faculty_id']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($faculty['name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($faculty['email']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($faculty['contact']); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($faculty['address']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <button 
                                                onclick="viewPerformance(
                                                    '<?php echo $faculty['id']; ?>', 
                                                    '<?php echo addslashes($faculty['name']); ?>'
                                                )" 
                                                class="inline-flex items-center px-3 py-1.5 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                                Performance
                                            </button>
                                            <button 
                                                onclick="editFaculty(
                                                    <?php echo $faculty['id']; ?>, 
                                                    '<?php echo $faculty['faculty_id']; ?>', 
                                                    '<?php echo addslashes($faculty['name']); ?>', 
                                                    '<?php echo $faculty['email']; ?>', 
                                                    '<?php echo $faculty['contact']; ?>',
                                                    '<?php echo addslashes($faculty['address']); ?>',
                                                    '<?php echo $faculty['subjects']; ?>'
                                                )" 
                                                class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Edit
                                            </button>
                                            <a href="faculty.php?delete=<?php echo $faculty['id']; ?><?php echo isset($_GET['page']) ? '&page='.$_GET['page'] : ''; ?>" 
                                               onclick="return confirm('Are you sure you want to delete this faculty member?')" 
                                               class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No faculty records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="text-sm text-gray-600">
                        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $itemsPerPage, $totalRecords); ?> of <?php echo $totalRecords; ?> entries
                    </div>
                    <div class="flex items-center space-x-2">
                        <?php if ($currentPage > 1): ?>
                            <a href="faculty.php?page=<?php echo $currentPage - 1; ?>" 
                                class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                Previous
                            </a>
                        <?php else: ?>
                            <button disabled class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-400 rounded-lg text-sm cursor-not-allowed">
                                Previous
                            </button>
                        <?php endif; ?>
                        
                        <div class="flex items-center space-x-1">
                            <?php 
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $startPage + 4);
                            if ($endPage - $startPage < 4) {
                                $startPage = max(1, $endPage - 4);
                            }
                            
                            for ($i = $startPage; $i <= $endPage; $i++): 
                            ?>
                                <a href="faculty.php?page=<?php echo $i; ?>" 
                                   class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm <?php echo ($i == $currentPage) ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="faculty.php?page=<?php echo $currentPage + 1; ?>" 
                                class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                Next
                            </a>
                        <?php else: ?>
                            <button disabled class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-400 rounded-lg text-sm cursor-not-allowed">
                                Next
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Faculty Modal -->
<div id="addFacultyModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 transition-opacity z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl mx-4 max-h-[90vh] overflow-hidden flex flex-col">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Add New Faculty</h2>
        </div>
        
        <div class="overflow-y-auto flex-grow">
            <form action="faculty.php<?php echo isset($_GET['page']) ? '?page='.$_GET['page'] : ''; ?>" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="add_faculty" value="1">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Faculty ID</label>
                        <input type="text" name="faculty_id" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact</label>
                        <input type="text" name="contact" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input type="text" name="address" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subjects</label>
                        <div id="subjects-container" class="space-y-2 max-h-48 overflow-y-auto p-2 border border-gray-200 rounded-lg">
                            <div class="subject-entry flex gap-2">
                                <select name="subjects[]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Subject</option>
                                    <?php
                                    $subjectStmt = $conn->query("SELECT * FROM subjects");
                                    while ($subject = $subjectStmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='" . htmlspecialchars($subject['id']) . "'>" . htmlspecialchars($subject['subject_name']) . "</option>";
                                    }
                                    ?>
                                </select>
                                <button type="button" onclick="removeSubject(this)" class="px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button type="button" onclick="addSubjectField()" class="mt-2 text-blue-600 hover:text-blue-700 flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Another Subject
                        </button>
                    </div>
                    
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" name="confirm_password" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 mt-4">
                    <button type="button" onclick="closeModal('addFacultyModal')" 
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Add Faculty
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Faculty Modal -->
<div id="editFacultyModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 transition-opacity z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl mx-4 max-h-[90vh] overflow-hidden flex flex-col">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Edit Faculty</h2>
        </div>
        
        <div class="overflow-y-auto flex-grow">
            <form action="faculty.php<?php echo isset($_GET['page']) ? '?page='.$_GET['page'] : ''; ?>" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="edit_faculty" value="1">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Faculty ID</label>
                        <input type="text" id="edit_faculty_id" name="faculty_id" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" id="edit_name" name="name" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="edit_email" name="email" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact</label>
                        <input type="text" id="edit_contact" name="contact" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input type="text" id="edit_address" name="address" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subjects</label>
                        <div id="edit-subjects-container" class="space-y-2 max-h-48 overflow-y-auto p-2 border border-gray-200 rounded-lg">
                            <div class="subject-entry flex gap-2">
                                <select name="subjects[]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Subject</option>
                                    <?php
                                    $subjectStmt = $conn->query("SELECT * FROM subjects");
                                    while ($subject = $subjectStmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='" . htmlspecialchars($subject['id']) . "'>" . htmlspecialchars($subject['subject_name']) . "</option>";
                                    }
                                    ?>
                                </select>
                                <button type="button" onclick="removeSubject(this)" class="px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button type="button" onclick="addEditSubjectField()" class="mt-2 text-blue-600 hover:text-blue-700 flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Another Subject
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 mt-4">
                    <button type="button" onclick="closeModal('editFacultyModal')" 
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Update Faculty
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Performance Modal -->
<div id="performanceModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 transition-opacity z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl mx-4 max-h-[90vh] overflow-hidden flex flex-col">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800">Faculty Performance</h2>
            <button onclick="closeModal('performanceModal')" class="text-gray-400 hover:text-gray-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="overflow-y-auto flex-grow p-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-2" id="facultyName"></h3>
            </div>

            <!-- Performance Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-blue-700 mb-2">Teaching Effectiveness Score</h4>
                    <p class="text-2xl font-bold text-blue-800" id="teachingScore">-</p>
                    <p class="text-xs text-blue-600 mt-1">Based on multiple factors</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-green-700 mb-2">Student Progress</h4>
                    <p class="text-2xl font-bold text-green-800" id="studentProgress">-</p>
                    <p class="text-xs text-green-600 mt-1">Average improvement</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-purple-700 mb-2">Attendance Rate</h4>
                    <p class="text-2xl font-bold text-purple-800" id="attendanceRate">-</p>
                    <p class="text-xs text-purple-600 mt-1">Overall attendance</p>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-orange-700 mb-2">Pass Rate</h4>
                    <p class="text-2xl font-bold text-orange-800" id="passRate">-</p>
                    <p class="text-xs text-orange-600 mt-1">Students passing</p>
                </div>
            </div>

            <!-- Additional Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Total Students</h4>
                    <p class="text-xl font-bold text-gray-800" id="totalStudents">-</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Excellence Rate</h4>
                    <p class="text-xl font-bold text-gray-800" id="studentsAbove85">-</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Average Grade</h4>
                    <p class="text-xl font-bold text-gray-800" id="avgPerformance">-</p>
                </div>
            </div>

            <!-- Performance Charts -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-4 rounded-lg shadow">
                    <h4 class="text-md font-medium text-gray-700 mb-3">Subject Performance Trends</h4>
                    <canvas id="subjectPerformanceChart"></canvas>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h4 class="text-md font-medium text-gray-700 mb-3">Student Progress Distribution</h4>
                    <canvas id="gradeDistributionChart"></canvas>
                </div>
            </div>

            <!-- Subject-wise Performance Table -->
            <div class="mt-6">
                <h4 class="text-md font-medium text-gray-700 mb-3">Detailed Subject Analysis</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prelim Avg</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Midterm Avg</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Final Avg</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Improvement</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pass Rate</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Attendance</th>
                            </tr>
                        </thead>
                        <tbody id="subjectPerformanceTable" class="bg-white divide-y divide-gray-200">
                            <!-- Data will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let subjectPerformanceChart = null;
let gradeDistributionChart = null;

function addSubjectField() {
    const container = document.getElementById('subjects-container');
    const newEntry = document.createElement('div');
    newEntry.className = 'subject-entry flex gap-2';
    newEntry.innerHTML = `
        <select name="subjects[]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select Subject</option>
            <?php
            $subjectStmt = $conn->query("SELECT * FROM subjects");
            while ($subject = $subjectStmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='" . htmlspecialchars($subject['id']) . "'>" . htmlspecialchars($subject['subject_name']) . "</option>";
            }
            ?>
        </select>
        <button type="button" onclick="removeSubject(this)" class="px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    container.appendChild(newEntry);
}

function addEditSubjectField() {
    const container = document.getElementById('edit-subjects-container');
    const newEntry = document.createElement('div');
    newEntry.className = 'subject-entry flex gap-2';
    newEntry.innerHTML = `
        <select name="subjects[]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select Subject</option>
            <?php
            $subjectStmt = $conn->query("SELECT * FROM subjects");
            while ($subject = $subjectStmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='" . htmlspecialchars($subject['id']) . "'>" . htmlspecialchars($subject['subject_name']) . "</option>";
            }
            ?>
        </select>
        <button type="button" onclick="removeSubject(this)" class="px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    container.appendChild(newEntry);
}

function removeSubject(button) {
    const entry = button.parentElement;
    if (document.querySelectorAll('.subject-entry').length > 1) {
        entry.remove();
    } else {
        alert('You must have at least one subject.');
    }
}

function editFaculty(id, facultyId, name, email, contact, address, subjects) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_faculty_id').value = facultyId;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_contact').value = contact;
    document.getElementById('edit_address').value = address;

    // Clear existing subjects
    const container = document.getElementById('edit-subjects-container');
    container.innerHTML = '';

    // Add subjects
    if (subjects) {
        const subjectArray = subjects.split(',');
        subjectArray.forEach(subject => {
            const newEntry = document.createElement('div');
            newEntry.className = 'subject-entry flex gap-2';
            newEntry.innerHTML = `
                <select name="subjects[]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Subject</option>
                    <?php
                    $subjectStmt = $conn->query("SELECT * FROM subjects");
                    while ($subject = $subjectStmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . htmlspecialchars($subject['id']) . "'>" . htmlspecialchars($subject['subject_name']) . "</option>";
                    }
                    ?>
                </select>
                <button type="button" onclick="removeSubject(this)" class="px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
            container.appendChild(newEntry);

            // Set the selected subject
            const select = newEntry.querySelector('select');
            const options = select.options;
            for (let i = 0; i < options.length; i++) {
                if (options[i].text === subject.trim()) {
                    options[i].selected = true;
                    break;
                }
            }
        });
    }

    openModal('editFacultyModal');
}

function viewPerformance(facultyId, facultyName) {
    document.getElementById('facultyName').textContent = facultyName;
    
    // Reset charts if they exist
    if (subjectPerformanceChart) {
        subjectPerformanceChart.destroy();
    }
    if (gradeDistributionChart) {
        gradeDistributionChart.destroy();
    }

    // Show loading state
    document.getElementById('teachingScore').textContent = 'Loading...';
    document.getElementById('studentProgress').textContent = 'Loading...';
    document.getElementById('attendanceRate').textContent = 'Loading...';
    document.getElementById('passRate').textContent = 'Loading...';
    document.getElementById('totalStudents').textContent = 'Loading...';
    document.getElementById('studentsAbove85').textContent = 'Loading...';
    document.getElementById('avgPerformance').textContent = 'Loading...';
    document.getElementById('subjectPerformanceTable').innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center">Loading...</td></tr>';

    // Fetch performance data
    fetch(`../../Controllers/get-faculty-performance.php?faculty_id=${facultyId}`)
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                const data = response.data;
                
                // Update statistics
                document.getElementById('teachingScore').textContent = data.teachingEffectiveness + '%';
                document.getElementById('studentProgress').textContent = (data.averageProgress >= 0 ? '+' : '') + data.averageProgress + '%';
                document.getElementById('attendanceRate').textContent = data.overallAttendanceRate + '%';
                document.getElementById('passRate').textContent = data.overallPassRate + '%';
                document.getElementById('totalStudents').textContent = data.totalStudents;
                document.getElementById('studentsAbove85').textContent = `${data.studentsAbove85Percentage}% (${data.studentsAbove85}/${data.totalStudents})`;
                document.getElementById('avgPerformance').textContent = data.overallAverage + '%';

                // Update subject performance table
                const tableBody = document.getElementById('subjectPerformanceTable');
                tableBody.innerHTML = '';
                
                data.subjectPerformance.forEach(subject => {
                    const row = document.createElement('tr');
                    const improvementClass = subject.improvement >= 0 ? 'text-green-600' : 'text-red-600';
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${subject.subject}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${subject.prelimAvg}%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${subject.midtermAvg}%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${subject.finalAvg}%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm ${improvementClass}">
                            ${(subject.improvement >= 0 ? '+' : '') + subject.improvement}%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${subject.passRate}%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${subject.attendanceRate}%</td>
                    `;
                    tableBody.appendChild(row);
                });

                // Create Subject Performance Chart
                const subjectCtx = document.getElementById('subjectPerformanceChart').getContext('2d');
                subjectPerformanceChart = new Chart(subjectCtx, {
                    type: 'line',
                    data: {
                        labels: data.subjectPerformance.map(s => s.subject),
                        datasets: [{
                            label: 'Prelim',
                            data: data.subjectPerformance.map(s => s.prelimAvg),
                            borderColor: 'rgb(59, 130, 246)',
                            tension: 0.1
                        }, {
                            label: 'Midterm',
                            data: data.subjectPerformance.map(s => s.midtermAvg),
                            borderColor: 'rgb(16, 185, 129)',
                            tension: 0.1
                        }, {
                            label: 'Final',
                            data: data.subjectPerformance.map(s => s.finalAvg),
                            borderColor: 'rgb(245, 158, 11)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100
                            }
                        }
                    }
                });

                // Create Progress Distribution Chart
                const gradeCtx = document.getElementById('gradeDistributionChart').getContext('2d');
                const progressData = data.subjectPerformance.reduce((acc, subject) => {
                    if (subject.improvement >= 15) acc['Excellent (>15%)']++;
                    else if (subject.improvement >= 10) acc['Good (10-15%)']++;
                    else if (subject.improvement >= 5) acc['Fair (5-10%)']++;
                    else if (subject.improvement >= 0) acc['Minimal (0-5%)']++;
                    else acc['Needs Improvement (<0%)']++;
                    return acc;
                }, {
                    'Excellent (>15%)': 0,
                    'Good (10-15%)': 0,
                    'Fair (5-10%)': 0,
                    'Minimal (0-5%)': 0,
                    'Needs Improvement (<0%)': 0
                });

                gradeDistributionChart = new Chart(gradeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(progressData),
                        datasets: [{
                            data: Object.values(progressData),
                            backgroundColor: [
                                'rgba(34, 197, 94, 0.5)',
                                'rgba(59, 130, 246, 0.5)',
                                'rgba(168, 85, 247, 0.5)',
                                'rgba(234, 179, 8, 0.5)',
                                'rgba(239, 68, 68, 0.5)'
                            ],
                            borderColor: [
                                'rgb(34, 197, 94)',
                                'rgb(59, 130, 246)',
                                'rgb(168, 85, 247)',
                                'rgb(234, 179, 8)',
                                'rgb(239, 68, 68)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'right'
                            }
                        }
                    }
                });
            } else {
                alert('Failed to load performance data: ' + (response.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching performance data');
        });

    openModal('performanceModal');
}
</script>
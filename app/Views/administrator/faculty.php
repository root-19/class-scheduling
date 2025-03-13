<?php

session_start();

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Controllers/FacultyController.php';

use App\Config\Database;

$database = new Database();
$conn = $database->connect();

// Handle Add Faculty
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_faculty'])) {
    $facultyId = $_POST['faculty_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];


    $stmt = $conn->prepare("INSERT INTO faculty (faculty_id, name, email, contact, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$facultyId, $name, $email, $contact]);

    header("Location: faculty.php");
    exit();
}

// Handle Update Faculty
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_faculty'])) {
    $id = $_POST['id'];
    $facultyId = $_POST['faculty_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];


    $stmt = $conn->prepare("UPDATE faculty SET faculty_id=?, name=?, email=?, contact=?, address=? WHERE id=?");
    $stmt->execute([$facultyId, $name, $email, $contact,$address, $id]);

    header("Location: faculty.php");
    exit();
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
<div class="p-6 w-full">
    <div class="p-4 border-2 border-gray-200 rounded-lg">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Faculty List</h1>

        <!-- Add Faculty Button -->
        <button onclick="openModal('addFacultyModal')" class="bg-blue-600 hover:bg-green-700 text-white font-semibold px-5 py-2 rounded-lg shadow-md transition">
            + Add Faculty
        </button>

        <!-- Faculty Table -->
        <div class="mt-6 overflow-x-auto">
            <table class="w-full border border-gray-200 shadow-md rounded-lg overflow-hidden">
                <thead class="bg-blue-600 text-white text-md">
                    <tr>
                        <th class="p-3 border">Faculty ID</th>
                        <th class="p-3 border">Name</th>
                        <th class="p-3 border">Email</th>
                        <th class="p-3 border">Contact</th>
                        <th class="p-3 border">Address</th>
                        <th class="p-3 border">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-100 text-gray-800">
                    <?php if (count($facultyList) > 0): ?>
                        <?php foreach ($facultyList as $faculty): ?>
                            <tr class="hover:bg-gray-200 transition-colors">
                                <td class="p-3 border"><?php echo htmlspecialchars($faculty['faculty_id']); ?></td>
                                <td class="p-3 border"><?php echo htmlspecialchars($faculty['name']); ?></td>
                                <td class="p-3 border"><?php echo htmlspecialchars($faculty['email']); ?></td>
                                <td class="p-3 border"><?php echo htmlspecialchars($faculty['contact']); ?></td>
                                <td class="p-3 border"><?php echo htmlspecialchars($faculty['address']); ?></td>

                                <td class="p-3 border">
                                    <div class="flex space-x-2">
                                        <button 
                                            onclick="editFaculty(<?php echo $faculty['id']; ?>, '<?php echo $faculty['faculty_id']; ?>', '<?php echo addslashes($faculty['name']); ?>', '<?php echo $faculty['email']; ?>', '<?php echo $faculty['contact']; ?>')" 
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">
                                            Edit
                                        </button>
                                        <a href="faculty.php?delete=<?php echo $faculty['id']; ?><?php echo isset($_GET['page']) ? '&page='.$_GET['page'] : ''; ?>" 
                                           onclick="return confirm('Are you sure you want to delete this faculty member?')" 
                                           class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-3 text-center">No faculty records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- Pagination Controls -->
            <?php if ($totalPages > 1): ?>
            <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $itemsPerPage, $totalRecords); ?> of <?php echo $totalRecords; ?> entries
                </div>
                <div class="flex space-x-2">
                    <?php if ($currentPage > 1): ?>
                        <a href="faculty.php?page=<?php echo $currentPage - 1; ?>" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                            Previous
                        </a>
                    <?php else: ?>
                        <button disabled class="px-4 py-2 bg-gray-300 text-gray-500 rounded cursor-not-allowed">
                            Previous
                        </button>
                    <?php endif; ?>
                    
                    <div class="flex space-x-1">
                        <?php 
                        // Show limited number of page links
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $startPage + 4);
                        if ($endPage - $startPage < 4) {
                            $startPage = max(1, $endPage - 4);
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                            <a href="faculty.php?page=<?php echo $i; ?>" 
                               class="px-3 py-2 rounded <?php echo ($i == $currentPage) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="faculty.php?page=<?php echo $currentPage + 1; ?>" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                            Next
                        </a>
                    <?php else: ?>
                        <button disabled class="px-4 py-2 bg-gray-300 text-gray-500 rounded cursor-not-allowed">
                            Next
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modals -->
<div id="addFacultyModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 transition-opacity z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Add Faculty</h2>
        <form action="faculty.php<?php echo isset($_GET['page']) ? '?page='.$_GET['page'] : ''; ?>" method="POST">
            <input type="hidden" name="add_faculty" value="1">
            <input type="text" name="faculty_id" placeholder="Faculty ID" required class="w-full px-4 py-2 border rounded mb-2">
            <input type="text" name="name" placeholder="Name" required class="w-full px-4 py-2 border rounded mb-2">
            <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2 border rounded mb-2">
            <input type="text" name="contact" placeholder="Contact" required class="w-full px-4 py-2 border rounded mb-2">
            <input type="text" name="address" placeholder="Address" required class="w-full px-4 py-2 border rounded mb-2">
            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded w-full">Add</button>
        </form>
        <button onclick="closeModal('addFacultyModal')" class="mt-2 w-full bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
    </div>
</div>

<!-- Edit Faculty Modal -->
<div id="editFacultyModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 transition-opacity z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Edit Faculty</h2>
        <form action="faculty.php<?php echo isset($_GET['page']) ? '?page='.$_GET['page'] : ''; ?>" method="POST">
            <input type="hidden" name="edit_faculty" value="1">
            <input type="hidden" id="edit_id" name="id">
            <input type="text" id="edit_faculty_id" name="faculty_id" required class="w-full px-4 py-2 border rounded mb-2">
            <input type="text" id="edit_name" name="name" required class="w-full px-4 py-2 border rounded mb-2">
            <input type="email" id="edit_email" name="email" required class="w-full px-4 py-2 border rounded mb-2">
            <input type="text" id="edit_contact" name="contact" required class="w-full px-4 py-2 border rounded mb-2">
            <input type="text" id="edit_address" name="address" required class="w-full px-4 py-2 border rounded mb-2">

            <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded w-full">Update</button>
        </form>
        <button onclick="closeModal('editFacultyModal')" class="mt-2 w-full bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
    </div>
</div>
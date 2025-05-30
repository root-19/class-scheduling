<?php  
session_start();

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Controllers/SubjectController.php';


use App\Controllers\SubjectController;

$subjectController = new SubjectController();
$subjectController->handleRequest();
$subjects = $subjectController->getSubjects();
include './layout/sidebar.php';
?>
<div class="p-8 w-full bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-gray-800">Manage Subjects</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                <?php if ($_GET['success'] == 1): ?>
                    Subject(s) added successfully!
                <?php elseif ($_GET['success'] == 2): ?>
                    Subject deleted successfully!
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <?php if ($_GET['error'] == 1): ?>
                    Error adding subject(s). Please try again.
                <?php elseif ($_GET['error'] == 2): ?>
                    Error deleting subject. Please try again.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Subject Form -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Add New Subject</h2>
            <form method="POST" action="subject-list.php" class="space-y-6">
                <div id="subjects-container">
                    <div class="subject-entry p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Subject Name</label>
                                <input type="text" name="subject_name[]" required 
                                    class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                                <input type="number" name="unit[]" required 
                                    class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description[]" required 
                                class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors h-24"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="addSubjectEntry()" 
                        class="inline-flex items-center px-4 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Another Subject
                    </button>
                    <button type="submit" name="add_subject" 
                        class="inline-flex items-center px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Subject(s)
                    </button>
                </div>
            </form>
        </div>

        <!-- Subjects Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-700">Subject List</h2>
                <!-- Search Bar -->
                <div class="mt-4">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search subjects..." 
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($subjects as $row): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $row['id'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $row['subject_name'] ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= $row['description'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $row['unit'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="subject-list.php?delete=<?= $row['id'] ?>" 
                                        class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors"
                                        onclick="return confirm('Are you sure you want to delete this subject?')">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function addSubjectEntry() {
    const container = document.getElementById('subjects-container');
    const entry = document.querySelector('.subject-entry');
    const newEntry = entry.cloneNode(true);

    newEntry.querySelectorAll('input, textarea').forEach(el => el.value = '');
    container.appendChild(newEntry);
}

function confirmDelete(event) {
    if (!confirm('Are you sure you want to delete this subject?')) {
        event.preventDefault();
    }
}

function searchSubjects() {
    const searchInput = document.getElementById('searchInput');
    const filter = searchInput.value.toLowerCase();
    const tbody = document.querySelector('tbody');
    const rows = tbody.getElementsByTagName('tr');

    for (let row of rows) {
        const subjectName = row.getElementsByTagName('td')[1].textContent.toLowerCase();
        const description = row.getElementsByTagName('td')[2].textContent.toLowerCase();
        
        if (subjectName.includes(filter) || description.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

// Add click event listeners to delete buttons and search functionality
document.addEventListener('DOMContentLoaded', function() {
    const deleteLinks = document.querySelectorAll('a[href*="delete="]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', confirmDelete);
    });

    // Add search event listener
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('keyup', searchSubjects);
});
</script>

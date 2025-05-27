<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Config\Database;

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

error_log("=== Starting student update process ===");
error_log("Time: " . date('Y-m-d H:i:s'));
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['update_student'])) {
    error_log("Error: Invalid request method or missing update_student parameter");
    header("Location: student-list.php?error=invalid_request");
    exit();
}

try {
    $database = new Database();
    $conn = $database->connect();
    
    error_log("Database connection established");
    error_log("POST Data received: " . print_r($_POST, true));

    // Get and validate form data
    $id = $_POST['id'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $studentId = trim($_POST['student_id'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $faculty = trim($_POST['faculty_name'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $sections = trim(trim($_POST['sections'] ?? '', '"')); // Remove quotes from sections
    $subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];

    error_log("Processed form data:");
    error_log("ID: $id");
    error_log("First Name: $firstName");
    error_log("Last Name: $lastName");
    error_log("Email: $email");
    error_log("Student ID: $studentId");
    error_log("Contact: $contact");
    error_log("Faculty: $faculty");
    error_log("Course: $course");
    error_log("Sections: $sections");
    error_log("Subjects: " . print_r($subjects, true));

    // Validate required fields
    $missingFields = [];
    if (empty($id)) $missingFields[] = 'Student ID';
    if (empty($firstName)) $missingFields[] = 'First Name';
    if (empty($lastName)) $missingFields[] = 'Last Name';
    if (empty($email)) $missingFields[] = 'Email';
    if (empty($studentId)) $missingFields[] = 'Student ID Number';
    
    if (!empty($missingFields)) {
        error_log("Validation Error - Missing fields: " . implode(', ', $missingFields));
        header("Location: student-list.php?error=missing_fields&fields=" . urlencode(implode(', ', $missingFields)));
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Validation Error - Invalid email format: $email");
        header("Location: student-list.php?error=invalid_email");
        exit();
    }

    // Check for duplicate email
    error_log("Checking for duplicate email...");
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ? AND role = 'student'");
    $checkStmt->execute([$email, $id]);
    if ($checkStmt->rowCount() > 0) {
        error_log("Error - Duplicate email found: $email");
        header("Location: student-list.php?error=duplicate_email");
        exit();
    }

    // Check for duplicate student ID
    error_log("Checking for duplicate student ID...");
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE student_id = ? AND id != ? AND role = 'student'");
    $checkStmt->execute([$studentId, $id]);
    if ($checkStmt->rowCount() > 0) {
        error_log("Error - Duplicate student ID found: $studentId");
        header("Location: student-list.php?error=duplicate_student_id");
        exit();
    }

    // Get current student data
    error_log("Fetching current student data...");
    $currentDataStmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
    $currentDataStmt->execute([$id]);
    $currentData = $currentDataStmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentData) {
        error_log("Error - Student not found with ID: $id");
        header("Location: student-list.php?error=student_not_found");
        exit();
    }

    // Convert subjects array to comma-separated string without quotes
    $subjectsStr = is_array($subjects) ? implode(', ', $subjects) : '';
    error_log("Processed subjects string: $subjectsStr");

    // Prepare and execute update
    error_log("Preparing to update student information...");
    $stmt = $conn->prepare("
        UPDATE users 
        SET first_name = ?, 
            last_name = ?, 
            email = ?, 
            student_id = ?, 
            contact = ?, 
            faculty = ?, 
            course = ?, 
            sections = ?, 
            subjects = ?
        WHERE id = ? AND role = 'student'
    ");

    $updateParams = [
        $firstName, $lastName, $email, $studentId, $contact,
        $faculty, $course, $sections, $subjectsStr, $id
    ];
    error_log("Update parameters: " . print_r($updateParams, true));

    $result = $stmt->execute($updateParams);

    if (!$result) {
        $error = $stmt->errorInfo();
        error_log("Database Error: " . print_r($error, true));
        header("Location: student-list.php?error=database_error");
        exit();
    }

    // Log changes made
    $changes = [];
    if ($currentData['faculty'] !== $faculty) $changes[] = 'faculty';
    if ($currentData['course'] !== $course) $changes[] = 'course';
    if ($currentData['subjects'] !== $subjectsStr) $changes[] = 'subjects';
    if ($currentData['sections'] !== $sections) $changes[] = 'sections';

    error_log("Changes made: " . implode(', ', $changes));
    error_log("Update completed successfully");

    header("Location: student-list.php?success=true");
    exit();

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    header("Location: student-list.php?error=system_error");
    exit();
}
?> 
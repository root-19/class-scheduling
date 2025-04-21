<?php
// get-grades.php
include_once __DIR__ . '/../../Config/Database.php';
header('Content-Type: application/json');

if (!isset($_GET['student_id'])) {
    echo json_encode(['error' => 'No student_id provided']);
    exit;
}

$student_id = $_GET['student_id'];

$stmt = $conn->prepare("SELECT subject, prelim, midterm, final FROM grades WHERE student_id = ?");
if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$grades = [];
while ($row = $result->fetch_assoc()) {
    $grades[] = $row;
}

echo json_encode($grades);
?>
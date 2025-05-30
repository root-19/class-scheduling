<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Config/Database.php';

use App\Config\Database;

header('Content-Type: application/json');

$database = new Database();
$conn = $database->connect();

if (!isset($_GET['faculty_id'])) {
    echo json_encode(['error' => 'Faculty ID is required']);
    exit;
}

$facultyId = $_GET['faculty_id'];

try {
    // Get faculty's subjects and name
    $stmt = $conn->prepare("SELECT id, name, subjects FROM faculty WHERE id = ?");
    $stmt->execute([$facultyId]);
    $faculty = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$faculty) {
        echo json_encode(['error' => 'Faculty not found']);
        exit;
    }

    // Parse subjects
    $subjects = [];
    if (!empty($faculty['subjects'])) {
        // Try to decode JSON format first
        $subjectData = json_decode($faculty['subjects'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($subjectData)) {
            $subjects = array_column($subjectData, 'subject_name');
        } else {
            // Fallback to comma-separated format
            $subjects = array_map('trim', explode(',', $faculty['subjects']));
        }
    }

    $performance = [];
    $totalStudents = 0;
    $studentsAbove85 = 0;
    $overallGradeSum = 0;
    $overallGradeCount = 0;
    $totalAttendance = 0;
    $totalAttendanceDays = 0;
    $totalPassingStudents = 0;
    $teachingEffectiveness = 0;
    $studentProgress = [];

    foreach ($subjects as $subject) {
        // Get all grades for this subject including attendance
        $stmt = $conn->prepare("
            SELECT 
                g.*, 
                u.first_name, 
                u.last_name,
                u.student_id,
                g.attendance as attendance_days
            FROM grades g 
            JOIN users u ON g.student_id = u.id 
            WHERE g.subject = ? AND u.faculty LIKE ?
        ");
        $stmt->execute([$subject, '%' . $faculty['name'] . '%']);
        $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $subjectStats = [
            'subject' => $subject,
            'totalStudents' => count($grades),
            'averageGrade' => 0,
            'passRate' => 0,
            'studentsAbove85' => 0,
            'attendanceRate' => 0,
            'prelimAvg' => 0,
            'midtermAvg' => 0,
            'finalAvg' => 0,
            'improvement' => 0
        ];

        if (count($grades) > 0) {
            $gradeSum = 0;
            $passCount = 0;
            $above85Count = 0;
            $prelimSum = 0;
            $midtermSum = 0;
            $finalSum = 0;
            $attendanceSum = 0;

            foreach ($grades as $grade) {
                // Calculate attendance rate
                $attendanceDays = intval($grade['attendance_days']);
                $totalAttendance += $attendanceDays;
                $totalAttendanceDays += 100; // Assuming 100 days per subject

                // Calculate grade averages
                $prelim = floatval($grade['prelim']);
                $midterm = floatval($grade['midterm']);
                $final = floatval($grade['final']);

                $prelimSum += $prelim;
                $midtermSum += $midterm;
                $finalSum += $final;

                // Calculate average grade
                $validGrades = array_filter([$prelim, $midterm, $final], function($g) { return $g > 0; });
                if (count($validGrades) > 0) {
                    $avgGrade = array_sum($validGrades) / count($validGrades);
                    $gradeSum += $avgGrade;
                    
                    if ($avgGrade >= 75) {
                        $passCount++;
                        $totalPassingStudents++;
                    }
                    if ($avgGrade >= 85) {
                        $above85Count++;
                        $studentsAbove85++;
                    }
                    
                    $overallGradeSum += $avgGrade;
                    $overallGradeCount++;

                    // Calculate student progress
                    if ($prelim > 0 && $midterm > 0) {
                        $improvement1 = $midterm - $prelim;
                    }
                    if ($midterm > 0 && $final > 0) {
                        $improvement2 = $final - $midterm;
                    }
                    if (isset($improvement1) && isset($improvement2)) {
                        $studentProgress[] = ($improvement1 + $improvement2) / 2;
                    }
                }
            }

            $studentCount = count($grades);
            $subjectStats['averageGrade'] = round($gradeSum / $studentCount, 2);
            $subjectStats['passRate'] = round(($passCount / $studentCount) * 100, 2);
            $subjectStats['studentsAbove85'] = $above85Count;
            $subjectStats['prelimAvg'] = round($prelimSum / $studentCount, 2);
            $subjectStats['midtermAvg'] = round($midtermSum / $studentCount, 2);
            $subjectStats['finalAvg'] = round($finalSum / $studentCount, 2);
            $subjectStats['attendanceRate'] = round(($attendanceSum / ($studentCount * 100)) * 100, 2);
            $subjectStats['improvement'] = round(($subjectStats['finalAvg'] - $subjectStats['prelimAvg']), 2);
            
            $totalStudents += $studentCount;
        }

        $performance[] = $subjectStats;
    }

    // Calculate overall statistics
    $overallAverage = $overallGradeCount > 0 ? round($overallGradeSum / $overallGradeCount, 2) : 0;
    $studentsAbove85Percentage = $totalStudents > 0 ? round(($studentsAbove85 / $totalStudents) * 100, 2) : 0;
    $overallAttendanceRate = $totalAttendanceDays > 0 ? round(($totalAttendance / $totalAttendanceDays) * 100, 2) : 0;
    $overallPassRate = $totalStudents > 0 ? round(($totalPassingStudents / $totalStudents) * 100, 2) : 0;
    
    // Calculate average student progress
    $averageProgress = count($studentProgress) > 0 ? round(array_sum($studentProgress) / count($studentProgress), 2) : 0;

    // Calculate teaching effectiveness score (0-100)
    $teachingEffectiveness = round(
        ($overallPassRate * 0.3) +          // 30% weight on pass rate
        ($studentsAbove85Percentage * 0.3) + // 30% weight on excellence
        ($overallAttendanceRate * 0.2) +     // 20% weight on attendance
        (min(max($averageProgress * 10, 0), 20)) // 20% weight on student progress
    , 2);

    echo json_encode([
        'success' => true,
        'data' => [
            'facultyName' => $faculty['name'],
            'overallAverage' => $overallAverage,
            'totalStudents' => $totalStudents,
            'studentsAbove85' => $studentsAbove85,
            'studentsAbove85Percentage' => $studentsAbove85Percentage,
            'overallAttendanceRate' => $overallAttendanceRate,
            'overallPassRate' => $overallPassRate,
            'averageProgress' => $averageProgress,
            'teachingEffectiveness' => $teachingEffectiveness,
            'subjectPerformance' => $performance
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => 'An error occurred while fetching performance data',
        'details' => $e->getMessage()
    ]);
} 
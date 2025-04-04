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
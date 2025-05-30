<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Config\Database;
use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController {
    private $user;

    public function __construct() {
        $db = new Database();
        $conn = $db->connect();
        $this->user = new User($conn);
    }

    public function register($firstName, $lastName, $email, $student_id, $contact, $password, $role, $subjects, $sections, $prelim, $semester, $faculty, $course)
    {
        $imageName = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageTmpPath = $_FILES['image']['tmp_name'];
            $imageName = time() . '-' . basename($_FILES['image']['name']);
            $imagePath = dirname(__DIR__, 2) . '/uploads/' . $imageName;

            if (!move_uploaded_file($imageTmpPath, $imagePath)) {
                return ["success" => false, "message" => "Error uploading image."];
            }
        }

        $subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];
        $sections = isset($_POST['sections']) ? trim($_POST['sections'], '"') : '';

        $register = $this->user->register(
            $firstName, $lastName, $student_id, $contact, $email,
            $password, $role, $imageName, $subjects, $sections,
            $prelim, $semester, $faculty, $course
        );

        if ($register) {
            if ($this->sendEmail($firstName, $email, $student_id, $password)) {
                return ["success" => true, "message" => "Registration successful! Check your email for login details."];
            } else {
                return ["success" => false, "message" => "Registered, but failed to send email."];
            }
        }

        return ["success" => false, "message" => "Error registering user."];
    }


    private function sendEmail($firstName, $email,  $student_id, $password) {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; 
            $mail->SMTPAuth = true;
            $mail->Username = 'hperformanceexhaust@gmail.com';
            $mail->Password = 'wolv wvyy chhl rvvm';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;


            // Recipients
            $mail->setFrom('hperformanceexhaust@gmail.com', 'Your App Name');
            $mail->addAddress($email, $firstName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Welcome to Our Platform!';
            $mail->Body = "
                <h3>Hello, $firstName!</h3>
                <p>Thank you for registering.</p>
                <p>Your login details:</p>
                <ul>
                    <li>Email: <strong>$email</strong></li>
                    <li>Password: <strong>$password</strong></li>
                </ul>
                <p>Please change your password after logging in.</p>
                <br>
                <p>Best Regards,<br>Your Company Name</p>
            ";

            // Send email
            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function login($email, $password) {
        error_log("Login attempt for email: " . $email);
        
        // Check in users table first
        $user = $this->user->findUserByEmail($email);
        error_log("User table check result: " . ($user ? "Found" : "Not found"));
    
        if ($user) {
            if (!password_verify($password, $user['password'])) {
                error_log("User password verification failed");
                return ["success" => false, "message" => "Incorrect password."];
            }
    
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name']; 
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['course'] = $user['course'];
            
            // Handle sections - remove any quotes and store as is
            $_SESSION['sections'] = trim($user['sections'], '"');
            
            $_SESSION['faculty'] = $user['faculty_name'];
            error_log("User login successful, role: " . $user['role']);
    
            // Redirect based on role
            if ($user['role'] == "student") {
                header("Location: ../Views/student/Dashboard.php");
                exit();
            } else {
                header("Location: ../Views/administrator/Dashboard.php");
                exit();
            }
        }
    
        // If no user is found, check the faculty table
        error_log("Checking faculty table");
        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare("SELECT * FROM faculty WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $faculty = $stmt->fetch(\PDO::FETCH_ASSOC);
        error_log("Faculty table check result: " . ($faculty ? "Found" : "Not found"));
    
        if ($faculty) {
            error_log("Verifying faculty password");
            error_log("Stored password hash: " . $faculty['password']);
            
            if (!password_verify($password, $faculty['password'])) {
                error_log("Faculty password verification failed");
                return ["success" => false, "message" => "Incorrect password."];
            }
    
            error_log("Faculty password verified successfully");
            session_start();
            $_SESSION['faculty_id'] = $faculty['id'];
            $_SESSION['faculty_name'] = $faculty['name']; 
            $_SESSION['role'] = "faculty"; 
    
            header("Location: ../Views/faculty/Dashboard.php");
            exit();
        }
    
        error_log("No matching user found in either table");
        return ["success" => false, "message" => "User not found."];
    }
}    
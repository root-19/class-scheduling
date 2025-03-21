<?php
namespace App\Controllers; 

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Config\Database;
use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController {
    private $user;
    private $db;

    public function __construct() {
        $this->db = new Database();
        $conn = $this->db->connect();
        $this->user = new User($conn);
    }

    public function register($firstName, $lastName, $email, $student_id, $contact, $password) {
        $register = $this->user->register($firstName, $lastName, $student_id, $contact, $email, $password);
        
        if ($register) {
            // Send email
            if ($this->sendEmail($firstName, $email,  $student_id, $password)) {
                return ["success" => true, "message" => "Registration successful! Check your email for login details."];
            } else {
                return ["success" => false, "message" => "Registration successful, but failed to send email."];
            }
        } else {
            return ["success" => false, "message" => "Error registering user."];
        }
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
            $mail->setFrom('your_email@gmail.com', 'Your App Name');
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
        $user = $this->user->findUserByEmail($email);
    
        if (!$user) {
            return ["success" => false, "message" => "User not found."];
        }
    
        // Verify password
        if (!password_verify($password, $user['password'])) {
            return ["success" => false, "message" => "Incorrect password."];
        }
    
        // Start user session
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name']; 
        $_SESSION['last_name'] = $user['last_name']; 
        $_SESSION['role'] = $user['role'];
    
        // Redirect based on role
        if ($user['role'] == "student") {
            header("Location: ../Views/student/Dashboard.php");
        } else {
            header("Location: ../Views/administrator/Dashboard.php");
        }
    
        return ["success" => true, "message" => "Login successful!"];
    }
    
}

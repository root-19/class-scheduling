<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Models/User.php';

use App\Config\Database;
use App\Models\User;
use App\Controllers\AuthController; 

$db = new Database();
$conn = $db->connect();
$auth = new AuthController(); 

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $auth->login($email, $password);

    if (!$result['success']) {
        $message = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Scheduling</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
        <div class="text-center">
            <img src="logo.png" alt="Logo" class="w-16 mx-auto mb-2">
            <h1 class="text-2xl font-semibold">Login</h1>
            <p class="text-gray-500 text-sm scheduling">Scheduling</p>
        </div>
        <?php if ($message): ?>
            <p class="text-red-500 text-center"><?= $message ?></p>
        <?php endif; ?>
        <form action="" method="POST" class="mt-4 space-y-4">
            <!-- <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2 border rounded-lg focus:ring"> -->
            <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border rounded-lg focus:ring">
            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">Login</button>
        </form>
        <!-- <p class="text-center text-gray-500 text-sm mt-3">
            Don't have an account? <a href="register.php" class="text-blue-500">Sign up</a>
        </p> -->
    </div>
</body>
</html>

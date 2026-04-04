<?php

namespace App\Controllers;

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Models\User;
use App\Middleware\Session;

class AuthController {
    public function handle() {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'login':
                $this->login();
                break;
            case 'signup':
                $this->signup();
                break;
            case 'logout':
                $this->logout();
                break;
            default:
                header('Location: ../../public/login.php');
                exit;
        }
    }

    private function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password_hash'])) {
            Session::set('user_id', $user['id']);
            Session::set('username', $user['username']);
            Session::set('user_role', $user['role_name']);
            
            header('Location: ../../public/index.php');
            exit;
        } else {
            header('Location: ../../public/login.php?error=invalid_credentials');
            exit;
        }
    }

    private function signup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role_id = $_POST['role_id'] ?? 2; // Default to Student

        $userModel = new User();
        
        // Check if user exists
        if ($userModel->findByEmail($email)) {
            header('Location: ../../public/signup.php?error=user_exists');
            exit;
        }

        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $token = bin2hex(random_bytes(16));

        $data = [
            'username' => $username,
            'email' => $email,
            'password_hash' => $password_hash,
            'role_id' => $role_id,
            'verification_token' => $token
        ];

        if ($userModel->create($data)) {
            // In a real app, send verification email here
            header('Location: ../../public/login.php?success=account_created');
            exit;
        } else {
            header('Location: ../../public/signup.php?error=creation_failed');
            exit;
        }
    }

    private function logout() {
        Session::destroy();
        header('Location: ../../public/login.php');
        exit;
    }
}

$controller = new AuthController();
$controller->handle();

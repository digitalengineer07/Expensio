<?php
namespace App\Controllers;

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Models\User;
use App\Middleware\Session;

class ProfileController {
    private $userModel;

    public function __construct() {
        if (!Session::isLoggedIn()) {
            header('Location: ../../public/login.php');
            exit;
        }
        $this->userModel = new User();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'update_profile':
                $this->updateProfile();
                break;
            case 'update_password':
                $this->updatePassword();
                break;
            case 'delete_account':
                $this->deleteAccount();
                break;
            default:
                header('Location: ../../public/profile.php');
                break;
        }
    }

    private function updateProfile() {
        $userId = Session::get('user_id');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (!empty($username) && !empty($email)) {
            $this->userModel->updateProfile($userId, $username, $email);
            Session::set('username', $username);
            Session::set('success_message', 'Profile updated successfully.');
        } else {
            Session::set('error_message', 'Fields cannot be empty.');
        }
        header('Location: ../../public/profile.php');
        exit;
    }

    private function updatePassword() {
        $userId = Session::get('user_id');
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        $user = $this->userModel->findById($userId);

        if (!password_verify($currentPass, $user['password_hash'])) {
            Session::set('error_message', 'Current password is incorrect.');
        } elseif ($newPass !== $confirmPass) {
            Session::set('error_message', 'New passwords do not match.');
        } else {
            $this->userModel->updatePassword($userId, password_hash($newPass, PASSWORD_DEFAULT));
            Session::set('success_message', 'Password updated successfully.');
        }
        header('Location: ../../public/profile.php');
        exit;
    }

    private function deleteAccount() {
        $userId = Session::get('user_id');
        $this->userModel->deleteAccount($userId);
        Session::destroy();
        header('Location: ../../public/login.php');
        exit;
    }
}

$controller = new ProfileController();
$controller->handleRequest();

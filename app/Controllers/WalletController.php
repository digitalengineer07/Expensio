<?php
namespace App\Controllers;

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Models\Wallet;
use App\Middleware\Session;

class WalletController {
    public function handleRequest() {
        if (!Session::isLoggedIn()) {
            header('Location: ../../public/login.php');
            exit;
        }

        $action = $_GET['action'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($action === 'create') {
                $this->create();
            } elseif ($action === 'delete') {
                $this->delete();
            }
        }
    }

    private function create() {
        $walletModel = new Wallet();
        $userId = Session::get('user_id');
        $name = $_POST['wallet_name'] ?? '';
        $type = $_POST['wallet_type'] ?? 'Cash';
        $balance = $_POST['balance'] ?? 0;

        $walletModel->create($userId, $name, $type, $balance);
        Session::set('success_message', 'Wallet created successfully!');
        header('Location: ../../public/wallet.php');
        exit;
    }

    private function delete() {
        $walletModel = new Wallet();
        $userId = Session::get('user_id');
        $id = $_POST['id'] ?? null;

        if ($id) {
            $walletModel->delete($id, $userId);
        }
        header('Location: ../../public/wallet.php');
        exit;
    }
}

// Instantiate and handle
$controller = new WalletController();
$controller->handleRequest();

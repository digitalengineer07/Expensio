<?php
namespace App\Controllers;

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Models\Transaction;
use App\Models\Wallet;
use App\Middleware\Session;

class WalletTransactionController {
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
        $transactionModel = new Transaction();
        $walletModel = new Wallet();
        $userId = Session::get('user_id');
        
        $amount = $_POST['amount'] ?? 0;
        // For category we map from ID to name if possible, or just store string
        $category = $_POST['category'] ?? ''; 
        $type = $_POST['type'] ?? 'expense';
        $walletId = $_POST['wallet_id'] ?? null;
        $note = $_POST['description'] ?? '';
        $date = $_POST['transaction_date'] ?? date('Y-m-d');

        if ($walletId && $amount > 0) {
            // No balance validation needed; allow negative balances
            $wallet = $walletModel->getById($walletId, $userId);

            // Create tx
            $transactionModel->create($userId, $walletId, $amount, $category, $type, $note, $date);
            // Update wallet balance
            $walletModel->updateBalance($walletId, $amount, $type);
            
            Session::set('success_message', 'Transaction added successfully!');
        }

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    private function delete() {
        $transactionModel = new Transaction();
        $userId = Session::get('user_id');
        $id = $_POST['id'] ?? null;

        if ($id) {
            $transactionModel->delete($id, $userId);
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

// Instantiate and handle
$controller = new WalletTransactionController();
$controller->handleRequest();

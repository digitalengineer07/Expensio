<?php

namespace App\Controllers;

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Models\Expense;
use App\Middleware\Session;

class ExpenseController {
    public function handle() {
        if (!Session::isLoggedIn()) {
            header('Location: ../../public/login.php');
            exit;
        }

        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'add':
            case 'create':
                $this->add();
                break;
            default:
                header('Location: ../../public/index.php');
                exit;
        }
    }

    private function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $expenseModel = new Expense();
        
        $data = [
            'user_id' => Session::get('user_id'),
            'category_id' => $_POST['category_id'] ?? null,
            'project_id' => $_POST['project_id'] ?? null,
            'amount' => $_POST['amount'] ?? 0,
            'description' => $_POST['description'] ?? '',
            'expense_date' => $_POST['expense_date'] ?? date('Y-m-d'),
            'receipt_path' => $this->handleUpload()
        ];

        if ($expenseModel->create($data)) {
            header('Location: ../../public/index.php?success=expense_added');
        } else {
            header('Location: ../../public/index.php?error=failed_to_add');
        }
    }

    private function handleUpload() {
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/';
            $filename = uniqid() . '_' . basename($_FILES['receipt']['name']);
            $targetPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['receipt']['tmp_name'], $targetPath)) {
                return 'uploads/' . $filename;
            }
        }
        return null;
    }
}

$controller = new ExpenseController();
$controller->handle();

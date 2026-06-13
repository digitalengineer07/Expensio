<?php
namespace App\Controllers;

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Models\Setting;
use App\Models\Transaction;
use App\Middleware\Session;

class SettingsController {
    private $settingModel;

    public function __construct() {
        if (!Session::isLoggedIn()) {
            header('Location: ../../public/login.php');
            exit;
        }
        $this->settingModel = new Setting();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'update_globals':
                $this->updateGlobals();
                break;
            case 'update_notifications':
                $this->updateNotifications();
                break;
            case 'toggle_theme':
                $this->toggleTheme();
                break;
            case 'export_csv':
                $this->exportCSV();
                break;
            default:
                header('Location: ../../public/settings.php');
                break;
        }
    }

    private function updateGlobals() {
        $userId = Session::get('user_id');
        $currency = $_POST['currency'] ?? '₹';
        $language = $_POST['language'] ?? 'en';

        $this->settingModel->updateGlobals($userId, $currency, $language);
        Session::set('currency', $currency);
        Session::set('success_message', 'Regional settings updated.');
        header('Location: ../../public/settings.php');
        exit;
    }

    private function updateNotifications() {
        $userId = Session::get('user_id');
        
        $data = [
            'notifications_enabled' => isset($_POST['notifications_enabled']) ? true : false,
            'expense_alerts' => isset($_POST['expense_alerts']) ? true : false,
            'group_updates' => isset($_POST['group_updates']) ? true : false,
            'spending_suggestions' => isset($_POST['spending_suggestions']) ? true : false,
            'weekly_summary' => isset($_POST['weekly_summary']) ? true : false,
        ];

        $this->settingModel->updateNotificationSettings($userId, $data);
        Session::set('success_message', 'Notification preferences saved.');
        header('Location: ../../public/settings.php');
        exit;
    }

    private function toggleTheme() {
        $userId = Session::get('user_id');
        // Parse JSON
        $input = json_decode(file_get_contents('php://input'), true);
        $isDark = $input['dark_mode'] ?? false;
        
        $this->settingModel->toggleDarkMode($userId, $isDark);
        echo json_encode(['success' => true]);
        exit;
    }

    private function exportCSV() {
        $userId = Session::get('user_id');
        $transactionModel = new Transaction();
        $transactions = $transactionModel->getByUser($userId);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="expensio_history.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Date', 'Amount', 'Type', 'Category', 'Wallet', 'Description']);

        foreach ($transactions as $t) {
            fputcsv($output, [
                $t['id'],
                $t['transaction_date'],
                $t['amount'],
                $t['type'],
                $t['category'],
                $t['wallet_name'],
                $t['note']
            ]);
        }
        fclose($output);
        exit;
    }
}

$controller = new SettingsController();
$controller->handleRequest();

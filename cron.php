<?php
// cron.php - Can be run via CLI `php cron.php` or requested via browser
require_once __DIR__ . '/config/bootstrap.php';

use App\Models\Database;
use App\Models\RecurringTransaction;
use App\Models\Expense;

$db = Database::getInstance();
$recurringModel = new RecurringTransaction();
$expenseModel = new Expense();

$dueTransactions = $recurringModel->getDueTransactions();
$processedCount = 0;

foreach ($dueTransactions as $rt) {
    $currentNextDate = new \DateTime($rt['next_date']);
    $today = new \DateTime(date('Y-m-d'));
    
    while ($currentNextDate <= $today) {
        // Auto-create the transaction
        $expenseData = [
            'user_id' => $rt['user_id'],
            'category_id' => $rt['category_id'],
            'project_id' => null,
            'amount' => $rt['amount'],
            'description' => $rt['description'] . ' (Auto-Recurring)',
            'expense_date' => $currentNextDate->format('Y-m-d'),
            'receipt_path' => null
        ];
        
        $expenseModel->create($expenseData);
        $processedCount++;
        
        // Calculate the next cycle
        $freq = $rt['frequency'];
        if ($freq === 'daily') $currentNextDate->modify('+1 day');
        elseif ($freq === 'weekly') $currentNextDate->modify('+1 week');
        else $currentNextDate->modify('+1 month');
    }
    
    // Update the next expected date in the database
    $recurringModel->updateNextDate($rt['id'], $currentNextDate->format('Y-m-d'));
}

// Find transactions due tomorrow to notify
$tomorrow = new \DateTime(date('Y-m-d'));
$tomorrow->modify('+1 day');
$tomorrowStr = $tomorrow->format('Y-m-d');

$stmt = $db->prepare("SELECT * FROM recurring_transactions WHERE next_date = ?");
$stmt->execute([$tomorrowStr]);
$dueTomorrow = $stmt->fetchAll();

foreach ($dueTomorrow as $rt) {
    // Check if notification already exists to prevent duplicate spam
    $checkStmt = $db->prepare("SELECT id FROM notifications WHERE user_id = ? AND title = ? AND DATE(created_at) = CURRENT_DATE");
    $title = "Upcoming Recurring Transaction";
    $checkStmt->execute([$rt['user_id'], $title]);
    if (!$checkStmt->fetch()) {
        $msg = "Your recurring transaction '{$rt['description']}' for ₹{$rt['amount']} is scheduled for tomorrow.";
        $ins = $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'alert')");
        $ins->execute([$rt['user_id'], $title, $msg]);
    }
}

// Log success (Silent since it runs in the background on dashboard load)
// echo "Successfully processed $processedCount recurring transactions and checked notifications.";

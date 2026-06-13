<?php
namespace App\Controllers;

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Models\Group;
use App\Models\Notification;
use App\Middleware\Session;

class GroupController {
    private $groupModel;
    private $notificationModel;

    public function __construct() {
        if (!Session::isLoggedIn()) {
            header('Location: ../../public/login.php');
            exit;
        }
        $this->groupModel = new Group();
        $this->notificationModel = new Notification();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'create':
                $this->createGroup();
                break;
            case 'add_expense':
                $this->addGroupExpense();
                break;
            case 'settle':
                $this->settleUp();
                break;
            default:
                header('Location: ../../public/groups.php');
                break;
        }
    }

    private function createGroup() {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $inviteUsers = trim($_POST['invite_users'] ?? '');
        $userId = Session::get('user_id');

        if (empty($name)) {
            Session::set('error_message', 'Group name is required.');
            header('Location: ../../public/groups.php');
            exit;
        }

        $groupId = $this->groupModel->createGroup($name, $description, $userId);
        if ($groupId) {
            // Process invites
            if (!empty($inviteUsers)) {
                $invites = explode(',', $inviteUsers);
                foreach ($invites as $invite) {
                    $invite = trim($invite);
                    if (!empty($invite)) {
                        $invitedUserId = $this->groupModel->findUserByEmailOrUsername($invite);
                        if ($invitedUserId) {
                            $this->groupModel->addMember($groupId, $invitedUserId);
                            $this->notificationModel->create(
                                $invitedUserId, 
                                'Group Invitation', 
                                Session::get('username') . ' added you to the group "' . $name . '"',
                                'invite'
                            );
                        }
                    }
                }
            }
            Session::set('success_message', 'Group created successfully.');
        } else {
            Session::set('error_message', 'Failed to create group.');
        }
        header('Location: ../../public/groups.php?id=' . $groupId);
        exit;
    }

    private function addGroupExpense() {
        $groupId = $_POST['group_id'] ?? 0;
        $amount = $_POST['amount'] ?? 0;
        $description = $_POST['description'] ?? '';
        $categoryId = $_POST['category_id'] ?? null;
        $splitType = $_POST['split_type'] ?? 'equal';
        $paidBy = $_POST['paid_by'] ?? Session::get('user_id');
        $date = $_POST['expense_date'] ?? date('Y-m-d');
        
        $userId = Session::get('user_id');
        $members = $this->groupModel->getGroupMembers($groupId);

        if (!$members || count($members) == 0) {
            Session::set('error_message', 'Group has no members.');
            header("Location: ../../public/groups.php?id=$groupId");
            exit;
        }

        $splits = [];
        if ($splitType === 'equal') {
            $splitAmount = round($amount / count($members), 2);
            foreach ($members as $member) {
                $splits[$member['id']] = $splitAmount;
            }
            // Adjust remainder
            $totalSplit = $splitAmount * count($members);
            $diff = $amount - $totalSplit;
            if ($diff != 0) {
                $splits[$paidBy] += $diff;
            }
        } elseif ($splitType === 'percentage') {
            foreach ($members as $member) {
                $pct = $_POST['split_pct_' . $member['id']] ?? 0;
                $splits[$member['id']] = round(($amount * $pct) / 100, 2);
            }
        } elseif ($splitType === 'custom') {
            foreach ($members as $member) {
                $amt = $_POST['split_amt_' . $member['id']] ?? 0;
                $splits[$member['id']] = floatval($amt);
            }
        }

        // Validate splits match amount
        $totalSplits = array_sum($splits);
        if (abs($totalSplits - $amount) > 0.05) {
            Session::set('error_message', 'Split amounts do not add up to total amount.');
            header("Location: ../../public/groups.php?id=$groupId");
            exit;
        }

        if ($this->groupModel->addGroupExpense($groupId, $paidBy, $amount, $description, $categoryId, $splitType, $date, $splits)) {
            // Notify other members
            foreach ($members as $member) {
                if ($member['id'] != $userId) {
                    $this->notificationModel->create(
                        $member['id'], 
                        'New Group Expense', 
                        Session::get('username') . ' added an expense of ₹' . $amount . ' for ' . $description,
                        'expense'
                    );
                }
            }
            Session::set('success_message', 'Expense added successfully.');
        } else {
            Session::set('error_message', 'Failed to add group expense.');
        }
        
        header("Location: ../../public/groups.php?id=$groupId");
        exit;
    }

    private function settleUp() {
        $groupId = $_POST['group_id'] ?? 0;
        $payeeId = $_POST['payee_id'] ?? 0;
        $amount = $_POST['amount'] ?? 0;
        $userId = Session::get('user_id');

        if ($this->groupModel->settleUp($groupId, $userId, $payeeId, $amount)) {
            $this->notificationModel->create(
                $payeeId, 
                'Payment Settled', 
                Session::get('username') . ' paid you ₹' . $amount,
                'settlement'
            );
            Session::set('success_message', 'Settlement recorded.');
        } else {
            Session::set('error_message', 'Failed to record settlement.');
        }
        header("Location: ../../public/groups.php?id=$groupId");
        exit;
    }
}

$controller = new GroupController();
$controller->handleRequest();

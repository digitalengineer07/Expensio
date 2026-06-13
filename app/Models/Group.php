<?php
namespace App\Models;

class Group {
    public $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function createGroup($name, $description, $userId) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO groups (name, description, created_by) VALUES (?, ?, ?)");
            $stmt->execute([$name, $description, $userId]);
            $groupId = $this->db->lastInsertId();

            if (!$groupId) {
                // Fallback for PostgreSQL if lastInsertId fails without sequence
                $stmt = $this->db->prepare("SELECT id FROM groups WHERE created_by = ? ORDER BY id DESC LIMIT 1");
                $stmt->execute([$userId]);
                $groupId = $stmt->fetchColumn();
            }

            $this->addMember($groupId, $userId); // Add creator as member
            $this->db->commit();
            return $groupId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function addMember($groupId, $userId) {
        // Handle both MySQL and PostgreSQL gracefully
        try {
            $stmt = $this->db->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
            return $stmt->execute([$groupId, $userId]);
        } catch (\PDOException $e) {
            // Ignore duplicate key errors (23000)
            if ($e->getCode() == 23000 || $e->getCode() == 23505) {
                return true;
            }
            throw $e;
        }
    }

    public function findUserByEmailOrUsername($query) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$query, $query]);
        return $stmt->fetchColumn();
    }

    public function getGroupsByUser($userId) {
        $stmt = $this->db->prepare("
            SELECT g.*, 
                   (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = g.id) as member_count
            FROM groups g
            JOIN group_members gm ON g.id = gm.group_id
            WHERE gm.user_id = ?
            ORDER BY g.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getGroupMembers($groupId) {
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.email 
            FROM users u
            JOIN group_members gm ON u.id = gm.user_id
            WHERE gm.group_id = ?
        ");
        $stmt->execute([$groupId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function addGroupExpense($groupId, $paidBy, $amount, $description, $categoryId, $splitType, $date, $splits) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO group_expenses (group_id, paid_by, amount, description, category_id, split_type, expense_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$groupId, $paidBy, $amount, $description, $categoryId, $splitType, $date]);
            $expenseId = $this->db->lastInsertId();

            if (!$expenseId) {
                $stmt = $this->db->prepare("SELECT id FROM group_expenses WHERE group_id = ? ORDER BY id DESC LIMIT 1");
                $stmt->execute([$groupId]);
                $expenseId = $stmt->fetchColumn();
            }

            $stmtSplit = $this->db->prepare("INSERT INTO group_expense_splits (group_expense_id, user_id, amount_owed) VALUES (?, ?, ?)");
            foreach ($splits as $userId => $splitAmount) {
                if ($splitAmount > 0) {
                    $stmtSplit->execute([$expenseId, $userId, $splitAmount]);
                }
            }

            $this->db->commit();
            return $expenseId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getGroupBalances($groupId) {
        $stmt = $this->db->prepare("
            WITH Paid AS (
                SELECT paid_by as user_id, SUM(amount) as total_paid
                FROM group_expenses
                WHERE group_id = ?
                GROUP BY paid_by
            ),
            Owed AS (
                SELECT s.user_id, SUM(s.amount_owed) as total_owed
                FROM group_expense_splits s
                JOIN group_expenses e ON s.group_expense_id = e.id
                WHERE e.group_id = ? AND s.is_settled = FALSE
                GROUP BY s.user_id
            )
            SELECT u.id, u.username, 
                   COALESCE(p.total_paid, 0) as paid, 
                   COALESCE(o.total_owed, 0) as owed,
                   (COALESCE(p.total_paid, 0) - COALESCE(o.total_owed, 0)) as net_balance
            FROM users u
            JOIN group_members gm ON u.id = gm.user_id
            LEFT JOIN Paid p ON u.id = p.user_id
            LEFT JOIN Owed o ON u.id = o.user_id
            WHERE gm.group_id = ?
        ");
        $stmt->execute([$groupId, $groupId, $groupId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function settleUp($groupId, $payerId, $payeeId, $amount) {
        $description = "Settlement";
        $date = date('Y-m-d');
        $splits = [
            $payeeId => $amount
        ];
        // For settlements, split type is 'settlement', category is NULL
        return $this->addGroupExpense($groupId, $payerId, $amount, $description, null, 'settlement', $date, $splits);
    }
}

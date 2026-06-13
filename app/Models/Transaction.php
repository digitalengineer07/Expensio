<?php
namespace App\Models;

class Transaction {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($userId, $walletId, $amount, $category, $type, $note, $date) {
        $stmt = $this->db->prepare("INSERT INTO transactions (user_id, wallet_id, amount, category, type, note, transaction_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$userId, $walletId, $amount, $category, $type, $note, $date]);
    }

    public function getByUser($userId, $filters = []) {
        $query = "SELECT t.*, w.wallet_name FROM transactions t JOIN wallets w ON t.wallet_id = w.id WHERE t.user_id = :userId";
        $params = [':userId' => $userId];

        if (!empty($filters['wallet_id'])) {
            $query .= " AND t.wallet_id = :walletId";
            $params[':walletId'] = $filters['wallet_id'];
        }
        if (!empty($filters['type'])) {
            $query .= " AND t.type = :type";
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['category'])) {
            $query .= " AND t.category = :category";
            $params[':category'] = $filters['category'];
        }
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query .= " AND t.transaction_date BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $filters['start_date'];
            $params[':endDate'] = $filters['end_date'];
        }
        
        $query .= " ORDER BY t.transaction_date DESC, t.created_at DESC";

        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function delete($id, $userId) {
        $stmt = $this->db->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $tx = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($tx) {
            $walletModel = new Wallet();
            $reverseType = $tx['type'] === 'income' ? 'expense' : 'income';
            $walletModel->updateBalance($tx['wallet_id'], $tx['amount'], $reverseType);
            
            $delStmt = $this->db->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
            return $delStmt->execute([$id, $userId]);
        }
        return false;
    }

    public function getTotalIncome($userId) {
        $stmt = $this->db->prepare("SELECT SUM(amount) FROM transactions WHERE user_id = ? AND type = 'income'");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() ?: 0;
    }

    public function getTotalExpense($userId) {
        $stmt = $this->db->prepare("SELECT SUM(amount) FROM transactions WHERE user_id = ? AND type = 'expense'");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() ?: 0;
    }

    public function getCategoryStats($userId) {
        $stmt = $this->db->prepare("SELECT category as name, SUM(amount) as total FROM transactions WHERE user_id = ? AND type = 'expense' GROUP BY category ORDER BY total DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

<?php

namespace App\Models;

class RecurringTransaction {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO recurring_transactions (user_id, amount, category_id, description, type, frequency, start_date, next_date) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['user_id'],
            $data['amount'],
            $data['category_id'],
            $data['description'],
            $data['type'],
            $data['frequency'],
            $data['start_date'],
            $data['next_date']
        ]);
    }

    public function getDueTransactions() {
        $stmt = $this->db->prepare("SELECT * FROM recurring_transactions WHERE next_date <= CURRENT_DATE");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateNextDate($id, $nextDate) {
        $stmt = $this->db->prepare("UPDATE recurring_transactions SET next_date = ? WHERE id = ?");
        return $stmt->execute([$nextDate, $id]);
    }

    public function getUpcomingTransactionsByUser($userId, $limit = 5) {
        $stmt = $this->db->prepare("SELECT r.*, c.name as category_name, c.icon as category_icon, c.type as cat_type
                                   FROM recurring_transactions r 
                                   LEFT JOIN categories c ON r.category_id = c.id 
                                   WHERE r.user_id = ? AND r.next_date >= CURRENT_DATE
                                   ORDER BY r.next_date ASC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
}

<?php

namespace App\Models;

class Expense {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllByUser($userId, $limit = 10) {
        $stmt = $this->db->prepare("SELECT e.*, c.name as category_name, c.icon as category_icon 
                                   FROM expenses e 
                                   LEFT JOIN categories c ON e.category_id = c.id 
                                   WHERE e.user_id = ? 
                                   ORDER BY e.expense_date DESC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    public function getTotalExpense($userId) {
        $stmt = $this->db->prepare("SELECT SUM(e.amount) as total 
                                   FROM expenses e 
                                   LEFT JOIN categories c ON e.category_id = c.id
                                   WHERE e.user_id = ? AND (c.type = 'expense' OR c.type IS NULL)");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getTotalIncome($userId) {
        $stmt = $this->db->prepare("SELECT SUM(e.amount) as total 
                                   FROM expenses e 
                                   JOIN categories c ON e.category_id = c.id
                                   WHERE e.user_id = ? AND c.type = 'income'");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getNetBalance($userId) {
        $income = $this->getTotalIncome($userId);
        $expense = $this->getTotalExpense($userId);
        return $income - $expense;
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO expenses (user_id, category_id, project_id, amount, currency, description, expense_date, receipt_path) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['user_id'],
            $data['category_id'],
            $data['project_id'],
            $data['amount'],
            $data['currency'] ?? 'USD',
            $data['description'],
            $data['expense_date'],
            $data['receipt_path']
        ]);
    }

    public function getCategoryStats($userId) {
        $stmt = $this->db->prepare("SELECT c.name, SUM(e.amount) as total 
                                   FROM expenses e 
                                   JOIN categories c ON e.category_id = c.id 
                                   WHERE e.user_id = ? 
                                   GROUP BY c.id");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}

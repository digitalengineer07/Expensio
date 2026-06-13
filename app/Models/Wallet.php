<?php
namespace App\Models;

class Wallet {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($userId, $name, $type, $balance) {
        $stmt = $this->db->prepare("INSERT INTO wallets (user_id, wallet_name, wallet_type, balance) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $name, $type, $balance]);
    }

    public function getByUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM wallets WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function delete($id, $userId) {
        $stmt = $this->db->prepare("DELETE FROM wallets WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }

    public function updateBalance($id, $amount, $type) {
        if ($type === 'income') {
            $stmt = $this->db->prepare("UPDATE wallets SET balance = balance + ? WHERE id = ?");
        } else {
            $stmt = $this->db->prepare("UPDATE wallets SET balance = balance - ? WHERE id = ?");
        }
        return $stmt->execute([$amount, $id]);
    }
    
    public function getById($id, $userId) {
        $stmt = $this->db->prepare("SELECT * FROM wallets WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}

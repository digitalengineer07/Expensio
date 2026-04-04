<?php

namespace App\Models;

class Category {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE is_global = 1 OR user_id IS NULL");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE is_global = 1 OR user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}

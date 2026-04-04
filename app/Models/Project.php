<?php

namespace App\Models;

class Project {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getByUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO projects (user_id, name, description, budget) VALUES (?, ?, ?, ?)");
        return $stmt->execute([
            $data['user_id'],
            $data['name'],
            $data['description'],
            $data['budget']
        ]);
    }
}

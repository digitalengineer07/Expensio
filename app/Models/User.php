<?php

namespace App\Models;

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password_hash, role_id, verification_token) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['username'],
            $data['email'],
            $data['password_hash'],
            $data['role_id'],
            $data['verification_token']
        ]);
    }

    public function verifyEmail($token) {
        $stmt = $this->db->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ?");
        return $stmt->execute([$token]);
    }
}

<?php

namespace App\Models;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        // Load .env variables (simple manual load for now or assume they are available)
        $host = getenv('DB_HOST') ?: 'localhost';
        $db   = getenv('DB_NAME') ?: 'money_management';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance->getConnection();
    }

    public function getConnection() {
        return $this->conn;
    }
}

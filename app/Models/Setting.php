<?php
namespace App\Models;

class Setting {
    public $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Get User Global Settings (from users table)
    public function getUserGlobals($userId) {
        $stmt = $this->db->prepare("SELECT currency, dark_mode, language, subscription_status, trial_ends_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Update Global Preferences
    public function updateGlobals($userId, $currency, $language) {
        $stmt = $this->db->prepare("UPDATE users SET currency = ?, language = ? WHERE id = ?");
        return $stmt->execute([$currency, $language, $userId]);
    }

    public function toggleDarkMode($userId, $isDark) {
        $stmt = $this->db->prepare("UPDATE users SET dark_mode = ? WHERE id = ?");
        return $stmt->execute([$isDark ? 1 : 0, $userId]); // PostgreSQL boolean can map to 1/0 or true/false
    }

    // Get User Notification Settings
    public function getNotificationSettings($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_settings WHERE user_id = ?");
        $stmt->execute([$userId]);
        $settings = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$settings) {
            // Create default settings
            $this->db->prepare("INSERT INTO user_settings (user_id) VALUES (?)")->execute([$userId]);
            $stmt->execute([$userId]);
            $settings = $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        return $settings;
    }

    // Update Notification Settings
    public function updateNotificationSettings($userId, $data) {
        $stmt = $this->db->prepare("
            UPDATE user_settings 
            SET notifications_enabled = ?, expense_alerts = ?, group_updates = ?, spending_suggestions = ?, weekly_summary = ? 
            WHERE user_id = ?
        ");
        return $stmt->execute([
            $data['notifications_enabled'] ?? false,
            $data['expense_alerts'] ?? false,
            $data['group_updates'] ?? false,
            $data['spending_suggestions'] ?? false,
            $data['weekly_summary'] ?? false,
            $userId
        ]);
    }
}

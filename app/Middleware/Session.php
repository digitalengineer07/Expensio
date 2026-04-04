<?php

namespace App\Middleware;

class Session {
    public static function start() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_secure' => isset($_SERVER['HTTPS']),
                'cookie_samesite' => 'Strict',
            ]);
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        return $_SESSION[$key] ?? null;
    }

    public static function destroy() {
        session_unset();
        session_destroy();
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function checkRole($roles) {
        $userRole = self::get('user_role');
        if (!in_array($userRole, (array)$roles)) {
            header('Location: /money-management/public/login.php?error=unauthorized');
            exit;
        }
    }
}

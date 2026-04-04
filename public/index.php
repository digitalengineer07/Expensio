<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Middleware\Session;

$queryString = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';

if (Session::isLoggedIn()) {
    header('Location: dashboard.php' . $queryString);
    exit;
} else {
    header('Location: login.php' . $queryString);
    exit;
}

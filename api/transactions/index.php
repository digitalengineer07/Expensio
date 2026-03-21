<?php
require_once __DIR__ . '/../../app/Controllers/TransactionController.php';

use App\Controllers\TransactionController;

$controller = new TransactionController();
$controller->handleRequest();

<?php
require_once __DIR__ . '/config/bootstrap.php';
$db = \App\Models\Database::getInstance();

try {
    $db->exec("ALTER TABLE groups ADD COLUMN description TEXT DEFAULT NULL");
    echo "Added description to groups\n";
} catch (Exception $e) {
    echo "Groups description: " . $e->getMessage() . "\n";
}

try {
    $db->exec("ALTER TABLE group_expenses ADD COLUMN category_id INT DEFAULT NULL");
    echo "Added category_id to group_expenses\n";
} catch (Exception $e) {
    echo "Group expenses category_id: " . $e->getMessage() . "\n";
}

try {
    $db->exec("ALTER TABLE group_expenses ADD COLUMN split_type VARCHAR(50) DEFAULT 'equal'");
    echo "Added split_type to group_expenses\n";
} catch (Exception $e) {
    echo "Group expenses split_type: " . $e->getMessage() . "\n";
}

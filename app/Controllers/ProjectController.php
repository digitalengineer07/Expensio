<?php

namespace App\Controllers;

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Models\Project;
use App\Middleware\Session;

class ProjectController {
    public function handle() {
        if (!Session::isLoggedIn()) {
            header('Location: ../../public/login.php');
            exit;
        }

        // Only Engineers or Admins can manage projects
        Session::checkRole(['Engineer', 'Admin']);

        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'add':
                $this->add();
                break;
            default:
                header('Location: ../../public/index.php');
                exit;
        }
    }

    private function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $projectModel = new Project();
        
        $data = [
            'user_id' => Session::get('user_id'),
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'budget' => $_POST['budget'] ?? 0
        ];

        if ($projectModel->create($data)) {
            header('Location: ../../public/index.php?success=project_added');
        } else {
            header('Location: ../../public/index.php?error=failed_to_add_project');
        }
    }
}

$controller = new ProjectController();
$controller->handle();

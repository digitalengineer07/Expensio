<<<<<<< HEAD
<?php
require_once __DIR__ . '/config/bootstrap.php';
use App\Middleware\Session;

if (Session::isLoggedIn()) {
    header('Location: public/index.php');
} else {
    header('Location: public/welcome.php');
}
exit;


if (Session::isLoggedIn()) {
    header('Location: public/index.php');
} else {
    header('Location: public/welcome.php');
}
exit;
>>>>>>> c80042f8 (First commit)

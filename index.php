<?php
declare(strict_types=1);
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
session_start();

$sessionLifetime = 1800; 
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionLifetime)) {
    $_SESSION = [];
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

require_once __DIR__ . '/controller/authet.php';
$authController = new AuthController();

$action = 'home';
if (isset($_GET['action'])) {
    $action = $_GET['action'];
}

switch ($action) {
       
    case 'register':
        if (AuthController::isAuthenticated()) {
            header('Location: index.php?action=dashboard');
            exit;
        }
        $errors = [];
        $old = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $authController->register($_POST);
            $old = ['username' => '', 'email' => ''];
        if (isset($_POST['username'])) {
            $old['username'] = $_POST['username'];
        }
        if (isset($_POST['email'])) {
            $old['email'] = $_POST['email'];
        }

            if (empty($errors)) {
                header('Location: index.php?action=login&registered=1');
                exit;
            }
        }
        require __DIR__ . '/view/register.php';
        break;
            // ---------------- LOGIN ----------------
    case 'login':
        if (AuthController::isAuthenticated()) {
            header('Location: index.php?action=dashboard');
            exit;
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $authController->login($_POST);

            if (empty($errors)) {
                $redirect = 'dashboard';
            if ($_SESSION['role'] === 'admin') {
                $redirect = 'admin';
}
                header('Location: index.php?action=' . $redirect);
                exit;
            }
        }

        require __DIR__ . '/view/login.php';
        break;

    
    case 'logout':
        $authController->logout();
        header('Location: index.php?action=login');
        exit;

    case 'dashboard':
        AuthController::requireAuth();

        $user = $authController->findById((int) $_SESSION['user_id']);

        if (!$user) {
            $authController->logout();
            header('Location: index.php?action=login');
            exit;
        }

        require __DIR__ . '/view/userspace.php';
        break;
    case 'admin':
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        if (!$authController->isAdmin()) {
            die('Access denied: you do not have the required permissions.');
        }

        $user         = $authController->findById((int) $_SESSION['user_id']);
        $allUsers     = $authController->getAllUsers();
        $totalUsers   = $authController->countAllUsers();
        $totalAdmins  = $authController->countByRole('admin');
        $totalRegular = $authController->countByRole('user');

        require __DIR__ . '/view/adminspace.php';
        break;
            // ---------------- HOME ----------------
    case 'home':
        if (AuthController::isAuthenticated()) {
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                header('Location: index.php?action=admin');
            } else {
                header('Location: index.php?action=dashboard');
            }
        } else {
            header('Location: index.php?action=login');
        }
        exit;
    default:
        http_response_code(404);
        echo '404 - Page not found';
        break;
}
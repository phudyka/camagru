<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

session_start();

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/config/setup.php';

require_once ROOT_PATH . '/controllers/AuthController.php';
$auth = new AuthController();
$auth->checkRememberToken();

$request = $_SERVER['REQUEST_URI'];
$base = '/';

$request = str_replace($base, '', $request);
$request = strtok($request, '?');

if (strpos($request, 'auth/') === 0) {
    $action = substr($request, 5);
    switch ($action) {
        case 'login':
            $auth->login();
            break;
        case 'register':
            $auth->register();
            break;
        case 'logout':
            $auth->logout();
            break;
        case 'verify':
            $auth->verify();
            break;
        default:
            header('Location: /');
            exit;
    }
    exit; 
}

if (strpos($request, 'gallery/') === 0) {
    require_once ROOT_PATH . '/controllers/GalleryController.php';
    $gallery = new GalleryController();
    $action = substr($request, 8); 
    
    $result = [];
    switch ($action) {
        case 'like':
            $result = $gallery->likeImage();
            break;
        case 'comment':
            $result = $gallery->commentImage();
            break;
        case 'delete':
            $result = $gallery->deleteImage();
            break;
        default:
            $result = ['success' => false, 'message' => 'Action non reconnue.'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

switch ($request) {
    case '':
    case '/':
        require ROOT_PATH . '/views/home.php';
        break;
    case 'login':
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
        require ROOT_PATH . '/views/login.php';
        break;
    case 'register':
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
        require ROOT_PATH . '/views/register.php';
        break;
    case 'gallery':
        require ROOT_PATH . '/views/gallery.php';
        break;
    case 'camera':
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'You must be logged in to access this page.'
            ];
            header('Location: /login');
            exit;
        }
        require ROOT_PATH . '/views/camera.php';
        break;
    default:
        http_response_code(404);
        require ROOT_PATH . '/views/404.php';
        break;
}

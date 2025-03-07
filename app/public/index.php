<?php
// Point d'entrée de l'application
session_start();

// Définir la constante ROOT_PATH
define('ROOT_PATH', dirname(__DIR__));

// Inclure l'autoloader
require_once ROOT_PATH . '/config/setup.php';

// Vérifier le cookie "Se souvenir de moi"
require_once ROOT_PATH . '/controllers/AuthController.php';
$auth = new AuthController();
$auth->checkRememberToken();

// Router simple
$request = $_SERVER['REQUEST_URI'];
$base = '/';  // Base URL

// Nettoyer l'URL
$request = str_replace($base, '', $request);
$request = strtok($request, '?');

// Traiter les actions des contrôleurs
if (strpos($request, 'auth/') === 0) {
    $action = substr($request, 5); // Enlever 'auth/'
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
    exit; // Arrêter l'exécution après avoir traité l'action
}

if (strpos($request, 'gallery/') === 0) {
    require_once ROOT_PATH . '/controllers/GalleryController.php';
    $gallery = new GalleryController();
    $action = substr($request, 8); // Enlever 'gallery/'
    
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
    
    // Renvoyer le résultat en JSON
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// Routes de base
switch ($request) {
    case '':
    case '/':
        require ROOT_PATH . '/views/home.php';
        break;
    case 'login':
        // Rediriger vers la page d'accueil si l'utilisateur est déjà connecté
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
        require ROOT_PATH . '/views/login.php';
        break;
    case 'register':
        // Rediriger vers la page d'accueil si l'utilisateur est déjà connecté
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
        require ROOT_PATH . '/views/register.php';
        break;
    case 'camera':
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Vous devez être connecté pour accéder à cette page.'
            ];
            header('Location: /login');
            exit;
        }
        require ROOT_PATH . '/views/camera.php';
        break;
    case 'gallery':
        require ROOT_PATH . '/views/gallery.php';
        break;
    default:
        http_response_code(404);
        require ROOT_PATH . '/views/404.php';
        break;
}

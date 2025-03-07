<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $user;
    
    public function __construct() {
        $this->user = new User();
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = htmlspecialchars(trim($_POST['username']));
            $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            $notifications = isset($_POST['notifications']) ? 1 : 0;
            
            $errors = [];
            
            if (strlen($username) < 3 || strlen($username) > 50) {
                $errors[] = "Le nom d'utilisateur doit contenir entre 3 et 50 caractères.";
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'adresse email n'est pas valide.";
            }
            
            if (strlen($password) < 8) {
                $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
            }
            
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
                $errors[] = "Le mot de passe doit contenir au moins une majuscule, un chiffre et un caractère spécial.";
            }
            
            if ($password !== $confirmPassword) {
                $errors[] = "Les mots de passe ne correspondent pas.";
            }
            
            if ($this->user->findByUsername($username)) {
                $errors[] = "Ce nom d'utilisateur est déjà utilisé.";
            }
            
            if ($this->user->findByEmail($email)) {
                $errors[] = "Cette adresse email est déjà utilisée.";
            }
            
            if (empty($errors)) {
                $this->user->username = $username;
                $this->user->email = $email;
                $this->user->password = $password; 
                $this->user->notifications_enabled = $notifications;
                
                if ($this->user->create()) {
                    $this->sendVerificationEmail($email, $this->user->verification_token);
                    
                    $_SESSION['flash'] = [
                        'type' => 'success',
                        'message' => 'Votre compte a été créé avec succès ! Veuillez vérifier votre email pour activer votre compte.'
                    ];
                    
                    header('Location: /login');
                    exit;
                } else {
                    $errors[] = "Une erreur est survenue lors de la création du compte.";
                }
            }
            
            if (!empty($errors)) {
                $_SESSION['flash'] = [
                    'type' => 'danger',
                    'message' => implode('<br>', $errors)
                ];
                $_SESSION['form_data'] = $_POST;
                header('Location: /register');
                exit;
            }
        }
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];
            $remember = isset($_POST['remember']) ? true : false;
            
            $errors = [];
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'adresse email n'est pas valide.";
            }
            
            if (empty($errors) && $this->user->findByEmail($email)) {
                if (!$this->user->verified) {
                    $errors[] = "Votre compte n'a pas été vérifié. Veuillez vérifier votre email pour activer votre compte.";
                } else {
                    if (password_verify($password, $this->user->password)) {
                        $_SESSION['user_id'] = $this->user->id;
                        $_SESSION['username'] = $this->user->username;
                        
                        if ($remember) {
                            $token = bin2hex(random_bytes(32));
                            $expiry = time() + (30 * 24 * 60 * 60); // 30 jours
                            
                            $this->user->storeRememberToken($token, $expiry);
                            
                            setcookie('remember_token', $token, $expiry, '/', '', false, true);
                        }
                        
                        $_SESSION['flash'] = [
                            'type' => 'success',
                            'message' => 'Vous êtes maintenant connecté !'
                        ];
                        
                        header('Location: /');
                        exit;
                    } else {
                        $errors[] = "Mot de passe incorrect.";
                    }
                }
            } else {
                $errors[] = "Aucun compte n'est associé à cette adresse email.";
            }
            
            if (!empty($errors)) {
                $_SESSION['flash'] = [
                    'type' => 'danger',
                    'message' => implode('<br>', $errors)
                ];
                $_SESSION['form_data'] = ['email' => $email];
                header('Location: /login');
                exit;
            }
        }
    }
    
    public function logout() {
        if (isset($_COOKIE['remember_token'])) {
            $this->user->removeRememberToken($_COOKIE['remember_token']);
            
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
        
        session_destroy();
        
        header('Location: /');
        exit;
    }
    
    public function verify() {
        if (isset($_GET['token'])) {
            $token = $_GET['token'];
            
            if ($this->user->verifyAccount($token)) {
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Votre compte a été vérifié avec succès ! Vous pouvez maintenant vous connecter.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'type' => 'danger',
                    'message' => 'Le lien de vérification est invalide ou a expiré.'
                ];
            }
            
            header('Location: /login');
            exit;
        }
    }
    
    private function sendVerificationEmail($email, $token) {
        $subject = "Vérification de votre compte Camagru";
        $verificationLink = "http://" . $_SERVER['HTTP_HOST'] . "/verify?token=" . $token;
        
        $message = "
        <html>
        <head>
            <title>Vérification de votre compte Camagru</title>
        </head>
        <body>
            <h2>Bienvenue sur Camagru !</h2>
            <p>Merci de vous être inscrit. Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :</p>
            <p><a href='$verificationLink'>Vérifier mon compte</a></p>
            <p>Si vous n'avez pas créé de compte, veuillez ignorer cet email.</p>
            <p>L'équipe Camagru</p>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: noreply@camagru.com" . "\r\n";
        
        mail($email, $subject, $message, $headers);
    }
    
    public function checkRememberToken() {
        if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            
            $user_id = $this->user->findByRememberToken($token);
            
            if ($user_id) {
                $this->user->findById($user_id);
                
                $_SESSION['user_id'] = $this->user->id;
                $_SESSION['username'] = $this->user->username;
                
                $newToken = bin2hex(random_bytes(32));
                $expiry = time() + (30 * 24 * 60 * 60); // 30 jours
                
                $this->user->updateRememberToken($token, $newToken, $expiry);
                
                setcookie('remember_token', $newToken, $expiry, '/', '', false, true);
            }
        }
    }
}

if (basename($_SERVER['PHP_SELF']) === 'AuthController.php') {
    session_start();
    $auth = new AuthController();
    
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        
        switch ($action) {
            case 'register':
                $auth->register();
                break;
            case 'login':
                $auth->login();
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
    }
} 
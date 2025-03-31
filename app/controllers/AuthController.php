<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/CSRFProtection.php';

class AuthController {
    private $user;
    
    public function __construct() {
        $this->user = new User();
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !CSRFProtection::verifyToken($_POST['csrf_token'])) {
                $_SESSION['flash'] = [
                    'type' => 'danger',
                    'message' => 'Invalid form submission. Please try again.'
                ];
                header('Location: /register');
                exit;
            }
            
            $username = htmlspecialchars(trim($_POST['username']));
            $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            $notifications = isset($_POST['notifications']) ? 1 : 0;
            
            $errors = [];
            
            if (empty($username)) {
                $errors[] = "Username is required.";
            } elseif (strlen($username) < 3 || strlen($username) > 50) {
                $errors[] = "Username must be between 3 and 50 characters.";
            } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                $errors[] = "Username can only contain letters, numbers, and underscores.";
            }
            
            if (empty($email)) {
                $errors[] = "Email is required.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format.";
            }
            
            if (empty($password)) {
                $errors[] = "Password is required.";
            } elseif (strlen($password) < 8) {
                $errors[] = "Password must be at least 8 characters.";
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
                $errors[] = "Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
            }
            
            if ($password !== $confirmPassword) {
                $errors[] = "Passwords do not match.";
            }
            
            if (empty($errors)) {
                if ($this->user->findByUsername($username)) {
                    $errors[] = "Username already exists.";
                }
                
                if ($this->user->findByEmail($email)) {
                    $errors[] = "Email already exists.";
                }
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
                        'message' => 'Your account has been created! Please check your email to verify your account.'
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
            $identifier = trim($_POST['identifier']);
            $password = $_POST['password'];
            $remember = isset($_POST['remember']) ? true : false;
            
            $errors = [];
            
            if (empty($identifier)) {
                $errors[] = "Veuillez entrer votre nom d'utilisateur ou votre email.";
            }
            
            if (empty($password)) {
                $errors[] = "Veuillez entrer votre mot de passe.";
            }
            
            if (empty($errors)) {
                $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
                
                $userFound = false;
                
                if ($isEmail) {
                    $userFound = $this->user->findByEmail($identifier);
                } else {
                    $userFound = $this->user->findByUsername($identifier);
                }
                
                if ($userFound) {
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
                    $errors[] = "Aucun compte n'est associé à cet identifiant.";
                }
            }
            
            if (!empty($errors)) {
                $_SESSION['flash'] = [
                    'type' => 'danger',
                    'message' => implode('<br>', $errors)
                ];
                $_SESSION['form_data'] = ['identifier' => $identifier];
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
<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Image.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Like.php';

class GalleryController {
    private $image;
    private $comment;
    private $like;
    
    public function __construct() {
        $this->image = new Image();
        $this->comment = new Comment();
        $this->like = new Like();
    }
    
    // Récupérer les images pour la galerie avec pagination
    public function getImages($page = 1, $perPage = 12) {
        $offset = ($page - 1) * $perPage;
        $images = $this->image->getAll($perPage, $offset);
        
        // Ajouter les commentaires et les likes pour chaque image
        foreach ($images as &$image) {
            $image['comments'] = $this->comment->getByImageId($image['id']);
            $image['comments_count'] = count($image['comments']);
            $image['likes_count'] = $this->like->countByImageId($image['id']);
            
            // Vérifier si l'utilisateur connecté a aimé cette image
            $image['user_liked'] = false;
            if (isset($_SESSION['user_id'])) {
                $image['user_liked'] = $this->like->hasUserLiked($image['id'], $_SESSION['user_id']);
            }
        }
        
        return $images;
    }
    
    // Récupérer le nombre total d'images
    public function getTotalImagesCount() {
        return $this->image->count();
    }
    
    // Ajouter un like à une image
    public function likeImage() {
        if (!isset($_SESSION['user_id'])) {
            return [
                'success' => false,
                'message' => 'Vous devez être connecté pour aimer une image.'
            ];
        }
        
        $imageId = isset($_POST['image_id']) ? (int)$_POST['image_id'] : 0;
        
        if ($imageId <= 0) {
            return [
                'success' => false,
                'message' => 'ID d\'image invalide.'
            ];
        }
        
        // Vérifier si l'utilisateur a déjà aimé cette image
        $hasLiked = $this->like->hasUserLiked($imageId, $_SESSION['user_id']);
        
        if ($hasLiked) {
            // Supprimer le like
            $this->like->delete($imageId, $_SESSION['user_id']);
            $liked = false;
        } else {
            // Ajouter le like
            $this->like->create($imageId, $_SESSION['user_id']);
            $liked = true;
            
            // Notifier le propriétaire de l'image
            $this->notifyImageOwner($imageId, 'like');
        }
        
        // Récupérer le nouveau nombre de likes
        $likesCount = $this->like->countByImageId($imageId);
        
        return [
            'success' => true,
            'liked' => $liked,
            'likes_count' => $likesCount
        ];
    }
    
    // Ajouter un commentaire à une image
    public function commentImage() {
        if (!isset($_SESSION['user_id'])) {
            return [
                'success' => false,
                'message' => 'Vous devez être connecté pour commenter une image.'
            ];
        }
        
        $imageId = isset($_POST['image_id']) ? (int)$_POST['image_id'] : 0;
        $commentText = isset($_POST['comment']) ? trim($_POST['comment']) : '';
        
        if ($imageId <= 0) {
            return [
                'success' => false,
                'message' => 'ID d\'image invalide.'
            ];
        }
        
        if (empty($commentText)) {
            return [
                'success' => false,
                'message' => 'Le commentaire ne peut pas être vide.'
            ];
        }
        
        // Créer le commentaire
        $this->comment->image_id = $imageId;
        $this->comment->user_id = $_SESSION['user_id'];
        $this->comment->comment = $commentText;
        
        if ($this->comment->create()) {
            // Notifier le propriétaire de l'image
            $this->notifyImageOwner($imageId, 'comment', $commentText);
            
            return [
                'success' => true,
                'comment' => htmlspecialchars($commentText),
                'username' => $_SESSION['username']
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Une erreur est survenue lors de l\'ajout du commentaire.'
        ];
    }
    
    // Supprimer une image
    public function deleteImage() {
        if (!isset($_SESSION['user_id'])) {
            return [
                'success' => false,
                'message' => 'Vous devez être connecté pour supprimer une image.'
            ];
        }
        
        $imageId = isset($_POST['image_id']) ? (int)$_POST['image_id'] : 0;
        
        if ($imageId <= 0) {
            return [
                'success' => false,
                'message' => 'ID d\'image invalide.'
            ];
        }
        
        // Vérifier que l'utilisateur est le propriétaire de l'image
        $this->image->getById($imageId);
        
        if ($this->image->user_id != $_SESSION['user_id']) {
            return [
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à supprimer cette image.'
            ];
        }
        
        // Supprimer le fichier image
        $imagePath = ROOT_PATH . '/public/img/uploads/' . $this->image->filename;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        
        // Supprimer l'image de la base de données
        if ($this->image->delete()) {
            return [
                'success' => true
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la suppression de l\'image.'
        ];
    }
    
    // Notifier le propriétaire d'une image
    private function notifyImageOwner($imageId, $action, $commentText = '') {
        // Récupérer les informations de l'image et de son propriétaire
        $this->image->getById($imageId);
        
        // Ne pas notifier si l'utilisateur est le propriétaire de l'image
        if ($this->image->user_id == $_SESSION['user_id']) {
            return;
        }
        
        // Récupérer les informations du propriétaire
        require_once __DIR__ . '/../models/User.php';
        $owner = new User();
        $owner->findById($this->image->user_id);
        
        // Vérifier si les notifications sont activées
        if (!$owner->notifications_enabled) {
            return;
        }
        
        // Préparer le message de notification
        $subject = 'Nouvelle activité sur votre photo Camagru';
        
        if ($action === 'like') {
            $message = "
            <html>
            <head>
                <title>Nouvelle activité sur votre photo</title>
            </head>
            <body>
                <h2>Bonjour {$owner->username},</h2>
                <p>{$_SESSION['username']} a aimé votre photo sur Camagru.</p>
                <p>Connectez-vous pour voir votre galerie : <a href='http://{$_SERVER['HTTP_HOST']}/gallery'>Voir ma galerie</a></p>
                <p>L'équipe Camagru</p>
            </body>
            </html>
            ";
        } else if ($action === 'comment') {
            $message = "
            <html>
            <head>
                <title>Nouveau commentaire sur votre photo</title>
            </head>
            <body>
                <h2>Bonjour {$owner->username},</h2>
                <p>{$_SESSION['username']} a commenté votre photo sur Camagru :</p>
                <p><em>\"" . htmlspecialchars($commentText) . "\"</em></p>
                <p>Connectez-vous pour voir votre galerie : <a href='http://{$_SERVER['HTTP_HOST']}/gallery'>Voir ma galerie</a></p>
                <p>L'équipe Camagru</p>
            </body>
            </html>
            ";
        }
        
        // Envoyer l'email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: noreply@camagru.com" . "\r\n";
        
        mail($owner->email, $subject, $message, $headers);
    }
}

// Traitement des actions si ce fichier est appelé directement
if (basename($_SERVER['PHP_SELF']) === 'GalleryController.php') {
    session_start();
    $gallery = new GalleryController();
    
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
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
} 
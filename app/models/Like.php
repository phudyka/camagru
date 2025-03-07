<?php

class Like {
    private $conn;
    private $table = 'likes';
    
    // Propriétés
    public $id;
    public $image_id;
    public $user_id;
    public $created_at;
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }
    
    // Créer un nouveau like
    public function create($imageId, $userId) {
        // Vérifier si l'utilisateur a déjà aimé cette image
        if ($this->hasUserLiked($imageId, $userId)) {
            return true;
        }
        
        $query = "INSERT INTO " . $this->table . " 
                  SET image_id = :image_id, 
                      user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Lier les paramètres
        $stmt->bindParam(':image_id', $imageId);
        $stmt->bindParam(':user_id', $userId);
        
        // Exécuter la requête
        return $stmt->execute();
    }
    
    // Supprimer un like
    public function delete($imageId, $userId) {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE image_id = :image_id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':image_id', $imageId);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }
    
    // Vérifier si un utilisateur a aimé une image
    public function hasUserLiked($imageId, $userId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                  WHERE image_id = :image_id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':image_id', $imageId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }
    
    // Compter le nombre de likes pour une image
    public function countByImageId($imageId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                  WHERE image_id = :image_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':image_id', $imageId);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
} 
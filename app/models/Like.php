<?php

class Like {
    private $conn;
    private $table = 'likes';
    
    public $id;
    public $image_id;
    public $user_id;
    public $created_at;
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }
    
    public function create($imageId, $userId) {
        if ($this->hasUserLiked($imageId, $userId)) {
            return true;
        }
        
        $query = "INSERT INTO " . $this->table . " 
                  SET image_id = :image_id, 
                      user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':image_id', $imageId);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }
    
    public function delete($imageId, $userId) {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE image_id = :image_id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':image_id', $imageId);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }
    
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
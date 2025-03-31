<?php

class Comment {
    private $conn;
    private $table = 'comments';
    
    // Propriétés
    public $id;
    public $image_id;
    public $user_id;
    public $comment;
    public $created_at;
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET image_id = :image_id, 
                      user_id = :user_id, 
                      comment = :comment";
        
        $stmt = $this->conn->prepare($query);
        
        $this->comment = htmlspecialchars(strip_tags($this->comment));
        
        $stmt->bindParam(':image_id', $this->image_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':comment', $this->comment);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    public function getByImageId($imageId) {
        $query = "SELECT c.*, u.username 
                  FROM " . $this->table . " c
                  JOIN users u ON c.user_id = u.id
                  WHERE c.image_id = :image_id
                  ORDER BY c.created_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':image_id', $imageId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);
        
        return $stmt->execute();
    }
} 